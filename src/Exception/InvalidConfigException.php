<?php

declare(strict_types=1);

namespace Dot\ResponseHeader\Exception;

use InvalidArgumentException;

use function get_debug_type;
use function sprintf;

class InvalidConfigException extends InvalidArgumentException
{
    public static function routeNotArray(int|string $route, mixed $value): self
    {
        return new self(sprintf(
            'dot_response_headers.%s must be an array of headers, %s given.',
            $route,
            get_debug_type($value)
        ));
    }

    public static function headerNotArray(int|string $route, int|string $header, mixed $value): self
    {
        return new self(sprintf(
            'dot_response_headers.%s.%s must be an array with a "value" key, %s given.',
            $route,
            $header,
            get_debug_type($value)
        ));
    }

    public static function invalidValue(int|string $route, int|string $header, mixed $value): self
    {
        return new self(sprintf(
            'dot_response_headers.%s.%s.value must be a string, a number or a list of strings, %s given.',
            $route,
            $header,
            get_debug_type($value)
        ));
    }

    public static function invalidOverwrite(int|string $route, int|string $header, mixed $value): self
    {
        return new self(sprintf(
            'dot_response_headers.%s.%s.overwrite must be a bool, %s given.',
            $route,
            $header,
            get_debug_type($value)
        ));
    }
}
