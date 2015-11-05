<?php

namespace ConfigToken\EventSystem;


use ConfigToken\EventSystem\Exceptions\EventListenerNotRegisteredException;

interface EventListenerManagerInterface
{

    /**
     * Register an event listener.
     *
     * @param EventListenerInterface $listener The event listener.
     */
    public function registerListener(EventListenerInterface $listener);

    /**
     * Un-register an event listener.
     *
     * @param EventListenerInterface $listener The event listener.
     * @throws EventListenerNotRegisteredException
     */
    public function removeListener(EventListenerInterface $listener);

    /**
     * Un-register all event listeners
     */
    public function removeAllListeners();

    /**
     * Check if the given event listener is registered.
     *
     * @param EventListenerInterface $listener
     * @return boolean
     */
    public function isListenerRegistered(EventListenerInterface $listener);

    /**
     * Check if any event listeners are registered.
     *
     * @return boolean
     */
    public function hasRegisteredListeners();

    /**
     * Get an array of all registered listeners.
     *
     * @return EventListenerInterface[]
     */
    public function getRegisteredListeners();
}