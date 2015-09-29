<?php

namespace ConfigToken\EventSystem;


use ConfigToken\EventSystem\Exceptions\EventDispatcherNotRegisteredException;
use ConfigToken\EventSystem\Exceptions\InvalidDispatcherException;

class EventDispatcherFactory
{
    protected static $dispatchers = array();
    public static $defaultDispatcherClass = 'ConfigToken\EventSystem\DefaultEventDispatcher';

    /**
     * @return string
     */
    public static function getDefaultDispatcherClass()
    {
        return static::$defaultDispatcherClass;
    }

    /**
     * @param string $className
     */
    public static function setDefaultDispatcherClass($className)
    {
        static::$defaultDispatcherClass = $className;
    }

    /**
     * @param string $id
     * @throws \Exception
     * @return EventDispatcherInterface
     */
    public static function getDispatcher($id)
    {
        if (!isset(static::$dispatchers[$id])) {
            static::$dispatchers[$id] = new static::$defaultDispatcherClass();
        }
        if (!static::$dispatchers[$id] instanceof EventDispatcherInterface) {
            throw new InvalidDispatcherException(get_class(static::$dispatchers[$id]));
        }
        return static::$dispatchers[$id];
    }

    /**
     * @param string $id
     * @param EventDispatcherInterface $dispatcher
     */
    public static function registerDispatcher($id, EventDispatcherInterface $dispatcher)
    {
        static::$dispatchers[$id] = $dispatcher;
    }

    /**
     * @param string $id
     * @throws \Exception
     */
    public static function removeDispatcher($id)
    {
        if (!isset(static::$dispatchers[$id])) {
            throw new EventDispatcherNotRegisteredException($id);
        }
        unset(static::$dispatchers[$id]);
    }
}