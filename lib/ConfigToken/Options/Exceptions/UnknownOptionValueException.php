<?php

namespace ConfigToken\Options\Exceptions;


class UnknownOptionValueException extends \Exception
{
    public function __construct($optionKey, $message = "", $code = 0, \Exception $previous = null)
    {
        $message = sprintf('Option key(s) "%s": %s', $optionKey, $message);
        parent::__construct($message, $code, $previous);
    }
}