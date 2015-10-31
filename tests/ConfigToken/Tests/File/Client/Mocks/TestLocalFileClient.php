<?php

namespace ConfigToken\Tests\File\Client\Mocks;


use ConfigToken\File\Client\Types\LocalFileClient;
use ConfigToken\File\ConnectionSettings\ConnectionSettingsInterface;

class TestLocalFileClient extends LocalFileClient
{
    public static $overrideConnectionSettings = true;

    /**
     * Get the unique id of the implementation.
     * @return string
     */
    public static function getId()
    {
        return 'local-test';
    }

    /**
     * Return a new instance of the connection settings implementation class.
     *
     * @param ConnectionSettingsInterface|null $connectionSettings
     * @return TestLocalFileClientConnectionSettings
     */
    public static function makeConnectionSettings(ConnectionSettingsInterface $connectionSettings = null)
    {
        if (static::$overrideConnectionSettings) {
            return new TestLocalFileClientConnectionSettings($connectionSettings);
        } else {
            return parent::makeConnectionSettings($connectionSettings);
        }
    }
}