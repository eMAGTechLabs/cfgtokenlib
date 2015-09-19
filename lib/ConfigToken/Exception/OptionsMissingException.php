<?php

namespace ConfigToken\Exception;


use Exception;

class OptionsMissingException extends OptionValueException
{
    public function __construct($missingOptions, $message = 'The following option(s) are required: ["%s"]',
                                $code = 0, Exception $previous = null)
    {
        $message = sprintf($message, implode('", "', $missingOptions));
        parent::__construct($message, $code, $previous);
    }

}