<?php

namespace ConfigToken\File\Client\Exceptions;


class InvalidPathException extends \Exception
{
    public function __construct($path, $code = 0, \Exception $previous = null)
    {
        $message = sprintf('Invalid path "%s".', $path);
        parent::__construct($message, $code, $previous);
    }
}