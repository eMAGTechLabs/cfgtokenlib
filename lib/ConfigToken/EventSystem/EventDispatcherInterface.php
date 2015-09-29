<?php

namespace ConfigToken\EventSystem;


interface EventDispatcherInterface
{
    /**
     * Dispatch the given event to all registered listeners.
     *
     * @param EventInterface $event
     */
    public function dispatchEvent(EventInterface $event);

    /**
     * Check if any event listeners are registered.
     *
     * @return boolean
     */
    public function hasRegisteredListeners();
}