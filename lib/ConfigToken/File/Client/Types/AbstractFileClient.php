<?php

namespace ConfigToken\File\Client\Types;


use ConfigToken\EventSystem\EventDispatcherInterface;
use ConfigToken\File\Client\Exceptions\NotConnectedException;
use ConfigToken\File\Client\FileClientInterface;
use ConfigToken\File\ConnectionSettings\ConnectionSettingsInterface;
use ConfigToken\File\ConnectionSettings\Types\LocalFileClientConnectionSettings;
use ConfigToken\Utils\FileUtils;

abstract class AbstractFileClient implements FileClientInterface
{
    /** @var LocalFileClientConnectionSettings */
    protected $connectionSettings;
    /** @var boolean */
    protected $connected = false;
    /** @var string */
    protected $path = '';

    /**
     * Get the implementation class name.
     *
     * @return string
     */
    public static function getClassName()
    {
        return get_called_class();
    }

    /**
     * Attempt to establish the connection.
     *
     * @param ConnectionSettingsInterface|null $connectionSettings
     * @param EventDispatcherInterface|null $eventDispatcher
     * @return boolean
     */
    public function connect(ConnectionSettingsInterface $connectionSettings = null,
                            EventDispatcherInterface $eventDispatcher=null)
    {
        $this->connectionSettings = static::makeConnectionSettings($connectionSettings);
        $this->connectionSettings->requestValues($eventDispatcher);
        $this->connectionSettings->validate(true);
        $this->connected = true;
        return $this->connected;
    }
    /**
     * Disconnect if connected.
     *
     * @throws \Exception
     * @return void
     */
    public function disconnect()
    {
        $this->connected = false;
        $this->connectionSettings = null;
    }

    /**
     * Check if connected.
     *
     * @return boolean
     */
    public function isConnected()
    {
        return $this->connected;
    }

    /**
     * Get the connection settings.
     *
     * @param string $message
     * @throws NotConnectedException
     * @return LocalFileClientConnectionSettings|null
     */
    protected function getConnectionSettingsCheck($message = '')
    {
        if (!isset($this->connectionSettings)) {
            throw new NotConnectedException($message);
        }
        return $this->connectionSettings;
    }

    /**
     * Get the connection settings.
     *
     * @return LocalFileClientConnectionSettings|null
     */
    public function getConnectionSettings()
    {
        return $this->connectionSettings;
    }

    /**
     * Get the directory separator value from the connection settings or raise exception.
     *
     * @param string $directorySeparatorOverride If not null, it will be returned instead.
     * @return string
     * @throws NotConnectedException
     */
    public function getDirectorySeparator($directorySeparatorOverride=null)
    {
        if (isset($directorySeparatorOverride)) {
            return $directorySeparatorOverride;
        }
        return $this->getConnectionSettingsCheck()->getDirectorySeparator();
    }

    /**
     * Get the current path.
     *
     * @param boolean|true $relative
     * @param string|null $directorySeparator
     * @throws NotConnectedException
     * @return string
     */
    public function getPath($relative=false, $directorySeparator=null)
    {
        $connectionSettings = $this->getConnectionSettingsCheck();
        $directorySeparator = $this->getDirectorySeparator($directorySeparator);
        $path = FileUtils::replaceDirectorySeparator($this->path, $directorySeparator);
        if ($relative) {
            return $path;
        }
        $rootPath = $connectionSettings->getRootPath($directorySeparator);
        $fullPath = FileUtils::joinPaths(array($rootPath, $path), $directorySeparator);
        return $fullPath;
    }

    /**
     * Set the current path.
     *
     * @param string $path
     * @param boolean|true $relative
     * @throws \Exception
     * @throws NotConnectedException
     * @return string
     */
    public function setPath($path, $relative=true)
    {
        $connectionSettings = $this->getConnectionSettingsCheck();
        $directorySeparator = $connectionSettings->getDirectorySeparator();
        $path = FileUtils::replaceDirectorySeparator($path, $directorySeparator);
        if (!$relative) {
            $path = FileUtils::makeRelative(
                $path,
                $connectionSettings->getRootPath($directorySeparator),
                $directorySeparator
            );
        }
        $this->path = $path;
        return $path;
    }

    /**
     * Get the actual file name.
     * Either via specifying a file with an absolute or relative path.
     *
     * @param $fileName
     * @param bool|true $relativeToPath
     * @return mixed|string
     * @throws NotConnectedException
     */
    protected function getActualFileName($fileName, $relativeToPath=true)
    {
        $connectionSettings = $this->getConnectionSettingsCheck($fileName);
        $directorySeparator = $connectionSettings->getDirectorySeparator();
        if ($relativeToPath) {
            $absolutePath = $this->getPath(false, $directorySeparator);
            return FileUtils::joinPaths(array($absolutePath, $fileName), $directorySeparator);
        } else {
            $rootPath = $connectionSettings->getRootPath($directorySeparator);
            $filePath = FileUtils::extractPath($fileName, $directorySeparator);
            $fileName = FileUtils::extractFile($fileName, $directorySeparator);
            $relativePath = FileUtils::makeRelative(
                $filePath,
                $connectionSettings->getRootPath($directorySeparator),
                $directorySeparator
            );
            return FileUtils::joinPaths(array($rootPath, $relativePath, $fileName), $directorySeparator);
        }
    }
}