<?php

declare(strict_types=1);

namespace Dot\ResponseHeader;

use Dot\ResponseHeader\Factory\ResponseHeaderMiddlewareFactory;
use Dot\ResponseHeader\Middleware\ResponseHeaderMiddleware;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => $this->getDependencies(),
        ];
    }

    public function getDependencies(): array
    {
        return [
            'factories' => [
                ResponseHeaderMiddleware::class => ResponseHeaderMiddlewareFactory::class,
            ],
        ];
    }
}
