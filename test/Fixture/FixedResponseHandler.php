<?php

declare(strict_types=1);

namespace DotTest\ResponseHeader\Fixture;

use Laminas\Diactoros\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Returns the same response for every request, optionally with headers already set.
 */
final readonly class FixedResponseHandler implements RequestHandlerInterface
{
    private ResponseInterface $response;

    /**
     * @param array<string, string> $headers
     */
    public function __construct(array $headers = [])
    {
        $response = new Response();
        foreach ($headers as $name => $value) {
            $response = $response->withHeader($name, $value);
        }
        $this->response = $response;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->response;
    }
}
