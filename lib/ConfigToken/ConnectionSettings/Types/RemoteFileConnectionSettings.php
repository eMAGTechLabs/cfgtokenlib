<?php

namespace ConfigToken\ConnectionSettings\Types;


use ConfigToken\ConnectionSettings\Exception\ConnectionSettingsException;
use ConfigToken\FileClient\Types\RemoteFileClient;

class RemoteFileConnectionSettings extends GenericConnectionSettings
{
    const URL = 'url';
    const FIELD_NAME = 'field';
    const REQUEST_METHOD = 'method';

    const METHOD_GET = 'GET';
    const METHOD_POST = 'POST';

    /**
     * Get the file server type identifier corresponding to the client's implementation.
     *
     * @return string|null If fallback class for all server types.
     */
    public static function getServerType()
    {
        return RemoteFileClient::getServerType();
    }

    /**
     * @param array|null $parameters
     */
    public function __construct(array $parameters = null)
    {
        parent::__construct($parameters);
        if (!$this->hasFieldName()) {
            $this->setFieldName('fileName');
        }
        if (!$this->hasRequestMethod()) {
            $this->setRequestMethod(static::METHOD_GET);
        }
    }


    /**
     * Check if the GitLab URL was set.
     *
     * @return boolean
     */
    public function hasUrl()
    {
        return $this->hasParameter(self::URL);
    }

    /**
     * Get the GitLab URL.
     *
     * @return string|null
     */
    public function getUrl()
    {
        return $this->getParameter(self::URL);
    }

    /**
     * Set the GitLab URL.
     *
     * @param string $value The new value.
     * @return $this
     */
    public function setUrl($value)
    {
        return $this->setParameter(self::URL, $value);
    }

    /**
     * Check if the field name used in GET/POST to request the file was set.
     *
     * @return boolean
     */
    public function hasFieldName()
    {
        return $this->hasParameter(self::FIELD_NAME);
    }

    /**
     * Get the field name used in GET/POST to request the file.
     *
     * @return string|null
     */
    public function getFieldName()
    {
        return $this->getParameter(self::FIELD_NAME);
    }

    /**
     * Set the field name used in GET/POST to request the file.
     *
     * @param string $value The new value.
     * @return $this
     */
    public function setFieldName($value)
    {
        return $this->setParameter(self::FIELD_NAME, $value);
    }

    /**
     * Check if the request method was set.
     *
     * @return boolean
     */
    public function hasRequestMethod()
    {
        return $this->hasParameter(self::REQUEST_METHOD);
    }

    /**
     * Get the request method.
     *
     * @return string|null
     */
    public function getRequestMethod()
    {
        return $this->getParameter(self::REQUEST_METHOD);
    }

    /**
     * Set the request method.
     *
     * @param string $value The new value.
     * @return $this
     */
    public function setRequestMethod($value)
    {
        return $this->setParameter(self::REQUEST_METHOD, $value);
    }

    /**
     * Get the required keys.
     *
     * @return array
     */
    protected static function getRequiredKeys()
    {
        return array(static::URL, static::FIELD_NAME, static::REQUEST_METHOD);
    }

    /**
     * Validate the connection parameters and throw appropriate exceptions.
     *
     * @throws ConnectionSettingsException
     */
    public function validate()
    {
        parent::validate();
        $requestMethod = $this->getRequestMethod();
        if (($requestMethod != static::METHOD_GET) && ($requestMethod != static::METHOD_GET)) {
            throw new ConnectionSettingsException(
                $this,
                sprintf('Invalid request method "%s"', $requestMethod)
            );
        }
    }

}