<?php

namespace ConfigToken\TokenResolver\Exception;


class UnknownTokenException extends \Exception
{
    public function __construct($tokenName = "", $scope = "", $code = 0, \Exception $previous = null)
    {
        if ($scope == "") {
            $message = sprintf('Unknown token "%s".', $tokenName);
        } else {
            $message = sprintf('Unknown token "%s" in "%s".', $tokenName, $scope);
        }
        parent::__construct($message, $code, $previous);
    }
}