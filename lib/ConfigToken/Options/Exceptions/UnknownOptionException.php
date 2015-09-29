<?php

namespace ConfigToken\Options\Exceptions;


use Exception;

class UnknownOptionException extends \Exception
{
    public function __construct($optionKey, $message = "", $code = 0, \Exception $previous = null)
    {
        $message = sprintf('Unknown option key "%s".%s', $optionKey, $message);
        parent::__construct($message, $code, $previous);
    }
}