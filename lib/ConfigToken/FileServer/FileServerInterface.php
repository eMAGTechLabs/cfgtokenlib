<?php

namespace ConfigToken\FileServer;


use ConfigToken\ConnectionSettings\ConnectionSettingsInterface;

/**
 * File server interface.
 * @package ConfigToken\FileServer
 */
interface FileServerInterface
{
    /**
     * Get the file server type identifier corresponding to the client's implementation.
     *
     * @return string
     */
    public static function getServerType();

    /**
     * Get the connection settings interface.
     *
     * @return ConnectionSettingsInterface
     */
    public function getConnectionSettings();

    /**
     * Process either the current request or the request with the specified parameters and method.
     *
     * @param boolean $output If true, standard error messages will be printed.
     * @param array $request The request parameters.
     * @param string $requestMethod The request method.
     * @return boolean
     */
    public function processRequest($output = true, $request = null, $requestMethod = null);

    /**
     * Serve the specified file to standard output.
     *
     * @param string $file The file to be served.
     * @param boolean $output If true, error messages will be printed.
     * @return boolean
     */
    public function serveFile($file, $output = true);
}