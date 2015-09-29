<?php

namespace ConfigToken\EventSystem;


interface EventInterface
{
    /**
     * Get the event identifier.
     *
     * @return string
     */
    public function getEventId();

    /**
     * Set the event identifier.
     *
     * @param string $id
     * @return $this
     */
    public function setEventId($id);
}