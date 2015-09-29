<?php

namespace ConfigToken\Tests\EventSystem;


use ConfigToken\EventSystem\EventDispatcherFactory;
use ConfigToken\EventSystem\Exceptions\InvalidDispatcherException;
use ConfigToken\Tests\EventSystem\Mocks\BadEventDispatcher;
use ConfigToken\Tests\EventSystem\Mocks\CustomEventDispatcher;

class EventDispatcherFactoryTest extends \PHPUnit_Framework_TestCase
{
    protected static $defaultDispatcherClass;

    public function __construct($name = null, array $data = array(), $dataName = '')
    {
        if (!isset(static::$defaultDispatcherClass)) {
            static::$defaultDispatcherClass = EventDispatcherFactory::getDefaultDispatcherClass();
        }
        parent::__construct($name, $data, $dataName);
    }


    public function testFactory()
    {
        EventDispatcherFactory::setDefaultDispatcherClass(static::$defaultDispatcherClass);
        $dispatcher = EventDispatcherFactory::getDispatcher('test1');
        $this->assertInstanceOf(static::$defaultDispatcherClass, $dispatcher);

        $dispatcher = new CustomEventDispatcher();
        EventDispatcherFactory::registerDispatcher('custom', $dispatcher);
        $this->assertEquals($dispatcher, EventDispatcherFactory::getDispatcher('custom'));

        EventDispatcherFactory::removeDispatcher('custom');
        $dispatcher = EventDispatcherFactory::getDispatcher('custom');
        $this->assertInstanceOf(static::$defaultDispatcherClass, $dispatcher);
    }

    /**
     * @expectedException \ConfigToken\EventSystem\Exceptions\InvalidDispatcherException
     */
    public function testFactoryException1()
    {
        $badDispatcherClass = BadEventDispatcher::getClassName();
        EventDispatcherFactory::setDefaultDispatcherClass($badDispatcherClass);
        $defaultDispatcherClass = EventDispatcherFactory::getDefaultDispatcherClass();
        $this->assertEquals($badDispatcherClass, $defaultDispatcherClass);
        try {
            EventDispatcherFactory::getDispatcher('test2');
        } catch (InvalidDispatcherException $e) {
            EventDispatcherFactory::setDefaultDispatcherClass(static::$defaultDispatcherClass);
            throw $e;
        }
    }

    /**
     * @expectedException \ConfigToken\EventSystem\Exceptions\EventDispatcherNotRegisteredException
     */
    public function testFactoryException2()
    {
        EventDispatcherFactory::removeDispatcher('not-registered');
    }
}