<?php

namespace ConfigToken\ConnectionSettings\Types;


use ConfigToken\ConnectionSettings\ConnectionSettingsInterface;
use ConfigToken\ConnectionSettings\Exception\ConnectionSettingsException;

class GenericConnectionSettings implements ConnectionSettingsInterface
{
    protected $parameters;

    /**
     * @param array|null $parameters
     */
    public function __construct(array $parameters = null)
    {
        if (isset($parameters)) {
            $this->setParameters($parameters);
        }
    }

    /**
     * Get the implementation class name.
     *
     * @return string
     */
    public static function getClassName()
    {
        return get_called_class();
    }

    /**
     * Get the file server type identifier corresponding to the client's implementation.
     *
     * @return string|null If fallback class for all server types.
     */
    public static function getServerType()
    {
        return null;
    }

    /**
     * Get the unique hash of the connection parameters.
     *
     * @return string
     */
    public function getHash()
    {
        return md5(serialize($this->getParameters()));
    }

    /**
     * Check if the parameter with the given key was set.
     *
     * @param string $key The parameter key.
     * @return boolean
     */
    public function hasParameter($key)
    {
        return isset($this->parameters) && isset($this->parameters[$key]);
    }

    /**
     * Get the parameter with the given key.
     *
     * @param string $key The parameter key.
     * @param mixed $default The default value to return if the parameter was not set.
     * @return mixed|null
     */
    public function getParameter($key, $default=null)
    {
        if (!$this->hasParameter($key)) {
            return $default;
        }
        return $this->parameters[$key];
    }

    /**
     * Set the parameter with the given key.
     *
     * @param string $key The parameter key.
     * @param mixed $value The new value.
     * @return $this
     */
    public function setParameter($key, $value)
    {
        if (!isset($this->parameters)) {
            $this->parameters = array();
        }
        $this->parameters[$key] = $value;
        return $this;
    }

    /**
     * Check if the parameters array was set.
     *
     * @return boolean
     */
    public function hasParameters()
    {
        return isset($this->parameters);
    }

    /**
     * Get the parameters array.
     *
     * @return array
     */
    public function getParameters()
    {
        if (!$this->hasParameters()) {
            return array();
        }
        return $this->parameters;
    }

    /**
     * Set the parameters array.
     *
     * @param array $value The new array of parameters.
     * @return $this
     */
    public function setParameters($value)
    {
        $this->parameters = $value;
        return $this;
    }

    /**
     * Get the required keys.
     *
     * @return array
     */
    protected static function getRequiredKeys()
    {
        return array();
    }

    /**
     * Validate the connection parameters and throw appropriate exceptions.
     *
     * @throws ConnectionSettingsException
     */
    public function validate()
    {
        $parameters = $this->hasParameters() ? $this->getParameters() : array();
        $missingRequiredKeys = array_diff(static::getRequiredKeys(), array_keys($parameters));
        if (!empty($missingRequiredKeys)) {
            throw new ConnectionSettingsException(
                $this,
                'Missing the following required key(s): ["%s"]',
                implode('", "', $missingRequiredKeys)
            );
        }
    }
}