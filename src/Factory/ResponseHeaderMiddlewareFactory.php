<?php

declare(strict_types=1);

namespace Dot\ResponseHeader\Factory;

use Dot\ResponseHeader\Middleware\ResponseHeaderMiddleware;
use Exception;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

use function array_key_exists;
use function is_array;

class ResponseHeaderMiddlewareFactory
{
    public const MESSAGE_MISSING_CONFIG         = 'Unable to find config.';
    public const MESSAGE_MISSING_PACKAGE_CONFIG = 'Unable to find dot-response-header config.';

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws Exception
     */
    public function __invoke(ContainerInterface $container): ResponseHeaderMiddleware
    {
        if (! $container->has('config')) {
            throw new Exception(self::MESSAGE_MISSING_CONFIG);
        }
        $config = $container->get('config');

        if (
            ! array_key_exists('dot_response_headers', $config)
            || ! is_array($config['dot_response_headers'])
            || empty($config['dot_response_headers'])
        ) {
            throw new Exception(self::MESSAGE_MISSING_PACKAGE_CONFIG);
        }

        return new ResponseHeaderMiddleware($container->get('config')['dot_response_headers'] ?? []);
    }
}
