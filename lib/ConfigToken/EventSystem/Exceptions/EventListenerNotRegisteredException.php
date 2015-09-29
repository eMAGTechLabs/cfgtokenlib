<?php

namespace ConfigToken\EventSystem\Exceptions;


class EventListenerNotRegisteredException extends \Exception
{
    public function __construct($message = "Event listener not registered.", $code = 0, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

}