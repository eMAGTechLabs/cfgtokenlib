<?php

namespace ConfigToken\Options;


use ConfigToken\Options\Exceptions\UnknownOptionException;
use ConfigToken\Options\Exceptions\UnknownOptionValueException;

interface OptionsInterface
{
    /**
     * Get an associative array with all registered option keys.
     * A "true" value specifies if the option is required.
     *
     * @return boolean[]
     */
    public function getKeys();

    /**
     * Get a list of optional keys and their corresponding values or default values.
     *
     * @return array
     */
    public function getOptionalKeys();

    /**
     * Get a list of all required keys that do not have values set.
     *
     * @param boolean|false $useDefaultsIfMissing If true, keys having default values registered will not be returned.
     * @return string[]
     */
    public function getRequiredKeysWithoutValues($useDefaultsIfMissing=false);

    /**
     * Get an array of required option keys with their corresponding values or default values.
     *
     * @return array
     */
    public function getRequiredKeys();

    /**
     * Check if the given option key is registered.
     *
     * @param string $key
     * @return boolean
     */
    public function hasKey($key);

    /**
     * Check if the given option key is required.
     *
     * @param string $key
     * @return boolean
     * @throws UnknownOptionException
     */
    public function isKeyRequired($key);

    /**
     * Check if the given option key is optional.
     *
     * @param string $key
     * @return boolean
     * @throws UnknownOptionException
     */
    public function isKeyOptional($key);

    /**
     * Get an associative array of option keys and default values.
     *
     * @return array
     */
    public function getDefaultValues();

    /**
     * Check if the given option key has a corresponding default value set.
     *
     * @param string $key
     * @return boolean
     * @throws UnknownOptionException
     */
    public function hasDefaultValue($key);

    /**
     * Get the default value for the given option key.
     *
     * @param string $key
     * @return mixed
     * @throws UnknownOptionException
     * @throws UnknownOptionValueException
     */
    public function getDefaultValue($key);

    /**
     * Get an associative array of option keys and their corresponding values if set.
     *
     * @param boolean|false $getDefaultsForMissingValues If true, default values will be used where values are not set.
     * @return array
     */
    public function getValues($getDefaultsForMissingValues=false);

    /**
     * Check if a value was set for the given registered option key.
     *
     * @param $key
     * @return bool
     * @throws UnknownOptionException
     */
    public function hasValue($key);

    /**
     * Get the value for the given registered option key.
     *
     * @param string $key
     * @param boolean|false $getDefaultIfMissing If true, the corresponding default value will be returned if set.
     * @return mixed
     * @throws UnknownOptionException
     * @throws UnknownOptionValueException
     */
    public function getValue($key, $getDefaultIfMissing=false);

    /**
     * Validate the values.
     *
     * @param boolean|false $useDefaultsIfMissing If true, corresponding default values will be used where values not set.
     * @return $this
     * @throws UnknownOptionValueException
     */
    public function validate($useDefaultsIfMissing=false);

    /**
     * Check if the values are valid.
     *
     * @param boolean|false $useDefaultsIfMissing If true, corresponding default values will be used where values not set.
     * @return bool
     */
    public function isValid($useDefaultsIfMissing=false);
}