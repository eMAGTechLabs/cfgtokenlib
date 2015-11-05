<?php

namespace ConfigToken\EventSystem;


use ConfigToken\EventSystem\Exceptions\EventListenerNotRegisteredException;

class DefaultEventListenerManager implements EventListenerManagerInterface
{
    /** @var EventListenerInterface[] */
    protected $listeners;

    /**
     * Register an event listener.
     *
     * @param EventListenerInterface $listener The event listener.
     */
    public function registerListener(EventListenerInterface $listener)
    {
        $this->listeners[spl_object_hash($listener)] = $listener;
    }

    /**
     * Un-register an event listener.
     *
     * @param EventListenerInterface $listener The event listener.
     * @throws EventListenerNotRegisteredException
     */
    public function removeListener(EventListenerInterface $listener)
    {
        if (!$this->isListenerRegistered($listener)) {
            throw new EventListenerNotRegisteredException();
        }
        unset($this->listeners[spl_object_hash($listener)]);
    }

    /**
     * Un-register all event listeners
     */
    public function removeAllListeners()
    {
        $this->listeners = array();
    }

    /**
     * Check if the given event listener is registered.
     *
     * @param EventListenerInterface $listener
     * @return boolean
     */
    public function isListenerRegistered(EventListenerInterface $listener)
    {
        return isset($this->listeners[spl_object_hash($listener)]);
    }

    /**
     * Check if any event listeners are registered.
     *
     * @return boolean
     */
    public function hasRegisteredListeners()
    {
        return count($this->listeners) > 0;
    }

    /**
     * Get an array of all registered listeners.
     *
     * @return EventListenerInterface[]
     */
    public function getRegisteredListeners()
    {
        return $this->listeners;
    }


}