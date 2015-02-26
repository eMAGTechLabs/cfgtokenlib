<?php

namespace ConfigToken\TreeSerializer\Exception;

use ConfigToken\Exception\NotRegisteredException;


class UnknownContentTypeException extends NotRegisteredException
{
    public function __construct($contentType = "", $code = 0, \Exception $previous = null)
    {
        $message = sprintf('Unknown tree serializer content type %s.', $contentType);
        parent::__construct($message, $code, $previous);
    }
}