<?php

namespace ConfigToken\ConnectionSettings\Types;


use ConfigToken\FileClient\Types\LocalFileClient;

class LocalFileConnectionSettings extends GenericConnectionSettings
{
    const ROOT_PATH = 'root';

    /**
     * Get the file server type identifier corresponding to the client's implementation.
     *
     * @return string|null If fallback class for all server types.
     */
    public static function getServerType()
    {
        return LocalFileClient::getServerType();
    }

    /**
     * @param array|null $parameters
     */
    public function __construct(array $parameters = null)
    {
        parent::__construct($parameters);
        if (!$this->hasRootPath()) {
            $this->setRootPath('.');
        }
    }

    /**
     * Check if the root path was set.
     *
     * @return boolean
     */
    public function hasRootPath()
    {
        return $this->hasParameter(self::ROOT_PATH);
    }

    /**
     * Get the root path.
     *
     * @return string|null
     */
    public function getRootPath()
    {
        return $this->getParameter(self::ROOT_PATH);
    }

    /**
     * Set the root path.
     *
     * @param string $value The new value.
     * @return $this
     */
    public function setRootPath($value)
    {
        return $this->setParameter(self::ROOT_PATH, $value);
    }

    /**
     * Get the required keys.
     *
     * @return array
     */
    protected static function getRequiredKeys()
    {
        return array(static::ROOT_PATH);
    }
}