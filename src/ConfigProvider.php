<?php


namespace Dot\ResponseHeader;

use Dot\ResponseHeader\Factory\ResponseHeaderMiddlewareFactory;
use Dot\ResponseHeader\Middleware\ResponseHeaderMiddleware;

/**
 * Class ConfigProvider
 * @package Dot\ResponseHeader
 */
class ConfigProvider
{
    /**
     * @return array
     */
    public function __invoke(): array
    {
        return [
            'dependencies' => $this->getDependencies(),
        ];
    }

    /**
     * @return array
     */
    public function getDependencies(): array
    {
        return [
            'factories' => [
                ResponseHeaderMiddleware::class => ResponseHeaderMiddlewareFactory::class,
            ],
        ];
    }
}