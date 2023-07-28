<?php

declare(strict_types=1);

namespace DotTest\ResponseHeader\Middleware;

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

    /**
     * @throws Exception
     */
    public function setUp(): void
    {
        $this->responseHeader = new ResponseHeaderMiddleware([]);
        $this->serverRequest  = $this->createMock(ServerRequestInterface::class);
        $this->requestHandler = $this->createMock(RequestHandlerInterface::class);
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
        $config         = [
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
        ];
        $responseHeader = new ResponseHeaderMiddleware($config);

        $response = new Response();

        $response = $responseHeader->addHeaders($response, 'test');
        $this->assertEmpty($response->getHeaders());
        $this->assertFalse($response->hasHeader('CustomHeader1'));
        $this->assertFalse($response->hasHeader('CustomHeader2'));

        $response = $responseHeader->addHeaders($response, '*');
        $this->assertCount(2, $response->getHeaders());
        $this->assertTrue($response->hasHeader('CustomHeader1'));
        $this->assertTrue($response->hasHeader('CustomHeader2'));
        $this->assertSame($config['*']['CustomHeader1']['value'], $response->getHeaderLine('CustomHeader1'));
        $this->assertSame($config['*']['CustomHeader2']['value'], $response->getHeaderLine('CustomHeader2'));
    }

    public function testWillAddHeadersWithCommonWithRouteSpecificHeadersConfiguredWhenNoRouteMatched(): void
    {
        $config         = [
            '*'    => [
                'CustomHeader1' => [
                    'value'     => 'CustomHeader1-Value',
                    'overwrite' => true,
                ],
                'CustomHeader2' => [
                    'value'     => 'CustomHeader2-Value',
                    'overwrite' => false,
                ],
            ],
            'home' => [
                'CustomHeader' => [
                    'value' => 'header3',
                ],
            ],
        ];
        $responseHeader = new ResponseHeaderMiddleware($config);

        $response = new Response();

        $response = $responseHeader->addHeaders($response, 'test');
        $this->assertFalse($response->hasHeader('CustomHeader'));
        $this->assertEmpty($response->getHeaders());

        $response = $responseHeader->addHeaders($response, '*');
        $this->assertCount(2, $response->getHeaders());
        $this->assertTrue($response->hasHeader('CustomHeader1'));
        $this->assertTrue($response->hasHeader('CustomHeader2'));
        $this->assertSame($config['*']['CustomHeader1']['value'], $response->getHeaderLine('CustomHeader1'));
        $this->assertSame($config['*']['CustomHeader2']['value'], $response->getHeaderLine('CustomHeader2'));
    }

    public function testWillAddHeadersWithCommonWithRouteSpecificHeadersConfiguredWhenRouteMatched(): void
    {
        $config         = [
            '*'    => [
                'CustomHeader1' => [
                    'value'     => 'CustomHeader1-Value',
                    'overwrite' => true,
                ],
                'CustomHeader2' => [
                    'value'     => 'CustomHeader2-Value',
                    'overwrite' => true,
                ],
            ],
            'home' => [
                'CustomHeader'  => [
                    'value' => 'header3',
                ],
                'CustomHeader1' => [
                    'value'     => 'CustomHeader1-Overwritten-Value',
                    'overwrite' => true,
                ],
                'CustomHeader2' => [
                    'value'     => 'CustomHeader2-Overwritten-Value',
                    'overwrite' => false,
                ],
            ],
        ];
        $responseHeader = new ResponseHeaderMiddleware($config);

        $response = new Response();

        $this->assertFalse($response->hasHeader('CustomHeader1'));
        $this->assertFalse($response->hasHeader('CustomHeader2'));
        $this->assertFalse($response->hasHeader('CustomHeader'));
        $this->assertEmpty($response->getHeaders());

        $response = $responseHeader->addHeaders($response, '*');
        $this->assertCount(2, $response->getHeaders());
        $this->assertTrue($response->hasHeader('CustomHeader1'));
        $this->assertTrue($response->hasHeader('CustomHeader2'));
        $this->assertSame($config['*']['CustomHeader1']['value'], $response->getHeaderLine('CustomHeader1'));
        $this->assertSame($config['*']['CustomHeader2']['value'], $response->getHeaderLine('CustomHeader2'));

        $response = $responseHeader->addHeaders($response, 'home');
        $this->assertCount(3, $response->getHeaders());
        $this->assertTrue($response->hasHeader('CustomHeader'));
        $this->assertTrue($response->hasHeader('CustomHeader1'));
        $this->assertTrue($response->hasHeader('CustomHeader2'));
        $this->assertSame($config['home']['CustomHeader']['value'], $response->getHeaderLine('CustomHeader'));
        $this->assertSame($config['home']['CustomHeader1']['value'], $response->getHeaderLine('CustomHeader1'));
        $this->assertSame($config['*']['CustomHeader2']['value'], $response->getHeaderLine('CustomHeader2'));
        $this->assertNotSame($config['home']['CustomHeader2']['value'], $response->getHeaderLine('CustomHeader2'));
    }
}
