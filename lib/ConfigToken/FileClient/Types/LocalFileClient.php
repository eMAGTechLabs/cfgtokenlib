<?php

namespace ConfigToken\FileClient\Types;


use ConfigToken\ConnectionSettings\Types\LocalFileConnectionSettings;
use ConfigToken\FileUtils;

class LocalFileClient extends AbstractFileClient
{
    /**
     * Get the file server type identifier corresponding to the client's implementation.
     *
     * @return string
     */
    public static function getServerType()
    {
        return 'file';
    }

    /**
     * Return the file contents.
     *
     * @param string $fileName
     * @throws \Exception
     * @return array(string, string) [$contentType, $content]
     */
    public function readFile($fileName)
    {
        $this->validateConnectionSettings();
        /** @var LocalFileConnectionSettings $connectionSettings */
        $connectionSettings = $this->getConnectionSettings();
        $rootPath = $connectionSettings->getRootPath();
        $normalizedPath = FileUtils::normalizePath($fileName, true);
        $localFileName = $rootPath . DIRECTORY_SEPARATOR . $normalizedPath;
        if (!file_exists($localFileName)) {
            throw new \Exception($this->getFileNotFoundMessage($fileName));
        }
        $content = file_get_contents($fileName);
        $contentType = FileUtils::getContentTypeFromFileName($fileName);
        return array($contentType, $content);
    }
}