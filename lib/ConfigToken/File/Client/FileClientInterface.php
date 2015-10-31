<?php

namespace ConfigToken\File\Client;


use ConfigToken\EventSystem\EventDispatcherInterface;
use ConfigToken\File\ConnectionSettings\ConnectionSettingsInterface;
use ConfigToken\File\Client\Exceptions\InvalidPathException;
use ConfigToken\File\Client\Exceptions\ReadException;
use ConfigToken\Options\Exceptions\UnknownOptionException;
use ConfigToken\Options\Exceptions\UnknownOptionValueException;

interface FileClientInterface
{
    /**
     * Get the implementation class name.
     *
     * @return string
     */
    public static function getClassName();

    /**
     * Get the unique id of the implementation.
     * @return string
     */
    public static function getId();

    /**
     * Return a new instance of the connection settings implementation class.
     *
     * @param ConnectionSettingsInterface|null $connectionSettings
     * @return ConnectionSettingsInterface
     */
    public static function makeConnectionSettings(ConnectionSettingsInterface $connectionSettings = null);

    /**
     * Attempt to establish the connection.
     *
     * @param ConnectionSettingsInterface|null $connectionSettings
     * @param EventDispatcherInterface|null $eventDispatcher
     * @throws UnknownOptionException
     * @throws UnknownOptionValueException
     * @return boolean
     */
    public function connect(ConnectionSettingsInterface $connectionSettings=null,
                            EventDispatcherInterface $eventDispatcher=null);

    /**
     * Disconnect if connected.
     *
     * @throws \Exception
     * @return void
     */
    public function disconnect();

    /**
     * Check if connected.
     *
     * @return boolean
     */
    public function isConnected();

    /**
     * Get the connection settings.
     *
     * @return ConnectionSettingsInterface|null
     */
    public function getConnectionSettings();

    /**
     * Get the current path.
     *
     * @param boolean|true $relative
     * @param string|null $directorySeparator
     * @return string
     */
    public function getPath($relative=false, $directorySeparator=null);

    /**
     * Set the current path.
     *
     * @param string $path
     * @param boolean|true $relative
     * @throws InvalidPathException
     * @return string
     */
    public function setPath($path, $relative=true);

    /**
     * Read the contents of the given file.
     *
     * @param string $fileName
     * @param boolean|true $relativeToPath
     * @param boolean|true $updatePath
     * @throws ReadException
     * @return string
     */
    public function readFile($fileName, $relativeToPath=true, $updatePath=true);
}