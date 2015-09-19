<?php

namespace ConfigToken\ConnectionSettings\Types;


abstract class GitRepoConnectionSettings extends GenericConnectionSettings
{
    const HOST_NAME = 'host';
    const GROUP_NAME = 'group';
    const REPO_NAME = 'repo';
    const NAMED_REFERENCE = 'version';

    /**
     * @param array|null $parameters
     */
    public function __construct(array $parameters = null)
    {
        parent::__construct($parameters);
        if (!$this->hasNamedReference()) {
            $this->setNamedReference('master');
        }
    }

    /**
     * Get the file server type identifier corresponding to the client's implementation.
     *
     * @return string|null If fallback class for all server types.
     */
    public static function getServerType()
    {
        return 'git';
    }

    /**
     * Check if the host name of the Git repository was set.
     *
     * @return boolean
     */
    public function hasHostName()
    {
        return $this->hasParameter(self::HOST_NAME);
    }

    /**
     * Get the host name of the Git repository.
     *
     * @return string|null
     */
    public function getHostName()
    {
        return $this->getParameter(self::HOST_NAME);
    }

    /**
     * Set the host name of the Git repository.
     *
     * @param string $value The new value.
     * @return $this
     */
    public function setHostName($value)
    {
        return $this->setParameter(self::HOST_NAME, $value);
    }

    /**
     * Check if the group name was set.
     *
     * @return boolean
     */
    public function hasGroupName()
    {
        return $this->hasParameter(self::GROUP_NAME);
    }

    /**
     * Get the group name.
     *
     * @return string|null
     */
    public function getGroupName()
    {
        return $this->getParameter(self::GROUP_NAME);
    }

    /**
     * Set the group name.
     *
     * @param string $value The new value.
     * @return $this
     */
    public function setGroupName($value)
    {
        return $this->setParameter(self::GROUP_NAME, $value);
    }

    /**
     * Check if the repo name was set.
     *
     * @return boolean
     */
    public function hasRepoName()
    {
        return $this->hasParameter(self::REPO_NAME);
    }

    /**
     * Get the repo name.
     *
     * @return string|null
     */
    public function getRepoName()
    {
        return $this->getParameter(self::REPO_NAME);
    }

    /**
     * Set the repo name.
     *
     * @param string $value The new value.
     * @return $this
     */
    public function setRepoName($value)
    {
        return $this->setParameter(self::REPO_NAME, $value);
    }

    /**
     * Check if the named reference (tag or branch) was set.
     *
     * @return boolean
     */
    public function hasNamedReference()
    {
        return $this->hasParameter(self::NAMED_REFERENCE);
    }

    /**
     * Get the named reference (tag or branch).
     *
     * @return string|null
     */
    public function getNamedReference()
    {
        return $this->getParameter(self::NAMED_REFERENCE);
    }

    /**
     * Set the named reference (tag or branch).
     *
     * @param string $value The new value.
     * @return $this
     */
    public function setNamedReference($value)
    {
        return $this->setParameter(self::NAMED_REFERENCE, $value);
    }

    /**
     * Get the required keys.
     *
     * @return array
     */
    protected static function getRequiredKeys()
    {
        return array(static::HOST_NAME, static::GROUP_NAME, static::REPO_NAME, static::NAMED_REFERENCE);
    }
}