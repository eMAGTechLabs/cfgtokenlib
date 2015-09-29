<?php

namespace ConfigToken\EventSystem;


use ConfigToken\EventSystem\Exceptions\EventListenerNotRegisteredException;

class DefaultEventDispatcher implements EventDispatcherInterface, EventSourceInterface
{
    /** @var EventListenerInterface[] */
    protected $listeners;

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
     * Dispatch the given event to all registered listeners.
     *
     * @param EventInterface $event
     */
    public function dispatchEvent(EventInterface $event)
    {
        foreach ($this->listeners as $listener) {
            if (!$listener->handleEvent($event)) {
                break;
            }
        }
    }

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
}