<?php

namespace ConfigToken\EventSystem;


interface EventDispatcherInterface
{
    /**
     * Get the Event Listener Manager.
     *
     * @return EventListenerManagerInterface
     */
    public function getListenerManager();

    /**
     * Dispatch the given event to all registered listeners.
     *
     * @param EventInterface $event
     */
    public function dispatchEvent(EventInterface $event);
}