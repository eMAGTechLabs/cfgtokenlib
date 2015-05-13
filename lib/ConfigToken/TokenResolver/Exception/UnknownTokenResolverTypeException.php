<?php

namespace ConfigToken\TokenResolver\Exception;


class UnknownTokenResolverTypeException extends \Exception
{
    public function __construct($resolverType = "", $code = 0, \Exception $previous = null)
    {
        $message = sprintf('Unknown token resolver type identifier "%s".', $resolverType);
        parent::__construct($message, $code, $previous);
    }
}