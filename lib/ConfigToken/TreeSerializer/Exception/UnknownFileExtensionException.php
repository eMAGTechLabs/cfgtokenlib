<?php

namespace ConfigToken\TreeSerializer\Exception;

use ConfigToken\Exception\NotRegisteredException;


class UnknownFileExtensionException extends NotRegisteredException
{
    public function __construct($fileExtension = "", $code = 0, \Exception $previous = null)
    {
        $message = sprintf('Unknown tree serializer file extension %s.', $fileExtension);
        parent::__construct($message, $code, $previous);
    }
}