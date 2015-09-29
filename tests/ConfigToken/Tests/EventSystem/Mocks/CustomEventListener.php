<?php

namespace ConfigToken\Tests\EventSystem\Mocks;


use ConfigToken\EventSystem\EventInterface;
use ConfigToken\EventSystem\EventListenerInterface;

class CustomEventListener implements EventListenerInterface
{
    protected $eventStack = array();
    protected $handlerResult = true;

    /**
     * Dispatched event handler.
     * Return false to stop propagation.
     *
     * @param EventInterface $event
     * @return boolean
     */
    public function handleEvent(EventInterface $event)
    {
        $this->eventStack[] = $event;
        return $this->handlerResult;
    }

    /**
     * Pop the last handled event.
     *
     * @return EventInterface
     */
    public function popEvent()
    {
        return array_pop($this->eventStack);
    }

    public function setHandlerResult($value)
    {
        $this->handlerResult = $value;
        return $this;
    }
}