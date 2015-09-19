<?php

namespace ConfigToken\ConnectionSettings\Types;


use ConfigToken\FileClient\Types\GitLabRepoClient;

class GitLabRepoConnectionSettings extends GitRepoConnectionSettings
{
    const API_URL = 'url';
    const API_TOKEN = 'api-token';

    /**
     * Get the file server type identifier corresponding to the client's implementation.
     *
     * @return string|null If fallback class for all server types.
     */
    public static function getServerType()
    {
        return GitLabRepoClient::getServerType();
    }

    /**
     * Check if the GitLab API URL was set.
     *
     * @return boolean
     */
    public function hasUrl()
    {
        return $this->hasParameter(self::API_URL);
    }

    /**
     * Get the GitLab API URL.
     *
     * @return string|null
     */
    public function getUrl()
    {
        return $this->getParameter(self::API_URL);
    }

    /**
     * Set the GitLab API URL.
     *
     * @param string $value The new value.
     * @return $this
     */
    public function setUrl($value)
    {
        return $this->setParameter(self::API_URL, $value);
    }

    /**
     * Check if the GitLab API token was set.
     *
     * @return boolean
     */
    public function hasApiToken()
    {
        return $this->hasParameter(self::API_TOKEN);
    }

    /**
     * Get the GitLab API token.
     *
     * @return string|null
     */
    public function getApiToken()
    {
        return $this->getParameter(self::API_TOKEN);
    }

    /**
     * Set the GitLab API token.
     *
     * @param string $value The new value.
     * @return $this
     */
    public function setApiToken($value)
    {
        return $this->setParameter(self::API_TOKEN, $value);
    }

    /**
     * Get the required keys.
     *
     * @return array
     */
    protected static function getRequiredKeys()
    {
        $result = parent::getRequiredKeys();
        $result[] = static::API_URL;
        $result[] = static::API_TOKEN;
        return $result;
    }
}