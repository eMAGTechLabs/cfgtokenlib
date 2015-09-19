<?php

namespace ConfigToken\FileClient;


use ConfigToken\ConnectionSettings\ConnectionSettingsInterface;
use ConfigToken\FileClient\Exception\UnknownServerTypeException;
use ConfigToken\FileClient\Types\GitLabRepoClient;
use ConfigToken\FileClient\Types\LocalFileClient;
use ConfigToken\FileClient\Types\RemoteFileClient;

class FileClientFactory
{
    /** @var string[] */
    protected static $registeredByServerType = array();

    protected static function registerKnownTypes()
    {
        if (!empty(static::$registeredByServerType)) {
            return;
        }
        static::register(LocalFileClient::getServerType(), LocalFileClient::getClassName());
        static::register(RemoteFileClient::getServerType(), RemoteFileClient::getClassName());
        static::register(GitLabRepoClient::getServerType(), GitLabRepoClient::getClassName());
    }

    public static function register($serverType, $clientClassName)
    {
        static::$registeredByServerType[$serverType] = $clientClassName;
    }

    /**
     * @param string $serverType
     * @return boolean
     */
    public static function isRegistered($serverType)
    {
        static::registerKnownTypes();
        return isset(static::$registeredByServerType[$serverType]);
    }

    /**
     * @param string $serverType
     * @return FileClientInterface
     *
     * @throws UnknownServerTypeException
     */
    public static function getClassName($serverType)
    {
        static::registerKnownTypes();
        if (isset(static::$registeredByServerType[$serverType])) {
            return static::$registeredByServerType[$serverType];
        }
        throw new UnknownServerTypeException($serverType);
    }

    public static function make($serverType, ConnectionSettingsInterface $connectionSettings = null)
    {
        if (!static::isRegistered($serverType)) {
            throw new UnknownServerTypeException($serverType);
        }
        $clientClassName = static::getClassName($serverType);
        return new $clientClassName($connectionSettings);
    }
}