<?php

namespace ConfigToken;


interface EventListenerInterface
{
    /**
     * Dispatched event handler. Return false to stop propagation.
     * @param Event $event
     * @return boolean
     */
    public function handleEvent(Event $event);
}