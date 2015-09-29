<?php

namespace ConfigToken\Tests\Options\Mocks;


use ConfigToken\Options\Options;

class TestOptionsClass extends Options
{
    public function getRequiredOrOptionalKeys($getRequired = true)
    {
        return parent::getRequiredOrOptionalKeys($getRequired);
    }

    public function getUnknownKeys($keys)
    {
        return parent::getUnknownKeys($keys);
    }

    public function setOptionalKeys(array $keyDefaultValues)
    {
        return parent::setOptionalKeys($keyDefaultValues);
    }

    public function setOptionalKey($key, $defaultValue = null)
    {
        return parent::setOptionalKey($key, $defaultValue);
    }

    public function setRequiredKeys(array $keyDefaultValues)
    {
        return parent::setRequiredKeys($keyDefaultValues);
    }

    public function setRequiredKey($key, $defaultValue = null)
    {
        return parent::setRequiredKey($key, $defaultValue);
    }

    public function unsetKey($key)
    {
        return parent::unsetKey($key);
    }

    public function unsetDefaultValue($key)
    {
        return parent::unsetDefaultValue($key);
    }

    public function setDefaultValues(array $defaultValues)
    {
        return parent::setDefaultValues($defaultValues);
    }

    public function setDefaultValue($key, $defaultValue)
    {
        return parent::setDefaultValue($key, $defaultValue);
    }

    public function isValidValue($key, $value)
    {
        if ($key == 'invalid') {
            return false;
        }
        return parent::isValidValue($key, $value);
    }

}