<?php

namespace ConfigToken\Utils\Exceptions;


use Exception;

class UnknownCredentialException extends \Exception
{
    public function __construct($resourceHash, $key, $code = 0, Exception $previous = null)
    {
        $message = sprintf(
            'There is no value for credential with key "%s" in the context of the resource "%s".',
            $key,
            $resourceHash
        );
        parent::__construct($message, $code, $previous);
    }
}