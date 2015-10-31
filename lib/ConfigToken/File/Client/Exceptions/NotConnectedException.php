<?php

namespace ConfigToken\File\Client\Exceptions;


class NotConnectedException extends \Exception
{
    public function __construct($file = '', $code = 0, \Exception $previous = null)
    {
        if ($file != '') {
            $file = sprintf(' while attempting to read file [%s].', $file);
        }
        $message = sprintf('File client not connected%s.', $file);
        parent::__construct($message, $code, $previous);
    }
}