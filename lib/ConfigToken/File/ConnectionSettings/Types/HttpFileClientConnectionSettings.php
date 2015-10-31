<?php

namespace ConfigToken\File\ConnectionSettings\Types;


use ConfigToken\Options\Exceptions\UnknownOptionException;
use ConfigToken\Options\Exceptions\UnknownOptionValueException;

class HttpFileClientConnectionSettings extends LocalFileClientConnectionSettings
{
    const URL = 'url';
    const FIELD_NAME = 'field';
    const REQUEST_METHOD = 'method';
    const AUTH_TYPE = 'auth';
    const USER = 'user';
    const PASSWORD = 'password';

    const DEFAULT_FIELD_NAME = 'file';

    const METHOD_GET = 'GET';
    const METHOD_POST = 'POST';

    const AUTH_NONE = 'none';
    const AUTH_BASIC = 'basic';

    protected static $KNOWN_VALUES = array(
        self::REQUEST_METHOD => array(
            self::METHOD_GET,
            self::METHOD_POST,
        ),
        self::AUTH_TYPE => array(
            self::AUTH_NONE,
            self::AUTH_BASIC,
        ),
    );

    /**
     * Initialize the required and optional settings with default values.
     */
    protected function initialize()
    {
        parent::initialize();
        $this->setRequiredKey(static::URL);
        $this->setRequiredKey(static::REQUEST_METHOD, static::METHOD_POST);
        $this->setRequiredKey(static::FIELD_NAME, static::DEFAULT_FIELD_NAME);
        $this->setRequiredKey(static::AUTH_TYPE, static::AUTH_NONE);
        $this->setOptionalKey(static::USER);
        $this->setOptionalKey(static::PASSWORD);
    }

    /**
     * Check if the given value is valid for the registered option key.
     * Override to implement validation.
     *
     * @param string $key
     * @param mixed $value
     * @return boolean
     * @throws UnknownOptionException
     */
    protected function isValidValue($key, $value)
    {
        switch ($key) {
            case static::REQUEST_METHOD:
            case static::AUTH_TYPE:
                return in_array($value, static::$KNOWN_VALUES[$key]);
            default:
                return parent::isValidValue($key, $value);
        }
    }

    /**
     * Get the URL.
     *
     * @throws UnknownOptionValueException
     * @return string
     */
    public function getUrl()
    {
        return $this->getValue(self::URL, true);
    }

    /**
     * Set the URL.
     *
     * @param string $value The new value.
     * @return $this
     */
    public function setUrl($value)
    {
        return $this->setValue(self::URL, $value);
    }

    /**
     * Get the field name.
     *
     * @return string
     */
    public function getFieldName()
    {
        return $this->getValue(self::FIELD_NAME, true);
    }

    /**
     * Set the field name.
     *
     * @param string $value The new value.
     * @return $this
     */
    public function setFieldName($value)
    {
        return $this->setValue(self::FIELD_NAME, $value);
    }

    /**
     * Get the request method.
     *
     * @return string
     */
    public function getRequestMethod()
    {
        return $this->getValue(self::REQUEST_METHOD, true);
    }

    /**
     * Set the request method.
     *
     * @param string $value The new value.
     * @return $this
     */
    public function setRequestMethod($value)
    {
        return $this->setValue(self::REQUEST_METHOD, $value);
    }

    /**
     * Get the authentication type.
     *
     * @return string
     */
    public function getAuthType()
    {
        return $this->getValue(self::AUTH_TYPE, true);
    }

    /**
     * Set the authentication type.
     *
     * @param string $value The new value.
     * @return $this
     */
    public function setAuthType($value)
    {
        return $this->setValue(self::AUTH_TYPE, $value);
    }

    /**
     * Check if the username was set.
     *
     * @return boolean
     */
    public function hasUser()
    {
        return $this->hasValue(self::USER);
    }

    /**
     * Get the username.
     *
     * @return string|null
     */
    public function getUser()
    {
        return $this->getValue(self::USER);
    }

    /**
     * Set the username.
     *
     * @param string $value The new value.
     * @return $this
     */
    public function setUser($value)
    {
        return $this->setValue(self::USER, $value);
    }

    /**
     * Check if the password was set.
     *
     * @return boolean
     */
    public function hasPassword()
    {
        return $this->hasValue(self::PASSWORD);
    }

    /**
     * Get the password.
     *
     * @return string|null
     */
    public function getPassword()
    {
        return $this->getValue(self::PASSWORD);
    }

    /**
     * Set the password.
     *
     * @param string $value The new value.
     * @return $this
     */
    public function setPassword($value)
    {
        return $this->setValue(self::PASSWORD, $value);
    }
}