<?php

namespace ConfigToken\Tests\TokenResolver;


use ConfigToken\Event;
use ConfigToken\EventListenerInterface;

class TestEventListener implements  EventListenerInterface
{
    /** @var callable */
    protected $callback;

    function __construct($callback)
    {
        $this->callback = $callback;
    }

    /**
     * Dispatched event handler. Return false to stop propagation.
     * @param Event $event
     * @return boolean
     */
    public function handleEvent(Event $event)
    {
        return call_user_func($this->callback, $event);
    }
}