<?php

declare(strict_types=1);

namespace Dot\ResponseHeader;

use Dot\ResponseHeader\Factory\ResponseHeaderMiddlewareFactory;
use Dot\ResponseHeader\Middleware\ResponseHeaderMiddleware;

final class ConfigProvider
{
    /**
     * @return array{dependencies: array{factories: array<class-string, class-string>}}
     */
    public function __invoke(): array
    {
        return [
            'dependencies' => $this->getDependencies(),
        ];
    }

    /**
     * @return array{factories: array<class-string, class-string>}
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
