<?php

declare(strict_types=1);

namespace Dot\ResponseHeader\Factory;

use Dot\ResponseHeader\Middleware\ResponseHeaderMiddleware;
use Psr\Container\ContainerInterface;

class ResponseHeaderMiddlewareFactory
{
    public function __invoke(ContainerInterface $container): ResponseHeaderMiddleware
    {
        return new ResponseHeaderMiddleware($container->get('config')['dot_response_headers'] ?? []);
    }
}
