<?php

namespace ConfigToken\Tests\EventSystem\Mocks;


use ConfigToken\EventSystem\DefaultEventListenerManager;
use ConfigToken\EventSystem\EventDispatcherInterface;
use ConfigToken\EventSystem\EventInterface;
use ConfigToken\EventSystem\EventListenerManagerInterface;

class CustomEventDispatcher implements EventDispatcherInterface
{
    /**
     * Dispatch the given event to all registered listeners.
     *
     * @param EventInterface $event
     */
    public function dispatchEvent(EventInterface $event)
    {
    }

    /**
     * Get the Event Listener Manager.
     *
     * @return EventListenerManagerInterface
     */
    public function getListenerManager()
    {
        static $listenerManager;
        if (!isset($listenerManager)) {
            $listenerManager = new DefaultEventListenerManager();
        }
        return $listenerManager;
    }


}