<?php

namespace ConfigToken\File\Client\Exceptions;


class FileClientNotRegisteredException extends \Exception
{
    public function __construct($id, $code = 0, \Exception $previous = null)
    {
        $message = sprintf('There is no file client registered with the id "%s".', $id);
        parent::__construct($message, $code, $previous);
    }
}