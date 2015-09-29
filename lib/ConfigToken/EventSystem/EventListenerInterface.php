<?php

namespace ConfigToken\EventSystem;


interface EventListenerInterface
{
    /**
     * Dispatched event handler.
     * Return false to stop propagation.
     *
     * @param EventInterface $event
     * @return boolean
     */
    public function handleEvent(EventInterface $event);
}