<?php

namespace ConfigToken\FileClient\Types;


use ConfigToken\ConnectionSettings\Types\RemoteFileConnectionSettings;
use ConfigToken\FileClient\Exception\FileClientConnectionException;

class RemoteFileClient extends AbstractFileClient
{
    /**
     * Get the file server type identifier corresponding to the client's implementation.
     *
     * @return string
     */
    public static function getServerType()
    {
        return 'remote-file';
    }

    /**
     * Return the file contents.
     *
     * @param string $fileName
     * @throws FileClientConnectionException
     * @return array(string, string) [$contentType, $content]
     */
    public function readFile($fileName)
    {
        $this->validateConnectionSettings();

        /** @var RemoteFileConnectionSettings $connectionSettings */
        $connectionSettings = $this->getConnectionSettings();
        $postFields = http_build_query(
            array(
                $connectionSettings->getFieldName() => $fileName
            )
        );
        $ch = curl_init();
        if ($connectionSettings->getRequestMethod() == 'GET') {
            curl_setopt($ch, CURLOPT_URL, $connectionSettings->getUrl());
        } else {
            curl_setopt($ch, CURLOPT_URL, $connectionSettings->getUrl() . '?' . $postFields);
        }
        if ($connectionSettings->getRequestMethod() == 'POST') {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $content = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        curl_close($ch);

        if ($httpCode == 404) {
            throw new FileClientConnectionException(
                sprintf(
                    'File "%s" not found on "%s".',
                    $fileName,
                    $connectionSettings->getUrl()
                )
            );
        } else if ($httpCode != 200) {
            throw new FileClientConnectionException(
                sprintf(
                    'Unable to read file "%s" from "%s": Got response code %d.',
                    $fileName,
                    $connectionSettings->getUrl(),
                    $httpCode
                )
            );
        }
        return array($contentType, $content);
    }
}