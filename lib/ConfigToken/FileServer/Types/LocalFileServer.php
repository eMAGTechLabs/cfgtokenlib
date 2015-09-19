<?php

namespace ConfigToken\FileServer;


use ConfigToken\ConnectionSettings\Types\LocalFileConnectionSettings;
use ConfigToken\FileUtils;

class LocalFileServer extends AbstractFileServer
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
     * Returns the current HTTP request.
     *
     * @return array
     */
    public function getDefaultRequest()
    {
        return $_REQUEST;
    }

    /**
     * Returns the current HTTP request method or null if unavailable.
     *
     * @return string|null
     */
    public function getDefaultRequestMethod()
    {
        return isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : null;
    }

    /**
     * Returns the allowed request methods.
     *
     * @throws \Exception
     * @return array
     */
    public function getAllowedRequestMethods()
    {
        return array('GET', 'POST');
    }

    /**
     * Return the file size and content type for the given file name.
     * The content type defaults to application/octet-stream.
     *
     * @param string $filePath The path and file name.
     * @return array [int|false fileSize, string contentType]
     */
    public function getFileInfo($filePath)
    {
        $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
        $contentType = finfo_file($fileInfo, $filePath);
        finfo_close($fileInfo);
        if ($contentType === false) {
            $contentType = 'application/octet-stream';
        } elseif (($contentType == 'text/plain') && (strtolower(pathinfo($filePath, PATHINFO_EXTENSION)) == 'json')) {
            $contentType = 'application/json';
        }

        set_error_handler(function($errno, $errstr, $errfile, $errline, array $errcontext) {
            if (0 === error_reporting()) {
                return false;
            }
            throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
        });
        try {
            $fileSize = filesize($filePath);
        } catch (\ErrorException $e) {
            $fileSize = false;
        }
        restore_error_handler();

        return array($fileSize, $contentType);
    }

    /**
     * Serve the specified file to standard output.
     *
     * @param string $file The file to be served.
     * @param boolean $output If true, error messages will be printed.
     * @throws \Exception
     * @return boolean
     */
    public function serveFile($file, $output = true)
    {
        /** @var LocalFileConnectionSettings $connectionSettings */
        $connectionSettings = $this->getConnectionSettings();
        if (!$connectionSettings instanceof LocalFileConnectionSettings) {
            throw new \Exception('Invalid connection settings.');
        }
        $connectionSettings->validate();
        $normalizedFilePath = FileUtils::normalizePath($file, true);
        $filePath = $connectionSettings->getRootPath() . DIRECTORY_SEPARATOR . $normalizedFilePath;
        if (!file_exists($filePath)) {
            $this->setHttpResponseCode(404, $output);
            if ($output) {
                printf('<p>The file <strong>%s</strong> could not be found.</p>', $normalizedFilePath);
            }
            return false;
        }

        list($fileSize, $contentType) = $this->getFileInfo($filePath);

        $this->writeHeaders($contentType, $fileSize, $filePath);

        readfile($filePath);
        return true;
    }
}