<?php

namespace ConfigToken\EventSystem;


class SimpleEvent implements EventInterface
{
    /** @var string */
    protected $id;

    /**
     * @param string $id The event id.
     */
    public function __construct($id)
    {
        $this->setEventId($id);
    }

    /**
     * Get the id.
     *
     * @return string
     */
    public function getEventId()
    {
        return $this->id;
    }

    /**
     * Set the id.
     *
     * @param string $id The new value.
     * @return $this
     */
    public function setEventId($id)
    {
        $this->id = $id;
        return $this;
    }
}