<?php

namespace ConfigToken\EventSystem\Exceptions;


class EventDispatcherNotRegisteredException extends \Exception
{
    public function __construct($id, $code = 0, \Exception $previous = null)
    {
        $message = sprintf('Event dispatcher "%s" was not registered.', $id);
        parent::__construct($message, $code, $previous);
    }
}