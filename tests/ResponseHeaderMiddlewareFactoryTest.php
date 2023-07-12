<?php

declare(strict_types=1);

namespace DotTest\ResponseHeader;

use Dot\ResponseHeader\Factory\ResponseHeaderMiddlewareFactory;
use Dot\ResponseHeader\Middleware\ResponseHeaderMiddleware;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class ResponseHeaderMiddlewareFactoryTest extends TestCase
{
    private ResponseHeaderMiddlewareFactory $responseHeaderMiddlewareFactory;

    private ContainerInterface|MockObject $containerInterface;

    public function setUp(): void
    {
        parent::setUp();

        $this->responseHeaderMiddlewareFactory = new ResponseHeaderMiddlewareFactory();

        $this->containerInterface = $this->createMock(ContainerInterface::class);
    }

    public function testInvoke()
    {
        $data = $this->responseHeaderMiddlewareFactory->__invoke($this->containerInterface);

        $this->assertInstanceOf(ResponseHeaderMiddleware::class, $data);
    }
}
