<?php

namespace ConfigToken\Utils;


use ConfigToken\Utils\Exceptions\UnknownCredentialException;

interface CredentialsManagerInterface
{
    /**
     * Check if the credential with the given key has been set for the given resource hash.
     *
     * @param string $resourceHash
     * @param string $key
     * @return boolean
     */
    public function hasCredential($resourceHash, $key);

    /**
     * Get the credential for the given key and resource hash.
     *
     * @param string $resourceHash
     * @param string $key
     * @throws UnknownCredentialException
     * @return string
     */
    public function getCredential($resourceHash, $key);
}