<?php

namespace ConfigToken\TokenResolver\Exception;

use Exception;


/**
 * @codeCoverageIgnore
 */
class UnknownTokenSourceException extends \Exception
{
    public function __construct($message = "", $code = 0, Exception $previous = null)
    {
        $message = sprintf('%sString hash does not match token source hash.', (($message == "") ? "": $message . ". "));
        parent::__construct($message, $code, $previous);
    }
}