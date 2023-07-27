<?php

declare(strict_types=1);

namespace DotTest\ResponseHeader;

use Dot\ResponseHeader\Middleware\ResponseHeaderMiddleware;
use Laminas\Diactoros\Response;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ResponseHeaderMiddlewareTest extends TestCase
{
    private ResponseHeaderMiddleware $responseHeader;

    private ServerRequestInterface|MockObject $serverRequest;

    private RequestHandlerInterface|MockObject $requestHandler;

    private ResponseInterface $responseInterface;

    /**
     * @throws Exception
     */
    public function setUp(): void
    {
        $this->responseHeader    = new ResponseHeaderMiddleware([]);
        $this->serverRequest     = $this->createMock(ServerRequestInterface::class);
        $this->requestHandler    = $this->createMock(RequestHandlerInterface::class);
        $this->responseInterface = $this->createMock(ResponseInterface::class);
    }

    public function testProcess()
    {
        $data = $this->responseHeader->process($this->serverRequest, $this->requestHandler);

        $this->assertInstanceOf(ResponseInterface::class, $data);
        $this->assertInstanceOf(StreamInterface::class, $data->getBody());
        $this->assertNotEmpty($data->getBody());
        $this->assertIsArray($data->getHeaders());
        $this->assertIsInt($data->getStatusCode());
        $this->assertIsString($data->getProtocolVersion());
        $this->assertIsString($data->getReasonPhrase());
    }

    public function testWillNotAddHeadersWithoutCommonWithoutRouteSpecificHeadersConfigured(): void
    {
        $responseHeader = new ResponseHeaderMiddleware([]);

        $response = new Response();
        $response = $responseHeader->addHeaders($response, 'test');

        $this->assertEmpty($response->getHeaders());
    }

    public function testWillAddHeadersWithCommonWithoutRouteSpecificHeadersConfigured(): void
    {
        $responseHeader = new ResponseHeaderMiddleware([
            '*' => [
                'CustomHeader1' => [
                    'value'     => 'CustomHeader1-Value',
                    'overwrite' => true,
                ],
                'CustomHeader2' => [
                    'value'     => 'CustomHeader2-Value',
                    'overwrite' => false,
                ],
            ],
        ]);

        $response = new Response();

        $response = $responseHeader->addHeaders($response, 'test');
        $this->assertEmpty($response->getHeaders());
        $this->assertFalse($response->hasHeader('CustomHeader1'));
        $this->assertFalse($response->hasHeader('CustomHeader2'));

        $response = $responseHeader->addHeaders($response, '*');
        $this->assertCount(2, $response->getHeaders());
        $this->assertTrue($response->hasHeader('CustomHeader1'));
        $this->assertTrue($response->hasHeader('CustomHeader2'));
    }
}
