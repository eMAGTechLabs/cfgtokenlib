<?php

namespace ConfigToken\EventSystem;


class DefaultEventDispatcher implements EventDispatcherInterface
{
    protected $listenerManager;

    function __construct(EventListenerManagerInterface $listenerManager = null)
    {
        if (!isset($listenerManager)) {
            $listenerManager = static::makeEventListenerManager();
        }
        $this->listenerManager = $listenerManager;
    }

    public static function makeEventListenerManager()
    {
        return new DefaultEventListenerManager();
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
     * Get the Event Listener Manager.
     *
     * @return EventListenerManagerInterface
     */
    public function getListenerManager()
    {
        return $this->listenerManager;
    }

    /**
     * Dispatch the given event to all registered listeners.
     *
     * @param EventInterface $event
     */
    public function dispatchEvent(EventInterface $event)
    {
        $listeners = $this->getListenerManager()->getRegisteredListeners();
        foreach ($listeners as $listener) {
            if (!$listener->handleEvent($event)) {
                break;
            }
        }
    }
}