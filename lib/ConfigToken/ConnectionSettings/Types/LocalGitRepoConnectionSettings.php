<?php

namespace ConfigToken\ConnectionSettings\Types;


use ConfigToken\FileClient\Types\LocalGitRepoClient;

class LocalGitRepoConnectionSettings extends GitRepoConnectionSettings
{
    const GIT_EXECUTABLE = 'git';

    /**
     * Get the file server type identifier corresponding to the client's implementation.
     *
     * @return string|null If fallback class for all server types.
     */
    public static function getServerType()
    {
        return LocalGitRepoClient::getServerType();
    }

    /**
     * @param array|null $parameters
     */
    public function __construct(array $parameters = null)
    {
        parent::__construct($parameters);
        if (!$this->hasGitExecutable()) {
            $this->setGitExecutable('git');
        }
    }

    /**
     * Check if the Git executable path was set.
     *
     * @return boolean
     */
    public function hasGitExecutable()
    {
        return $this->hasParameter(self::GIT_EXECUTABLE);
    }

    /**
     * Get the Git executable path.
     *
     * @return string|null
     */
    public function getGitExecutable()
    {
        return $this->getParameter(self::GIT_EXECUTABLE);
    }

    /**
     * Set the Git executable path.
     *
     * @param string $value The new value.
     * @return $this
     */
    public function setGitExecutable($value)
    {
        return $this->setParameter(self::GIT_EXECUTABLE, $value);
    }

    /**
     * Get the required keys.
     *
     * @return array
     */
    protected static function getRequiredKeys()
    {
        $result = parent::getRequiredKeys();
        $result[] = static::GIT_EXECUTABLE;
        return $result;
    }
}