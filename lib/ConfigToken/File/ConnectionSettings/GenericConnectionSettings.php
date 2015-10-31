<?php

namespace ConfigToken\File\ConnectionSettings;


use ConfigToken\EventSystem\EventDispatcherFactory;
use ConfigToken\EventSystem\EventDispatcherInterface;
use ConfigToken\Options\Exceptions\UnknownOptionException;
use ConfigToken\Options\Options;
use ConfigToken\Options\OptionsInterface;

class GenericConnectionSettings extends Options implements ConnectionSettingsInterface
{
    const EVENT_DISPATCHER_ID = 'gcs-req-dis';
    const REQUEST_EVENT_ID = 'req-cs';

    /**
     * Initialize the connection settings instance with values from the given option set.
     *
     * @param OptionsInterface|null $options
     * @throws UnknownOptionException
     */
    function __construct(OptionsInterface $options = null)
    {
        $this->initialize();
        if (isset($options)) {
            $this->setValues($options->getValues());
        }
    }

    /**
     * Initialize the required and optional settings with default values.
     */
    protected function initialize() {}

    /**
     * Get the prompt message for the given connection setting.
     *
     * @param string $key
     * @return string
     */
    protected function getPromptForKey($key)
    {
        return sprintf('Please enter a value for "%s"', $key);
    }

    /**
     * Request the missing values via the given event dispatcher.
     *
     * @param EventDispatcherInterface $eventDispatcher
     * @return boolean
     */
    public function requestValues(EventDispatcherInterface $eventDispatcher=null)
    {
        if (!isset($eventDispatcher)) {
            $eventDispatcher = EventDispatcherFactory::getDispatcher(static::EVENT_DISPATCHER_ID);
        }
        if (!$eventDispatcher->hasRegisteredListeners()) {
            return false;
        }
        $keys = $this->getRequiredOrOptionalKeys();
        foreach ($keys as $key) {
            $event = new RequestEvent(static::REQUEST_EVENT_ID);
            $event->setKey($key);
            $event->setPrompt($this->getPromptForKey($key));
            do {
                if (!$event->isBadValue() && $this->hasDefaultValue($key)) {
                    $event->setValue($this->getDefaultValue($key));
                }
                $eventDispatcher->dispatchEvent($event);
                $event->setBadValue(
                    (!$event->hasValue() && $this->isKeyRequired($key)) ||
                    ($event->hasValue() && (!$this->isValidValue($key, $event->getValue())))
                );
            } while ($event->isBadValue());
        }
        return true;
    }

}