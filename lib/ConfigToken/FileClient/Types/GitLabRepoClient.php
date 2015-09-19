<?php

namespace ConfigToken\FileClient\Types;


use ConfigToken\ConnectionSettings\Types\GitLabRepoConnectionSettings;
use ConfigToken\FileClient\Exception\FileClientConnectionException;
use ConfigToken\FileUtils;

class GitLabRepoClient extends AbstractFileClient
{
    /**
     * Get the file server type identifier corresponding to the client's implementation.
     *
     * @return string
     */
    public static function getServerType()
    {
        return 'gitlab';
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

        /** @var GitLabRepoConnectionSettings $connectionSettings */
        $connectionSettings = $this->getConnectionSettings();
        $remote = sprintf(
            'git@%s:%s/%s.git',
            $connectionSettings->getHostName(),
            $connectionSettings->getGroupName(),
            $connectionSettings->getRepoName()
        );
        $url = sprintf(
            "%s/projects/%s/repository/blobs/%s?private_token=%s&filepath=%s",
            $connectionSettings->getUrl(),
            str_replace('.', '%2E', urlencode($remote)),
            $connectionSettings->getNamedReference(),
            $connectionSettings->getApiToken(),
            $fileName
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, "application/json");

        $content = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        if ($httpCode != 200) {
            throw new FileClientConnectionException(
                sprintf(
                    'Unable to fetch file "%s" from %s (%s) via GitLab API %s. Got response code %s.',
                    $fileName,
                    $remote,
                    $connectionSettings->getNamedReference(),
                    $connectionSettings->getUrl(),
                    $httpCode
                )
            );
        }

        $contentType = FileUtils::getContentTypeFromFileName($fileName);
        return array($contentType, $content);
    }

}