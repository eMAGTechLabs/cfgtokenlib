<?php

namespace ConfigToken\File\ConnectionSettings\Types;


use ConfigToken\File\ConnectionSettings\GenericConnectionSettings;
use ConfigToken\Options\Exceptions\OptionValueException;
use ConfigToken\Options\Exceptions\UnknownOptionException;
use ConfigToken\Options\Exceptions\UnknownOptionValueException;
use ConfigToken\Utils\FileUtils;

class LocalFileClientConnectionSettings extends GenericConnectionSettings
{
    const ROOT_PATH = 'root';
    const DIRECTORY_SEPARATOR = 'dir-sep';

    /**
     * Initialize the required and optional settings with default values.
     */
    protected function initialize()
    {
        $this->setRequiredKey(static::ROOT_PATH);
        $this->setOptionalKey(static::DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR);
        $this->setRootPath(getcwd());
    }

    /**
     * Check if the root path was set.
     *
     * @return boolean
     */
    public function hasRootPath()
    {
        return $this->hasValue(static::ROOT_PATH);
    }

    /**
     * Get the root path.
     *
     * @param string|null $directorySeparator
     * @throws UnknownOptionValueException
     * @return string
     */
    public function getRootPath($directorySeparator=null)
    {
        $rootPath = $this->getValue(static::ROOT_PATH, true);
        if (!isset($directorySeparator)) {
            $directorySeparator = $this->getDirectorySeparator();
        }
        $rootPath = FileUtils::replaceDirectorySeparator($rootPath, $directorySeparator);
        return $rootPath;
    }

    /**
     * Set the root path.
     *
     * @param string $value The new value.
     * @throws OptionValueException
     * @return $this
     */
    public function setRootPath($value)
    {
        $value = FileUtils::normalizePath($value);
        $value = FileUtils::replaceDirectorySeparator($value, $this->getDirectorySeparator());
        return $this->setValue(static::ROOT_PATH, $value);
    }

    /**
     * Get the directory separator.
     *
     * @return string
     */
    public function getDirectorySeparator()
    {
        return $this->getValue(static::DIRECTORY_SEPARATOR, true);
    }

    /**
     * Set the directory separator.
     *
     * @param string $value The new value.
     * @return $this
     */
    public function setDirectorySeparator($value)
    {
        return $this->setValue(static::DIRECTORY_SEPARATOR, $value);
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
            case static::DIRECTORY_SEPARATOR:
                return in_array($value, FileUtils::$DIRECTORY_SEPARATORS);
            default:
                return parent::isValidValue($key, $value);
        }
    }
}