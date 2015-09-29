<?php

namespace ConfigToken\Options\Exceptions;


class OptionValueException extends \Exception
{
    public function __construct($optionKey, $message = "", $code = 0, \Exception $previous = null)
    {
        $message = sprintf('Invalid value for option "%s". %s', $optionKey, $message);
        parent::__construct($message, $code, $previous);
    }
}