<?php

namespace ConfigToken\TokenFilter\Exception;


/**
 * @codeCoverageIgnore
 */
class UnknownFilterException extends \Exception
{
    public function __construct($filterName = "", $scope = "", $code = 0, \Exception $previous = null)
    {
        if ($scope == "") {
            $message = sprintf('Unknown filter "%s".', $filterName);
        } else {
            $message = sprintf('Unknown filter "%s" in "%s".', $filterName, $scope);
        }
        parent::__construct($message, $code, $previous);
    }
}