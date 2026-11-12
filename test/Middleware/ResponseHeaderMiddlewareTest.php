<?php

declare(strict_types=1);

namespace DotTest\ResponseHeader\Middleware;

use Dot\ResponseHeader\Exception\InvalidConfigException;
use Dot\ResponseHeader\Middleware\ResponseHeaderMiddleware;
use DotTest\ResponseHeader\Fixture\FixedResponseHandler;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\ServerRequest;
use Mezzio\Router\Route;
use Mezzio\Router\RouteResult;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class ResponseHeaderMiddlewareTest extends TestCase
{
    private const CONFIG = [
        '*'    => [
            'X-All'       => ['value' => 'all', 'overwrite' => false],
            'X-Overwrite' => ['value' => 'all', 'overwrite' => true],
        ],
        'home' => [
            'X-Home'      => ['value' => 'home'],
            'X-All'       => ['value' => 'home', 'overwrite' => true],
            'X-Overwrite' => ['value' => 'home', 'overwrite' => false],
        ],
    ];

    public function testEmptyConfigReturnsTheHandlerResponseUnchanged(): void
    {
        $handler  = new FixedResponseHandler(['X-Existing' => 'kept']);
        $response = (new ResponseHeaderMiddleware([]))->process(new ServerRequest(), $handler);

        $this->assertSame($handler->handle(new ServerRequest()), $response);
    }

    public function testWithoutRouteResultOnlyCommonHeadersAreAdded(): void
    {
        $response = (new ResponseHeaderMiddleware(self::CONFIG))
            ->process(new ServerRequest(), new FixedResponseHandler());

        $this->assertSame(['X-All' => ['all'], 'X-Overwrite' => ['all']], $response->getHeaders());
    }

    public function testRouteFailureOnlyAddsCommonHeaders(): void
    {
        $request  = (new ServerRequest())->withAttribute(RouteResult::class, RouteResult::fromRouteFailure(null));
        $response = (new ResponseHeaderMiddleware(self::CONFIG))->process($request, new FixedResponseHandler());

        $this->assertSame(['X-All' => ['all'], 'X-Overwrite' => ['all']], $response->getHeaders());
    }

    public function testMatchedRouteAddsRouteHeadersAfterCommonHeaders(): void
    {
        $response = (new ResponseHeaderMiddleware(self::CONFIG))
            ->process($this->requestForRoute('home'), new FixedResponseHandler());

        $this->assertSame('home', $response->getHeaderLine('X-Home'));
        $this->assertSame('home', $response->getHeaderLine('X-All'), 'route header with overwrite replaces *');
        $this->assertSame('all', $response->getHeaderLine('X-Overwrite'), 'route header without overwrite keeps *');
    }

    public function testRouteWithoutConfigOnlyAddsCommonHeaders(): void
    {
        $response = (new ResponseHeaderMiddleware(self::CONFIG))
            ->process($this->requestForRoute('login'), new FixedResponseHandler());

        $this->assertSame(['X-All' => ['all'], 'X-Overwrite' => ['all']], $response->getHeaders());
    }

    public function testHeaderSetByTheHandlerIsKeptUnlessOverwriteIsTrue(): void
    {
        $handler  = new FixedResponseHandler(['x-all' => 'handler', 'x-overwrite' => 'handler']);
        $response = (new ResponseHeaderMiddleware(self::CONFIG))->process(new ServerRequest(), $handler);

        $this->assertSame('handler', $response->getHeaderLine('X-All'));
        $this->assertSame('all', $response->getHeaderLine('X-Overwrite'));
    }

    public function testHeaderWithoutValueIsSkipped(): void
    {
        $response = (new ResponseHeaderMiddleware(['*' => ['X-Empty' => ['overwrite' => true]]]))
            ->process(new ServerRequest(), new FixedResponseHandler());

        $this->assertFalse($response->hasHeader('X-Empty'));
    }

    /**
     * @return iterable<string, array{mixed, list<string>}>
     */
    public static function validValueProvider(): iterable
    {
        yield 'string' => ['text', ['text']];
        yield 'int' => [3600, ['3600']];
        yield 'float' => [1.5, ['1.5']];
        yield 'list of strings' => [['Accept', 'Origin'], ['Accept', 'Origin']];
    }

    /**
     * @param list<string> $expected
     */
    #[DataProvider('validValueProvider')]
    public function testAcceptedValueTypes(mixed $value, array $expected): void
    {
        $response = (new ResponseHeaderMiddleware(['*' => ['X-Value' => ['value' => $value]]]))
            ->addHeaders(new Response(), '*');

        $this->assertSame($expected, $response->getHeader('X-Value'));
    }

    /**
     * @return iterable<string, array{array<mixed>, string}>
     */
    public static function invalidConfigProvider(): iterable
    {
        yield 'route is a string' => [
            ['*' => 'X-All'],
            'dot_response_headers.* must be an array of headers, string given.',
        ];
        yield 'header is a string' => [
            ['home' => ['X-Home' => 'home']],
            'dot_response_headers.home.X-Home must be an array with a "value" key, string given.',
        ];
        yield 'value is null' => [
            ['*' => ['X-All' => ['value' => null]]],
            'dot_response_headers.*.X-All.value must be a string, a number or a list of strings, null given.',
        ];
        yield 'value is an empty array' => [
            ['*' => ['X-All' => ['value' => []]]],
            'dot_response_headers.*.X-All.value must be a string, a number or a list of strings, array given.',
        ];
        yield 'value list holds a non-string' => [
            ['*' => ['X-All' => ['value' => ['a', 1]]]],
            'dot_response_headers.*.X-All.value must be a string, a number or a list of strings, array given.',
        ];
        yield 'overwrite is an int' => [
            ['*' => ['X-All' => ['value' => 'a', 'overwrite' => 1]]],
            'dot_response_headers.*.X-All.overwrite must be a bool, int given.',
        ];
    }

    /**
     * @param array<mixed> $config
     */
    #[DataProvider('invalidConfigProvider')]
    public function testInvalidConfigIsRejectedOnConstruction(array $config, string $message): void
    {
        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage($message);

        new ResponseHeaderMiddleware($config);
    }

    private function requestForRoute(string $name): ServerRequestInterface
    {
        $middleware = new class implements MiddlewareInterface {
            public function process(
                ServerRequestInterface $request,
                RequestHandlerInterface $handler
            ): ResponseInterface {
                return $handler->handle($request);
            }
        };

        return (new ServerRequest())->withAttribute(
            RouteResult::class,
            RouteResult::fromRoute(new Route('/' . $name, $middleware, ['GET'], $name))
        );
    }
}
