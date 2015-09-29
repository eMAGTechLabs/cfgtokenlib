<?php

namespace ConfigToken\Tests\EventSystem;


use ConfigToken\EventSystem\DefaultEventDispatcher;
use ConfigToken\EventSystem\EventDispatcherFactory;
use ConfigToken\EventSystem\SimpleEvent;
use ConfigToken\Tests\EventSystem\Mocks\CustomEventListener;

class DefaultEventDispatcherTest extends \PHPUnit_Framework_TestCase
{
    public function testRegistration()
    {
        $dispatcher = EventDispatcherFactory::getDispatcher('default');
        $this->assertInstanceOf(DefaultEventDispatcher::getClassName(), $dispatcher);

        /** @var DefaultEventDispatcher $dispatcher */
        $listener1 = new CustomEventListener();
        $this->assertFalse($dispatcher->hasRegisteredListeners());
        $dispatcher->registerListener($listener1);
        $this->assertTrue($dispatcher->hasRegisteredListeners());
        $event1 = new SimpleEvent('e1');
        $dispatcher->dispatchEvent($event1);
        $receivedEvent = $listener1->popEvent();
        $this->assertEquals($event1, $receivedEvent);
        $this->assertEquals('e1', $receivedEvent->getEventId());
        $listener2 = new CustomEventListener();
        $dispatcher->registerListener($listener2);
        $dispatcher->dispatchEvent($event1);
        $this->assertEquals($event1, $listener1->popEvent());
        $this->assertEquals($event1, $listener2->popEvent());
        $dispatcher->removeListener($listener1);
        $dispatcher->dispatchEvent($event1);
        $this->assertNull($listener1->popEvent());
        $this->assertEquals($event1, $listener2->popEvent());
        $dispatcher->registerListener($listener1);
        $dispatcher->dispatchEvent($event1);
        $listener1->popEvent();
        $this->assertNull($listener1->popEvent());
        $this->assertEquals($event1, $listener2->popEvent());
        $listener2->setHandlerResult(false);
        $dispatcher->dispatchEvent($event1);
        $this->assertEquals($event1, $listener2->popEvent());
        $this->assertNull($listener1->popEvent());
        $dispatcher->removeAllListeners();
        $this->assertNull($listener1->popEvent());
        $this->assertNull($listener2->popEvent());
    }

    /**
     * @expectedException \ConfigToken\EventSystem\Exceptions\EventListenerNotRegisteredException
     */
    public function testEventListenerNotRegisteredException()
    {
        $dispatcher = new DefaultEventDispatcher();
        $listener = new CustomEventListener();
        $dispatcher->removeListener($listener);
    }
}