<?php

namespace ConfigToken\File\Client\Exceptions;


class CredentialsManagerNotSetException extends \Exception
{
    public function __construct($message = "", $code = 0, \Exception $previous = null)
    {
        parent::__construct('The credentials manager was not set.', $code, $previous);
    }
}