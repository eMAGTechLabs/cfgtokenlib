<?php

namespace ConfigToken\TokenResolver\Types;


use ConfigToken\Event;
use ConfigToken\EventManager;

/**
 * On-demand token resolver that queries a decoupled client to provide values
 * for the requested token names via events.
 *
 * @package ConfigToken\TokenResolver\Types
 */
class OnDemandTokenResolver extends RegisteredTokenResolver
{
    const TYPE = 'on-demand';

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
        return static::TYPE;
    }

    /**
     * Dispatch an event to query the registered listeners for data.
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

    /**
     * Check if the token value with the given name is registered.
     *
     * @param string $tokenName The identifier of the token value.
     * @return boolean
     */
    public function hasValue($tokenName)
    {
        return $this->queryListeners(
            static::EVENT_ID_IS_TOKEN_VALUE_REGISTERED,
            false,
            array(
                static::EVENT_TOKEN_NAME => $tokenName
            )
        ) !== false;
    }

    /**
     * Query the listeners for the token value with the given name.
     *
     * @param string $tokenName The name of the token value.
     * @param string|null $default The default value to return if no value registered for given token name.
     * @return string|null If there is no token value registered with the given name.
     */
    public function getValue($tokenName, $default = null)
    {
        return $this->queryListeners(
            static::EVENT_ID_GET_REGISTERED_TOKEN_VALUE,
            $default,
            array(
                static::EVENT_TOKEN_NAME => $tokenName
            )
        );
    }

    /**
     * Query the listeners to see if they are able to provide token values.
     *
     * @return boolean
     */
    public function hasValues()
    {
        return $this->queryListeners(static::EVENT_ID_HAS_REGISTERED_TOKEN_VALUES, true) === true;
    }
}