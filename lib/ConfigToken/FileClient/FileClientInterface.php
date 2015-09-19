<?php

namespace ConfigToken\FileClient;


use ConfigToken\ConnectionSettings\ConnectionSettingsInterface;

/**
 * File client interface.
 * @package ConfigToken\FileServer
 */
interface FileClientInterface
{
    /**
     * Get the file server type identifier corresponding to the client's implementation.
     *
     * @return string
     */
    public static function getServerType();

    /**
     * Get the implementation class name.
     *
     * @return string
     */
    public static function getClassName();

    /**
     * Check if the connection settings have been set.
     *
     * @return boolean
     */
    public function hasConnectionSettings();

    /**
     * Get the connection settings.
     *
     * @return ConnectionSettingsInterface
     */
    public function getConnectionSettings();

    /**
     * Obtain an unique identifier for the given file name based on the connection settings.
     *
     * @param string $file
     * @return string
     */
    public function getFileLocationHash($file);

    /**
     * Return the file content type and content.
     *
     * @param string $fileName
     * @throws \Exception
     * @return array(string, string) [$contentType, $content]
     */
    public function readFile($fileName);
}