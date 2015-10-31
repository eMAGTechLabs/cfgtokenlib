<?php

namespace ConfigToken\File\Client\Exceptions;


class FileNotFoundException extends \Exception
{
    public function __construct($fileName, $code = 0, \Exception $previous = null)
    {
        $message = sprintf('File not found "%s".', $fileName);
        parent::__construct($message, $code, $previous);
    }
}