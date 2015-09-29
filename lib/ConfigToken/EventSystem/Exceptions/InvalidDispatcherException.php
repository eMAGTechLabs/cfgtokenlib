<?php

namespace ConfigToken\EventSystem\Exceptions;


class InvalidDispatcherException extends \Exception
{
    public function __construct($class, $code = 0, \Exception $previous = null)
    {
        $message = sprintf(
            'The event dispatcher implementation class %s does not implement the EventDispatcherInterface.',
            $class
        );
        parent::__construct($message, $code, $previous);
    }
}