<?php

namespace ConfigToken\File\Client\Exceptions;


class ReadException extends \Exception
{
    public function __construct($fileName, $message="", $code = 0, \Exception $previous = null)
    {
        $message = sprintf('Unable to read "%s": %s.', $fileName, $message);
        parent::__construct($message, $code, $previous);
    }
}