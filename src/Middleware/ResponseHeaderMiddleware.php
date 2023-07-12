<?php

declare(strict_types=1);

namespace Dot\ResponseHeader\Middleware;

use Mezzio\Router\RouteResult;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function array_key_exists;

class ResponseHeaderMiddleware implements MiddlewareInterface
{
    private const ALL_ROUTES = '*';

    private array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);
        if (empty($this->config)) {
            return $response;
        }

        $response    = $this->addHeaders($response, self::ALL_ROUTES);
        $routeResult = $request->getAttribute(RouteResult::class);
        if ($routeResult instanceof RouteResult && $routeResult->isSuccess()) {
            $response = $this->addHeaders($response, $routeResult->getMatchedRouteName());
        }
        return $response;
    }

    public function addHeaders(ResponseInterface $response, string $route): ResponseInterface
    {
        if (array_key_exists($route, $this->config)) {
            foreach ($this->config[$route] as $header => $data) {
                if (! array_key_exists('value', $data)) {
                    continue;
                }
                $overwrite = isset($data['overwrite']) && $data['overwrite'] === true ? true : false;
                if ($overwrite) {
                    $response = $response->withHeader($header, $data['value']);
                }
            }
        }
        return $response;
    }
}
