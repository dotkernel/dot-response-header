<?php

declare(strict_types=1);

namespace DotTest\ResponseHeader\Factory;

use ArrayObject;
use Dot\ResponseHeader\Exception\InvalidConfigException;
use Dot\ResponseHeader\Factory\ResponseHeaderMiddlewareFactory;
use DotTest\ResponseHeader\Fixture\ArrayContainer;
use DotTest\ResponseHeader\Fixture\FixedResponseHandler;
use Laminas\Diactoros\ServerRequest;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ResponseHeaderMiddlewareFactoryTest extends TestCase
{
    public function testThrowsWhenConfigServiceIsMissing(): void
    {
        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage(ResponseHeaderMiddlewareFactory::MESSAGE_MISSING_CONFIG);

        (new ResponseHeaderMiddlewareFactory())(new ArrayContainer());
    }

    public function testThrowsWhenConfigServiceIsNotAnArray(): void
    {
        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage(ResponseHeaderMiddlewareFactory::MESSAGE_MISSING_CONFIG);

        (new ResponseHeaderMiddlewareFactory())(new ArrayContainer(['config' => null]));
    }

    /**
     * @return iterable<string, array{array<mixed>}>
     */
    public static function missingPackageConfigProvider(): iterable
    {
        yield 'key missing' => [['other' => []]];
        yield 'key is a string' => [['dot_response_headers' => 'X-Header']];
        yield 'key is null' => [['dot_response_headers' => null]];
    }

    /**
     * @param array<mixed> $config
     */
    #[DataProvider('missingPackageConfigProvider')]
    public function testThrowsWhenPackageConfigIsMissing(array $config): void
    {
        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage(ResponseHeaderMiddlewareFactory::MESSAGE_MISSING_PACKAGE_CONFIG);

        (new ResponseHeaderMiddlewareFactory())(new ArrayContainer(['config' => $config]));
    }

    public function testEmptyPackageConfigCreatesMiddlewareThatAddsNothing(): void
    {
        $middleware = (new ResponseHeaderMiddlewareFactory())(
            new ArrayContainer(['config' => ['dot_response_headers' => []]])
        );

        $response = $middleware->process(new ServerRequest(), new FixedResponseHandler());

        $this->assertSame([], $response->getHeaders());
    }

    public function testAcceptsArrayAccessConfig(): void
    {
        $config     = new ArrayObject(['dot_response_headers' => ['*' => ['X-All' => ['value' => 'all']]]]);
        $middleware = (new ResponseHeaderMiddlewareFactory())(new ArrayContainer(['config' => $config]));

        $response = $middleware->process(new ServerRequest(), new FixedResponseHandler());

        $this->assertSame('all', $response->getHeaderLine('X-All'));
    }

    public function testInvalidPackageConfigFailsWhenTheMiddlewareIsCreated(): void
    {
        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage('dot_response_headers.* must be an array of headers, string given.');

        (new ResponseHeaderMiddlewareFactory())(
            new ArrayContainer(['config' => ['dot_response_headers' => ['*' => 'X-All']]])
        );
    }
}
