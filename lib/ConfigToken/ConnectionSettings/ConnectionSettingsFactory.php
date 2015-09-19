<?php

namespace ConfigToken\ConnectionSettings;


use ConfigToken\ConnectionSettings\Types\GenericConnectionSettings;
use ConfigToken\ConnectionSettings\Types\GitLabRepoConnectionSettings;
use ConfigToken\ConnectionSettings\Types\LocalFileConnectionSettings;
use ConfigToken\ConnectionSettings\Types\RemoteFileConnectionSettings;
use ConfigToken\FileClient\Exception\UnknownServerTypeException;
use ConfigToken\FileClient\FileClientInterface;

class ConnectionSettingsFactory
{
    /** @var string[] */
    protected static $registeredByServerType = array();
    /** @var string|null */
    protected static $fallbackClassName = null;

    protected static function registerKnownTypes()
    {
        if (!empty(static::$registeredByServerType)) {
            return;
        }
        static::register(GenericConnectionSettings::getServerType(), GenericConnectionSettings::getClassName());
        static::register(LocalFileConnectionSettings::getServerType(), LocalFileConnectionSettings::getClassName());
        static::register(RemoteFileConnectionSettings::getServerType(), RemoteFileConnectionSettings::getClassName());
        static::register(GitLabRepoConnectionSettings::getServerType(), GitLabRepoConnectionSettings::getClassName());
    }

    /**
     * Register the given class name for the given server type.
     *
     * @param string|null $serverType If null, register fallback class name.
     * @param $connectionSettingsClassName
     */
    public static function register($serverType, $connectionSettingsClassName)
    {
        if (isset($serverType)) {
            static::$registeredByServerType[$serverType] = $connectionSettingsClassName;
        } else {
            static::$fallbackClassName = $connectionSettingsClassName;
        }
    }

    /**
     * Check if there is a class name registered for the given server type.
     *
     * @param string $serverType
     * @return boolean
     */
    public static function isRegistered($serverType)
    {
        static::registerKnownTypes();
        if (isset($serverType)) {
            return isset(static::$registeredByServerType[$serverType]);
        } else {
            return isset(static::$fallbackClassName);
        }
    }

    /**
     * Get the class name corresponding to the given server type.
     *
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
        if (isset(static::$fallbackClassName)) {
            return static::$fallbackClassName;
        }
        throw new UnknownServerTypeException($serverType);
    }

    /**
     * Create a ConnectionSettings instance for the given server type.
     *
     * @param string $serverType
     * @param array|null $parameters
     * @return mixed
     * @throws UnknownServerTypeException
     */
    public static function make($serverType, $parameters = null)
    {
        if (!static::isRegistered($serverType)) {
            throw new UnknownServerTypeException($serverType);
        }
        $connectionSettingsClassName = static::getClassName($serverType);
        return new $connectionSettingsClassName($parameters);
    }
}