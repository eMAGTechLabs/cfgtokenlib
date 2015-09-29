<?php

namespace ConfigToken\Options;


use ConfigToken\Options\Exceptions\OptionValueException;
use ConfigToken\Options\Exceptions\UnknownOptionException;
use ConfigToken\Options\Exceptions\UnknownOptionValueException;

class Options implements OptionsInterface
{
    /** @var boolean[] */
    protected $keys = array();
    protected $defaultValues = array();
    protected $values = array();

    /**
     * Get an associative array with all registered option keys.
     * A "true" value specifies if the option is required.
     *
     * @return boolean[]
     */
    public function getKeys()
    {
        return $this->keys;
    }

    /**
     * Get an array of either optional or required option keys with their corresponding values or default values.
     *
     * @param boolean|true $getRequired If true, required options will be returned otherwise the optional ones.
     * @return array
     */
    protected function getRequiredOrOptionalKeys($getRequired=true)
    {
        $result = array();
        foreach ($this->keys as $key => $required) {
            if ($required == $getRequired) {
                if ($this->hasValue($key)) {
                    $result[$key] = $this->getValue($key);
                } else if ($this->hasDefaultValue($key)) {
                    $result[$key] = $this->getDefaultValue($key);
                } else {
                    $result[$key] = null;
                }
            }
        }
        return $result;
    }

    /**
     * Get the keys that are not registered from the given array.
     *
     * @param string[] $keys
     * @return string[]
     */
    protected function getUnknownKeys($keys)
    {
        return array_values(array_diff($keys, array_keys($this->keys)));
    }

    /**
     * Get an array of optional keys with their corresponding values or default values.
     *
     * @return array
     */
    public function getOptionalKeys()
    {
        return $this->getRequiredOrOptionalKeys(false);
    }

    /**
     * Overwrite the registered optional keys with the ones from the given array of keys and default values.
     *
     * @param array $keyDefaultValues Associative array of default values for optional keys.
     * @return $this
     * @throws OptionValueException
     * @throws UnknownOptionException
     * @throws UnknownOptionValueException
     */
    protected function setOptionalKeys(array $keyDefaultValues)
    {
        foreach ($this->keys as $key => $required) {
            if (!$required) {
                unset($this->keys[$key]);
            }
        }
        foreach ($keyDefaultValues as $key => $defaultValue) {
            $this->setOptionalKey($key, $defaultValue);
        }
        return $this;
    }

    /**
     * Register the given key as optional along with a default value.
     *
     * @param string $key The name of the new optional key.
     * @param mixed|null $defaultValue If null, default value will not be set.
     * @return $this
     * @throws OptionValueException
     * @throws UnknownOptionException
     * @throws UnknownOptionValueException
     */
    protected function setOptionalKey($key, $defaultValue = null)
    {
        $hasValue = false;
        if ($this->hasKey($key) && $this->isKeyRequired($key)) {
            $hasValue = $this->hasValue($key);
            if ($hasValue) {
                $value = $this->getValue($key, false);
            }
            $this->unsetKey($key);
        }
        $this->keys[$key] = false;
        if (isset($defaultValue)) {
            $this->setDefaultValue($key, $defaultValue);
        }
        if ($hasValue) {
            /** @noinspection PhpUndefinedVariableInspection */
            $this->setValue($key, $value);
        }
        return $this;
    }

    /**
     * Get a list of all required keys that do not have values set.
     *
     * @param boolean|false $useDefaultsIfMissing If true, keys having default values registered will not be returned.
     * @return string[]
     */
    public function getRequiredKeysWithoutValues($useDefaultsIfMissing=false)
    {
        $requiredKeys = $this->getRequiredKeys();
        $result = array_diff(array_keys($requiredKeys), array_keys($this->values));
        if ($useDefaultsIfMissing) {
            $result = array_diff($result, array_keys($this->defaultValues));
        }
        return array_values($result);
    }

    /**
     * Get an array of required option keys with their corresponding values or default values.
     *
     * @return array
     */
    public function getRequiredKeys()
    {
        return $this->getRequiredOrOptionalKeys(true);
    }

    /**
     * Overwrite the registered required keys with the ones from the given array of keys and default values.
     *
     * @param array $keyDefaultValues Associative array of default values for required keys.
     * @return $this
     * @throws OptionValueException
     * @throws UnknownOptionException
     * @throws UnknownOptionValueException
     */
    protected function setRequiredKeys(array $keyDefaultValues)
    {
        foreach ($this->keys as $key => $required) {
            if ($required) {
                unset($this->keys[$key]);
            }
        }
        foreach ($keyDefaultValues as $key => $defaultValue) {
            $this->setRequiredKey($key, $defaultValue);
        }
        return $this;
    }

    /**
     * Register the given key as required along with a default value.
     *
     * @param string $key The name of the new required key.
     * @param mixed|null $defaultValue If null, default value will not be set.
     * @return $this
     * @throws OptionValueException
     * @throws UnknownOptionException
     * @throws UnknownOptionValueException
     */
    protected function setRequiredKey($key, $defaultValue = null)
    {
        $hasValue = false;
        if ($this->hasKey($key) && $this->isKeyOptional($key)) {
            $hasValue = $this->hasValue($key);
            if ($hasValue) {
                $value = $this->getValue($key, false);
            }
            $this->unsetKey($key);
        }
        $this->keys[$key] = true;
        if (isset($defaultValue)) {
            $this->setDefaultValue($key, $defaultValue);
        }
        if ($hasValue) {
            /** @noinspection PhpUndefinedVariableInspection */
            $this->setValue($key, $value);
        }
        return $this;
    }

    /**
     * Check if the given option key is registered.
     *
     * @param string $key
     * @return boolean
     */
    public function hasKey($key)
    {
        return array_key_exists($key, $this->keys);
    }

    /**
     * Un-register the given option key. Also unset it's value and default value.
     *
     * @param string $key
     * @return $this
     * @throws UnknownOptionException
     * @throws UnknownOptionValueException
     */
    protected function unsetKey($key)
    {
        if (!$this->hasKey($key)) {
            throw new UnknownOptionException($key);
        }
        if ($this->hasValue($key)) {
            $this->unsetValue($key);
        }
        if ($this->hasDefaultValue($key)) {
            $this->unsetDefaultValue($key);
        }
        return $this;
    }

    /**
     * Check if the given option key is required.
     *
     * @param string $key
     * @return boolean
     * @throws UnknownOptionException
     */
    public function isKeyRequired($key)
    {
        if (!$this->hasKey($key)) {
            throw new UnknownOptionException($key);
        }
        return $this->keys[$key];
    }

    /**
     * Check if the given option key is optional.
     *
     * @param string $key
     * @return boolean
     * @throws UnknownOptionException
     */
    public function isKeyOptional($key)
    {
        if (!$this->hasKey($key)) {
            throw new UnknownOptionException($key);
        }
        return !$this->keys[$key];
    }

    /**
     * Get an associative array of option keys and default values.
     *
     * @return array
     */
    public function getDefaultValues()
    {
        return $this->defaultValues;
    }

    /**
     * Unset the default value for the given option key.
     *
     * @param string $key
     * @return $this
     * @throws UnknownOptionException
     * @throws UnknownOptionValueException
     */
    protected function unsetDefaultValue($key)
    {
        if (!$this->hasKey($key)) {
            throw new UnknownOptionException($key);
        }
        if (!$this->hasDefaultValue($key)) {
            throw new UnknownOptionValueException($key, 'default value not set.');
        }
        unset($this->defaultValues[$key]);
        return $this;
    }

    /**
     * Overwrite the associative array of default values for registered option keys.
     *
     * @param array $defaultValues
     * @return $this
     * @throws OptionValueException
     * @throws UnknownOptionException
     */
    protected function setDefaultValues(array $defaultValues)
    {
        $unknownKeys = $this->getUnknownKeys(array_keys($defaultValues));
        if (!empty($unknownKeys)) {
            throw new UnknownOptionException(implode('", "', $unknownKeys));
        }
        $this->defaultValues = array();
        foreach ($defaultValues as $key => $defaultValue) {
            $this->setDefaultValue($key, $defaultValue);
        }
        return $this;
    }

    /**
     * Check if the given option key has a corresponding default value set.
     *
     * @param string $key
     * @return boolean
     * @throws UnknownOptionException
     */
    public function hasDefaultValue($key)
    {
        if (!$this->hasKey($key)) {
            throw new UnknownOptionException($key);
        }
        return array_key_exists($key, $this->defaultValues);
    }

    /**
     * Get the default value for the given option key.
     *
     * @param string $key
     * @return mixed
     * @throws UnknownOptionException
     * @throws UnknownOptionValueException
     */
    public function getDefaultValue($key)
    {
        if (!$this->hasKey($key)) {
            throw new UnknownOptionException($key);
        }
        if (!$this->hasDefaultValue($key)) {
            throw new UnknownOptionValueException($key, 'default value not set.');
        }
        return $this->defaultValues[$key];
    }

    /**
     * Set the default value for the given registered option key.
     *
     * @param string $key
     * @param mixed $defaultValue
     * @return $this
     * @throws OptionValueException
     * @throws UnknownOptionException
     */
    protected function setDefaultValue($key, $defaultValue)
    {
        if (!$this->hasKey($key)) {
            throw new UnknownOptionException($key);
        }
        $isValid = $this->isValidValue($key, $defaultValue);
        if ($isValid !== true) {
            throw new OptionValueException($key, $isValid === false ? '' : $isValid);
        }
        $this->defaultValues[$key] = $defaultValue;
        return $this;
    }

    /**
     * Get an associative array of option keys and their corresponding values if set.
     *
     * @param boolean|false $getDefaultsForMissingValues If true, default values will be used where values are not set.
     * @return array
     */
    public function getValues($getDefaultsForMissingValues = false)
    {
        if (!$getDefaultsForMissingValues) {
            return $this->values;
        }
        $result = $this->values;
        foreach ($this->defaultValues as $key => $defaultValue) {
            if (!array_key_exists($key, $result)) {
                $result[$key] = $defaultValue;
            }
        }
        return $result;
    }

    /**
     * Overwrite the values set for the registered option keys.
     *
     * @param array $values
     * @throws OptionValueException
     * @throws UnknownOptionException
     */
    public function setValues(array $values)
    {
        $unknownKeys = $this->getUnknownKeys(array_keys($values));
        if (!empty($unknownKeys)) {
            throw new UnknownOptionException(implode('", "', $unknownKeys));
        }
        $this->values = array();
        foreach ($values as $key => $value) {
            $this->setValue($key, $value);
        }
    }

    /**
     * Check if a value was set for the given registered option key.
     *
     * @param $key
     * @return bool
     * @throws UnknownOptionException
     */
    public function hasValue($key)
    {
        if (!$this->hasKey($key)) {
            throw new UnknownOptionException($key);
        }
        return array_key_exists($key, $this->values);
    }

    /**
     * Get the value for the given registered option key.
     *
     * @param string $key
     * @param boolean|false $getDefaultIfMissing If true, the corresponding default value will be returned if set.
     * @return mixed
     * @throws UnknownOptionException
     * @throws UnknownOptionValueException
     */
    public function getValue($key, $getDefaultIfMissing = false)
    {
        if (!$this->hasKey($key)) {
            throw new UnknownOptionException($key);
        }
        if ($this->hasValue($key)) {
            return $this->values[$key];
        }
        if ($getDefaultIfMissing) {
            if (!$this->hasDefaultValue($key)) {
                throw new UnknownOptionValueException($key, 'value and default value not set.');
            }
            return $this->getDefaultValue($key);
        }
        throw new UnknownOptionValueException($key, 'value not set.');
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
        if (!$this->hasKey($key)) {
            throw new UnknownOptionException($key);
        }
        return true;
    }

    /**
     * Set the value for the given registered option key.
     *
     * @param string $key
     * @param mixed $value
     * @return $this
     * @throws OptionValueException
     * @throws UnknownOptionException
     */
    public function setValue($key, $value)
    {
        $isValid = $this->isValidValue($key, $value);
        if ($isValid !== true) {
            throw new OptionValueException($key, $isValid === false ? '' : $isValid);
        }
        $this->values[$key] = $value;
        return $this;
    }

    /**
     * Unset the value for the given registered option key.
     *
     * @param string $key
     * @throws UnknownOptionException
     * @throws UnknownOptionValueException
     */
    public function unsetValue($key)
    {
        if (!$this->hasKey($key)) {
            throw new UnknownOptionException($key);
        }
        if (!$this->hasValue($key)) {
            throw new UnknownOptionValueException($key, 'value not set.');
        }
        unset($this->values[$key]);
    }

    /**
     * Validate the values.
     *
     * @param boolean|false $useDefaultsIfMissing If true, corresponding default values will be used where values not set.
     * @return $this
     * @throws UnknownOptionValueException
     */
    public function validate($useDefaultsIfMissing=false)
    {
        $missingKeys = $this->getRequiredKeysWithoutValues($useDefaultsIfMissing);
        if (!empty($missingKeys)) {
            throw new UnknownOptionValueException(implode('", "', $missingKeys), 'required to have values.');
        }
        return $this;
    }

    /**
     * Check if the values are valid.
     *
     * @param boolean|false $useDefaultsIfMissing If true, corresponding default values will be used where values not set.
     * @return bool
     */
    public function isValid($useDefaultsIfMissing=false)
    {
        try {
            $this->validate($useDefaultsIfMissing);
            return true;
        } catch (UnknownOptionValueException $e) {
            return false;
        }
    }
}