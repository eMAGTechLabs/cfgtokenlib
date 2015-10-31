<?php

namespace ConfigToken\File\Client;


use ConfigToken\File\Client\Exceptions\FileClientNotRegisteredException;
use ConfigToken\File\Client\Types\GitLabRepoFileClient;
use ConfigToken\File\Client\Types\GitRepoFileClient;
use ConfigToken\File\Client\Types\HttpFileClient;
use ConfigToken\File\Client\Types\LocalFileClient;

class FileClientFactory
{
    protected static $knownClientsRegistered = false;
    protected static $registeredClientClasses = array();

    protected static function registerKnownFileClients()
    {
        // prevent recursion
        if (static::$knownClientsRegistered) {
            return;
        } else {
            static::$knownClientsRegistered = true;
        }
        static::registerFileClient(LocalFileClient::getId(), LocalFileClient::getClassName());
        static::registerFileClient(HttpFileClient::getId(), HttpFileClient::getClassName());
        static::registerFileClient(GitRepoFileClient::getId(), GitRepoFileClient::getClassName());
        static::registerFileClient(GitLabRepoFileClient::getId(), GitLabRepoFileClient::getClassName());
    }

    public static function registerFileClient($id, $className)
    {
        static::registerKnownFileClients();
        static::$registeredClientClasses[$id] = $className;
    }

    public static function removeFileClient($id)
    {
        if (!static::isRegisteredFileClient($id)) {
            throw new FileClientNotRegisteredException($id);
        }
        unset(static::$registeredClientClasses[$id]);
    }

    public static function isRegisteredFileClient($id)
    {
        static::registerKnownFileClients();
        return isset(static::$registeredClientClasses[$id]);
    }

    /**
     * @param $id
     * @return FileClientInterface
     * @throws FileClientNotRegisteredException
     */
    public static function makeFileClient($id)
    {
        if (!static::isRegisteredFileClient($id)) {
            throw new FileClientNotRegisteredException($id);
        }
        return new static::$registeredClientClasses[$id]();
    }
}