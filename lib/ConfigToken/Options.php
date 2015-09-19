<?php

namespace ConfigToken;


use ConfigToken\Exception\OptionValueException;

/**
 * Option bag class.
 *
 * @package ConfigToken
 */
class Options
{
    /** @var array */
    protected $values;
    /** @var array */
    protected $defaults;

    /**
     * Initializes the options manager with the option values and defaults
     *
     * @param array|null $defaults Associative array of default option values.
     * @param array|null $values Associative array of option values.
     */
    public function __construct(array $defaults = null, array $values = null)
    {
        $this->defaults = $defaults;
        if (isset($values)) {
            $this->values = isset($defaults) ? array_merge($defaults, $values) : $values;
        } else {
            $this->values = isset($defaults) ? $defaults : array();
        }
    }

    /**
     * Check if the default values associative array was set.
     *
     * @return boolean
     */
    public function hasDefaults()
    {
        return isset($this->defaults);
    }

    /**
     * Get the default values associative array.
     *
     * @return array|null
     */
    public function getDefaults()
    {
        if (!$this->hasDefaults()) {
            return null;
        }
        return $this->defaults;
    }

    /**
     * Set the default values associative array.
     *
     * @param array $defaults The new value.
     * @param boolean $mergeWithValues If true, the new defaults will be merged with the existing values.
     * @return $this
     */
    public function setDefaults($defaults, $mergeWithValues = true)
    {
        if ($mergeWithValues) {
            $this->values = array_merge($defaults, $this->values);
        }
        $this->defaults = $defaults;
        return $this;
    }

    /**
     * Check if a default value was set for the given option key.
     *
     * @param string $key The option key.
     * @return boolean
     */
    public function hasDefault($key)
    {
        return $this->hasDefaults() && array_key_exists($key, $this->defaults);
    }

    /**
     * Get the default value for the given option key.
     *
     * @param string $key The option key.
     * @throws OptionValueException
     */
    public function getDefault($key)
    {
        if (!$this->hasDefault($key)) {
            throw new OptionValueException(
                sprintf(
                    'No default value was set for option "%s"',
                    $key
                )
            );
        }
        return $this->defaults[$key];
    }

    /**
     * Sets the default value for the given option key.
     *
     * @param string $key The option key.
     * @param mixed $value
     * @return $this
     */
    public function setDefault($key, $value)
    {
        if (!$this->hasDefaults()) {
            $this->defaults = array();
        }
        $this->defaults[$key] = $value;
        return $this;
    }

    /**
     * Removes the default value given option key.
     *
     * @param string $key The option key.
     * @throws OptionValueException
     * @return $this
     */
    public function unsetDefault($key)
    {
        if (!$this->hasDefault($key)) {
            throw new OptionValueException(
                sprintf(
                    'No default value was set for option "%s". Unable to unset.',
                    $key
                )
            );
        }
        unset($this->defaults[$key]);
    }

    /**
     * Get the options array.
     *
     * @return array|null Associative array of option values.
     */
    public function getValues()
    {
        return $this->values;
    }

    /**
     * Set the options array.
     *
     * @param array $value Associative array of option values.
     * @return $this
     */
    public function setValues($value)
    {
        $this->values = $value;
        return $this;
    }

    /**
     * Check if the value for the option with the given key was set.
     *
     * @param string $key The option key.
     * @return boolean
     */
    public function hasValue($key)
    {
        return isset($this->values[$key]);
    }

    /**
     * Get the value for the option with the given key.
     *
     * @param string $key The option key.
     * @param boolean $fallbackToDefault If true and the option value was not set, return the default value.
     * @throws OptionValueException
     * @return mixed|null
     */
    public function getValue($key, $fallbackToDefault = true)
    {
        if (!$this->hasValue($key)) {
            if ($fallbackToDefault && $this->hasDefault($key)) {
                return $this->getDefault($key);
            }
            throw new OptionValueException(sprintf('Option value not set for "%s".', $key));
        }
        return $this->values[$key];
    }

    /**
     * Set the value for the option with the given key.
     *
     * @param string $key The option key.
     * @param mixed $value The new value.
     * @return $this
     */
    public function setValue($key, $value)
    {
        $this->values[$key] = $value;
        return $this;
    }

    /**
     * Sets the value for the given option key to default or removes it.
     *
     * @param string $key The option key.
     * @throws OptionValueException
     */
    public function unsetValue($key)
    {
        if (!$this->hasValue($key)) {
            throw new OptionValueException(
                sprintf(
                    'No value was set for option "%s". Unable to unset.',
                    $key
                )
            );
        }
        if ($this->hasDefault($key)) {
            $this->setValue($key, $this->getDefault($key));
        } else {
            unset($this->values[$key]);
        }
    }

    /**
     * Get the missing required values.
     *
     * @param array $required Array of required option keys.
     * @param bool|true $nullIsValid If true, null values are considered valid.
     * @return array
     */
    public function getMissingValues(array $required, $nullIsValid=true)
    {
        if ($nullIsValid) {
            $values = array_keys($this->values);
            $result = array_diff($required, $values);
            return $result;
        }
        $result = array();
        foreach ($required as $requiredKey) {
            if (!isset($this->values[$requiredKey])) {
                $result[] = $requiredKey;
            }
        }
        return $result;
    }
}