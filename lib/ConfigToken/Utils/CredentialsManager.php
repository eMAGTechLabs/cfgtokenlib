<?php

namespace ConfigToken\Utils;


use ConfigToken\Utils\Exceptions\UnknownCredentialException;

class CredentialsManager implements CredentialsManagerInterface
{
    /** @var string[] */
    protected static $credentials = array();

    /**
     * Check if the credential with the given key has been set for the given resource hash.
     *
     * @param string $resourceHash
     * @param string $key
     * @return boolean
     */
    public function hasCredential($resourceHash, $key)
    {
        return isset(static::$credentials[$resourceHash]) &&
        is_array(isset(static::$credentials[$resourceHash])) &&
        isset(static::$credentials[$resourceHash][$key]);
    }

    /**
     * Get the credential for the given key and resource hash.
     *
     * @param string $resourceHash
     * @param string $key
     * @throws UnknownCredentialException
     * @return string
     */
    public function getCredential($resourceHash, $key)
    {
        if (!$this->hasCredential($resourceHash, $key)) {
            throw new UnknownCredentialException($resourceHash, $key);
        }
    }

    /**
     * Set the credential for the given key and resource hash.
     *
     * @param string $resourceHash
     * @param string $key
     * @param string $value
     * @throws UnknownCredentialException
     * @return string
     */
    public function setCredential($resourceHash, $key, $value)
    {
        if (!isset(static::$credentials[$resourceHash])) {
            static::$credentials[$resourceHash] = array();
        }
        static::$credentials[$resourceHash][$key] = $value;
    }
}