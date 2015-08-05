<?php

namespace ConfigToken\TokenResolver\Types;


use ConfigToken\Event;
use ConfigToken\EventManager;

class OnDemandTokenResolver extends RegisteredTokenResolver
{
    const EVENT_ID_IS_TOKEN_VALUE_REGISTERED = 'is-token-value-registered';
    const EVENT_ID_GET_REGISTERED_TOKEN_VALUE = 'get-registered-token-value';
    const EVENT_ID_HAS_REGISTERED_TOKEN_VALUES = 'has-registered-token-values';
    const EVENT_TOKEN_NAME = 'token-name';

    /**
     * Get the token resolver type identifier.
     *
     * @return string
     */
    public static function getType()
    {
        return 'on-demand';
    }

    /**
     * Query registered listeners.
     *
     * @param string $eventId
     * @param boolean|mixed $defaultResult
     * @param array|null $data
     * @return boolean|mixed
     */
    protected function queryListeners($eventId, $defaultResult = false, $data = null)
    {
        $eventManager = EventManager::getInstance();
        if (!$eventManager->hasListeners()) {
            return $defaultResult;
        }
        $event = new Event($eventId);
        if (isset($data)) {
            $event->data = $data;
        }
        $event->data[Event::SENDER] = $this;
        $eventManager->dispatch($event);
        return isset($event->data[Event::RESULT]) ? $event->data[Event::RESULT] : $defaultResult;
    }

    public function isTokenValueRegistered($tokenName)
    {
        return $this->queryListeners(
            static::EVENT_ID_IS_TOKEN_VALUE_REGISTERED,
            false,
            array(
                static::EVENT_TOKEN_NAME => $tokenName
            )
        ) !== false;
    }

    public function getRegisteredTokenValue($tokenName)
    {
        return $this->queryListeners(
            static::EVENT_ID_GET_REGISTERED_TOKEN_VALUE,
            false,
            array(
                static::EVENT_TOKEN_NAME => $tokenName
            )
        );
    }

    public function hasRegisteredTokenValues()
    {
        return $this->queryListeners(static::EVENT_ID_HAS_REGISTERED_TOKEN_VALUES, true) === true;
    }
}