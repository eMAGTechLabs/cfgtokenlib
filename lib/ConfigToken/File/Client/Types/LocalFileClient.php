<?php

namespace ConfigToken\File\Client\Types;


use ConfigToken\File\Client\Exceptions\NotConnectedException;
use ConfigToken\File\ConnectionSettings\ConnectionSettingsInterface;
use ConfigToken\File\ConnectionSettings\Types\LocalFileClientConnectionSettings;
use ConfigToken\Utils\FileUtils;

class LocalFileClient extends AbstractFileClient
{
    /**
     * Get the unique id of the implementation.
     * @return string
     */
    public static function getId()
    {
        return 'file';
    }

    /**
     * Return a new instance of the connection settings implementation class.
     *
     * @param ConnectionSettingsInterface|null $connectionSettings
     * @return LocalFileClientConnectionSettings
     */
    public static function makeConnectionSettings(ConnectionSettingsInterface $connectionSettings = null)
    {
        return new LocalFileClientConnectionSettings($connectionSettings);
    }

    /**
     * Override to implement other methods of fetching file contents.
     *
     * @param $fileName
     * @return string
     */
    protected function getContents($fileName)
    {
        return FileUtils::getContents($fileName);
    }

    /**
     * Read the contents of the given file.
     *
     * @param string $fileName
     * @param boolean|true $relativeToPath
     * @param boolean|true $updatePath
     * @throws NotConnectedException
     * @return string
     */
    public function readFile($fileName, $relativeToPath = true, $updatePath = true)
    {
        $actualFileName = $this->getActualFileName($fileName, $relativeToPath);
        $contents = static::getContents($actualFileName);
        if ($updatePath) {
            $connectionSettings = $this->getConnectionSettings();
            $directorySeparator = $connectionSettings->getDirectorySeparator();
            $path = FileUtils::extractPath($actualFileName, $directorySeparator);
            $this->setPath($path, false);
        }
        return $contents;
    }
}