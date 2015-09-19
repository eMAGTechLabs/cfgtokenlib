<?php

namespace ConfigToken\FileClient\Types;


use ConfigToken\ConnectionSettings\ConnectionSettingsInterface;
use ConfigToken\ConnectionSettings\Exception\ConnectionSettingsException;
use ConfigToken\ConnectionSettings\Types\GenericConnectionSettings;
use ConfigToken\FileClient\FileClientInterface;

abstract class AbstractFileClient implements FileClientInterface
{
    /** @var ConnectionSettingsInterface */
    protected $connectionSettings;

    /**
     * @param ConnectionSettingsInterface|null $connectionSettings
     */
    public function __construct(ConnectionSettingsInterface $connectionSettings = null)
    {
        if (!isset($connectionSettings)) {
            $connectionSettings = new GenericConnectionSettings();
        }
        $this->setConnectionSettings($connectionSettings);
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
     * Set the connection settings.
     *
     * @param ConnectionSettingsInterface $connectionSettings
     * @return $this
     */
    public function setConnectionSettings(ConnectionSettingsInterface $connectionSettings)
    {
        $this->connectionSettings = $connectionSettings;
        return $this;
    }

    /**
     * Get the connection settings.
     *
     * @return ConnectionSettingsInterface
     */
    public function getConnectionSettings()
    {
        return $this->connectionSettings;
    }

    /**
     * Check if the connection settings have been set.
     *
     * @return boolean
     */
    public function hasConnectionSettings()
    {
        return isset($this->connectionSettings);
    }

    /**
     * Get error message for file not found.
     *
     * @param string $fileName
     * @return string
     */
    protected function getFileNotFoundMessage($fileName)
    {
        return sprintf('File %s not found.', $fileName);
    }

    /**
     * Obtain an unique identifier for the given file name based on the connection settings.
     *
     * @param string $file
     * @return string
     */
    public function getFileLocationHash($file)
    {
        if (!$this->hasConnectionSettings()) {
            return md5($file);
        }
        return md5($file . $this->getConnectionSettings()->getHash());
    }

    /**
     * Validate the connection settings.
     *
     * @throws \Exception
     */
    public function validateConnectionSettings()
    {
        $connectionSettings = $this->getConnectionSettings();
        if ($connectionSettings->getServerType() != $this->getServerType()) {
            throw new ConnectionSettingsException(
                $connectionSettings,
                sprintf(
                    'File client "%s" is incompatible with connection settings "%s".',
                    $this->getServerType(),
                    $connectionSettings->getServerType()
                )
            );
        }
        $connectionSettings->validate();
    }
}