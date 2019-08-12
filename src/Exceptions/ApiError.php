<?php

namespace Spatie\Newsletter\Exceptions;

use Exception;
use Throwable;
/**
 * Class ApiError
 * @package Spatie\Newsletter\Exceptions;
 */
class ApiError extends Exception
{
    /**
     * @param string $error
     * @param string $name
     * @param int $code
     * @param Throwable|null $previous
     * @return ApiError
     */
    public static function responseError(string $error, string $name, $code = 0, Throwable $previous = null)
    {
        return new static("{$name} returned an error: {$error}", $code, $previous);
    }
}