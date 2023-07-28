<?php

declare(strict_types=1);

namespace DotTest\ResponseHeader\Factory;

use Dot\ResponseHeader\Factory\ResponseHeaderMiddlewareFactory;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class ResponseHeaderMiddlewareFactoryTest extends TestCase
{
    /**
     * @throws ContainerExceptionInterface
     * @throws Exception
     * @throws NotFoundExceptionInterface
     */
    public function testWillNotCreateApplicationWithoutConfig(): void
    {
        $container = $this->createMock(ContainerInterface::class);

        $container->expects($this->once())
            ->method('has')
            ->with('config')
            ->willReturn(false);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage(ResponseHeaderMiddlewareFactory::MESSAGE_MISSING_CONFIG);
        (new ResponseHeaderMiddlewareFactory())($container);
    }

    /**
     * @throws Exception
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testWillNotCreateApplicationWithoutPackageConfig(): void
    {
        $container = $this->createMock(ContainerInterface::class);

        $container->expects($this->once())
            ->method('has')
            ->with('config')
            ->willReturn(true);

        $container->expects($this->once())
            ->method('get')
            ->with('config')
            ->willReturn([
                'test',
            ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage(ResponseHeaderMiddlewareFactory::MESSAGE_MISSING_PACKAGE_CONFIG);
        (new ResponseHeaderMiddlewareFactory())($container);
    }
}
