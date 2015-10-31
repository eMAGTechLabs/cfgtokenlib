<?php

namespace ConfigToken\File\Client\Types;


use ConfigToken\File\Client\Exceptions\ReadException;
use ConfigToken\File\ConnectionSettings\ConnectionSettingsInterface;
use ConfigToken\File\ConnectionSettings\Types\GitRepoFileClientConnectionSettings;

class GitRepoFileClient extends AbstractFileClient
{
    /**
     * Get the unique id of the implementation.
     * @return string
     */
    public static function getId()
    {
        return 'git';
    }

    /**
     * Return a new instance of the connection settings implementation class.
     *
     * @param ConnectionSettingsInterface|null $connectionSettings
     * @return GitRepoFileClientConnectionSettings
     */
    public static function makeConnectionSettings(ConnectionSettingsInterface $connectionSettings = null)
    {
        return new GitRepoFileClientConnectionSettings($connectionSettings);
    }

    /**
     * Read the contents of the given file.
     *
     * @param string $fileName
     * @param boolean|true $relativeToPath
     * @param boolean|true $updatePath
     * @throws ReadException
     * @return string
     */
    public function readFile($fileName, $relativeToPath = true, $updatePath = true)
    {
        throw new ReadException($fileName, sprintf('%s not implemented.', static::getClassName()));
    }
}