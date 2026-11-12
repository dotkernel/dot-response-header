<?php

declare(strict_types=1);

namespace Dot\ResponseHeader\Middleware;

use Dot\ResponseHeader\Exception\InvalidConfigException;
use Mezzio\Router\RouteResult;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function array_is_list;
use function array_key_exists;
use function is_array;
use function is_bool;
use function is_float;
use function is_int;
use function is_string;

/**
 * @phpstan-type HeaderConfig array{value: string|non-empty-list<string>, overwrite: bool}
 * @phpstan-type ResponseHeadersConfig array<string, array<string, HeaderConfig>>
 */
class ResponseHeaderMiddleware implements MiddlewareInterface
{
    private const ALL_ROUTES = '*';

    /** @var ResponseHeadersConfig */
    private readonly array $config;

    /**
     * @param array<mixed> $config
     * @throws InvalidConfigException
     */
    public function __construct(array $config)
    {
        $this->config = self::normalize($config);
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);
        if ($this->config === []) {
            return $response;
        }

        $response    = $this->addHeaders($response, self::ALL_ROUTES);
        $routeResult = $request->getAttribute(RouteResult::class);
        if (! $routeResult instanceof RouteResult) {
            return $response;
        }

        $routeName = $routeResult->getMatchedRouteName();
        if ($routeName === false) {
            return $response;
        }

        return $this->addHeaders($response, $routeName);
    }

    public function addHeaders(ResponseInterface $response, string $route): ResponseInterface
    {
        foreach ($this->config[$route] ?? [] as $header => $data) {
            if ($response->hasHeader($header) && ! $data['overwrite']) {
                continue;
            }
            $response = $response->withHeader($header, $data['value']);
        }

        return $response;
    }

    /**
     * @param array<mixed> $config
     * @return ResponseHeadersConfig
     * @throws InvalidConfigException
     */
    private static function normalize(array $config): array
    {
        $normalized = [];
        foreach ($config as $route => $headers) {
            if (! is_array($headers)) {
                throw InvalidConfigException::routeNotArray($route, $headers);
            }
            foreach ($headers as $header => $data) {
                if (! is_array($data)) {
                    throw InvalidConfigException::headerNotArray($route, $header, $data);
                }
                if (! array_key_exists('value', $data)) {
                    continue;
                }
                $overwrite = $data['overwrite'] ?? false;
                if (! is_bool($overwrite)) {
                    throw InvalidConfigException::invalidOverwrite($route, $header, $overwrite);
                }

                $normalized[(string) $route][(string) $header] = [
                    'value'     => self::normalizeValue($route, $header, $data['value']),
                    'overwrite' => $overwrite,
                ];
            }
        }

        return $normalized;
    }

    /**
     * @return string|non-empty-list<string>
     * @throws InvalidConfigException
     */
    private static function normalizeValue(int|string $route, int|string $header, mixed $value): string|array
    {
        if (is_string($value)) {
            return $value;
        }
        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }
        if (! is_array($value) || $value === [] || ! array_is_list($value)) {
            throw InvalidConfigException::invalidValue($route, $header, $value);
        }

        $values = [];
        foreach ($value as $item) {
            if (! is_string($item)) {
                throw InvalidConfigException::invalidValue($route, $header, $value);
            }
            $values[] = $item;
        }

        return $values;
    }
}
