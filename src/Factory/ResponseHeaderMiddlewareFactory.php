<?php


namespace Dot\ResponseHeader\Factory;

use Dot\ResponseHeader\Middleware\ResponseHeaderMiddleware;
use Psr\Container\ContainerInterface;

/**
 * Class ResponseHeaderMiddlewareFactory
 * @package Dot\ResponseHeader\Factory
 */
class ResponseHeaderMiddlewareFactory
{
    /**
     * @param ContainerInterface $container
     * @return ResponseHeaderMiddleware
     */
    public function __invoke(ContainerInterface $container): ResponseHeaderMiddleware
    {
        return new ResponseHeaderMiddleware($container->get('config')['dot_response_headers'] ?? []);
    }
}