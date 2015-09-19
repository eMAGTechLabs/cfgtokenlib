<?php

namespace ConfigToken\ConnectionSettings\Exception;


use ConfigToken\ConnectionSettings\ConnectionSettingsInterface;

class ConnectionSettingsException extends \Exception
{
    public function __construct(ConnectionSettingsInterface $connectionSettings, $message = "", $code = 0,
                                \Exception $previous = null)
    {
        $message = sprintf(
            '%s: Invalid connection settings %s. %s',
            $connectionSettings->getClassName(),
            $connectionSettings->hasParameters() ? json_encode($connectionSettings->getParameters()) : '[None]',
            $message
        );
        parent::__construct($message, $code, $previous);
    }
}