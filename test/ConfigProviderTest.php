<?php

declare(strict_types=1);

namespace DotTest\ResponseHeader;

use Dot\ResponseHeader\ConfigProvider;
use Dot\ResponseHeader\Factory\ResponseHeaderMiddlewareFactory;
use Dot\ResponseHeader\Middleware\ResponseHeaderMiddleware;
use PHPUnit\Framework\TestCase;

final class ConfigProviderTest extends TestCase
{
    public function testInvokeReturnsDependencies(): void
    {
        $config = (new ConfigProvider())();

        $this->assertSame(['dependencies' => (new ConfigProvider())->getDependencies()], $config);
    }

    public function testMiddlewareIsRegisteredWithItsFactory(): void
    {
        $dependencies = (new ConfigProvider())->getDependencies();

        $this->assertSame(
            [ResponseHeaderMiddleware::class => ResponseHeaderMiddlewareFactory::class],
            $dependencies['factories']
        );
    }
}
