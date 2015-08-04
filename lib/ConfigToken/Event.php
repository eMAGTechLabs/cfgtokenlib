<?php

namespace ConfigToken;


class Event
{
    const SENDER = 'sender';
    const RESULT = 'result';

    /** @var string */
    protected $id;
    /** @var array */
    public $data;

    /**
     * @param string $id The event id.
     */
    public function __construct($id)
    {
        $this->setId($id);
        $this->data = array();
    }

    /**
     * Get the id.
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the id.
     *
     * @param string $value The new value.
     * @return $this
     */
    public function setId($value)
    {
        $this->id = $value;
        return $this;
    }
}