<?php

namespace ConfigToken\File\ConnectionSettings;


use ConfigToken\EventSystem\EventDispatcherInterface;
use ConfigToken\Options\OptionsInterface;

interface ConnectionSettingsInterface extends OptionsInterface
{
    /**
     * Request the missing values via the given event dispatcher.
     *
     * @param EventDispatcherInterface $eventDispatcher
     * @return boolean
     */
    public function requestValues(EventDispatcherInterface $eventDispatcher);
}