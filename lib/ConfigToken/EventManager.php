<?php

namespace ConfigToken;


class EventManager
{
    /** @var EventManager */
    private static $instance;
    /** @var EventListenerInterface[] */
    protected $listeners;

    /**
     * Get the event manager singleton instance.
     *
     * @return EventManager The event manager singleton.
     */
    public static function getInstance()
    {
        if (null === self::$instance) {
            self::$instance = new static();
        }

        return self::$instance;
    }

    protected function __construct()
    {
        $this->clear();
    }

    /**
     * @codeCoverageIgnore
     */
    private function __clone()
    {
    }

    /**
     * Dispatch the given event to all registered listeners.
     *
     * @param Event $event
     */
    public function dispatch(Event $event)
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
    public function register(EventListenerInterface $listener)
    {
        $this->listeners[spl_object_hash($listener)] = $listener;
    }

    /**
     * Un-register an event listener.
     *
     * @param EventListenerInterface $listener The event listener.
     */
    public function remove(EventListenerInterface $listener)
    {
        if ($this->isRegistered($listener)) {
            unset($this->listeners[spl_object_hash($listener)]);
        }
    }

    /**
     * Un-register all event listeners
     */
    public function clear()
    {
        $this->listeners = array();
    }

    /**
     * Check if the given event listener is registered.
     *
     * @param EventListenerInterface $listener
     * @return boolean
     */
    public function isRegistered(EventListenerInterface $listener)
    {
        return isset($this->listeners[spl_object_hash($listener)]);
    }

    /**
     * Check if any event listeners are registered.
     *
     * @return boolean
     */
    public function hasListeners()
    {
        return count($this->listeners) > 0;
    }
}