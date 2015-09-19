<?php

namespace ConfigToken\ConnectionSettings;


use ConfigToken\ConnectionSettings\Exception\ConnectionSettingsException;

interface ConnectionSettingsInterface
{
    /**
     * Get the file server type identifier corresponding to the client's implementation.
     *
     * @return string|null If fallback class for all server types.
     */
    public static function getServerType();

    /**
     * Get the implementation class name.
     *
     * @return string
     */
    public static function getClassName();

    /**
     * Get the unique hash of the connection parameters.
     *
     * @return string
     */
    public function getHash();

    /**
     * Check if the parameter with the given key was set.
     *
     * @param string $key The parameter key.
     * @return boolean
     */
    public function hasParameter($key);

    /**
     * Get the parameter with the given key.
     *
     * @param string $key The parameter key.
     * @param mixed $default The default value to return if the parameter was not set.
     * @return mixed|null
     */
    public function getParameter($key, $default = null);

    /**
     * Set the parameter with the given key.
     *
     * @param string $key The parameter key.
     * @param mixed $value The new value.
     * @return $this
     */
    public function setParameter($key, $value);

    /**
     * Check if the parameters array was set.
     *
     * @return boolean
     */
    public function hasParameters();

    /**
     * Get the parameters array.
     *
     * @return array
     */
    public function getParameters();

    /**
     * Set the parameters array.
     *
     * @param array $value The new value.
     * @return $this
     */
    public function setParameters($value);

    /**
     * Validate the connection parameters and throw appropriate exceptions.
     *
     * @throws ConnectionSettingsException
     */
    public function validate();
}