<?php

namespace ConfigToken\Tests\TokenResolver;


use ConfigToken\Event;
use ConfigToken\EventManager;
use ConfigToken\TokenResolver\Types\OnDemandTokenResolver;
use ConfigToken\TreeCompiler;
use ConfigToken\TreeCompiler\Xref;

class OnDemandTokenResolverTest extends \PHPUnit_Framework_TestCase
{
    protected $onDemandTokenValues;

    public function eventListenerCallback(Event $event)
    {
        if (isset($event->data[Event::SENDER]) && ($event->data[Event::SENDER] instanceof OnDemandTokenResolver)) {
            switch ($event->getId()) {
                case OnDemandTokenResolver::EVENT_ID_HAS_REGISTERED_TOKEN_VALUES:
                case OnDemandTokenResolver::EVENT_ID_IS_TOKEN_VALUE_REGISTERED:
                    $event->data[Event::RESULT] = true;
                    return false;
                case OnDemandTokenResolver::EVENT_ID_GET_REGISTERED_TOKEN_VALUE:
                    if (isset($this->onDemandTokenValues[$event->data[OnDemandTokenResolver::EVENT_TOKEN_NAME]])) {
                        $event->data[Event::RESULT] = $this->onDemandTokenValues[$event->data[OnDemandTokenResolver::EVENT_TOKEN_NAME]];
                        return false;
                    }
                    return true;
            }
        }
        return true;
    }

    public function testOnDemandResolver()
    {
        $xrefDep1 = new Xref('file', '/dep1.json');
        $xrefDep1->setData(
            array(
                'on-demand1' => '{{token1}}',
                'on-demand2' => '{{token2}}',
            )
        )->setResolved(true);

        $xrefMain = new Xref('file', '/main.json');
        $xrefMain->setData(
            array(
                'include' => array(
                    'xref' => array(
                        'dep1' => array(
                            'type' => 'file',
                            'src' => '/dep1.json',
                            'resolve' => array(
                                array(
                                    'type' => 'on-demand',
                                    'options' => array(
                                        'token-prefix' => '{{',
                                        'token-suffix' => '}}',
                                    ),
                                    'values' => array(),
                                ),
                            ),
                        ),
                    ),
                    'main' => array(
                        'dep1',
                    ),
                ),
            )
        )->setResolved(true);

        $treeCompiler = new TreeCompiler();
        $treeCompiler->getXrefs()->add($xrefDep1);

        $compiled = $treeCompiler->compileXref($xrefMain);

        $expected = array(
            'on-demand1' => '{{token1}}',
            'on-demand2' => '{{token2}}',
        );

        $this->assertEquals($expected, $compiled);

        $this->onDemandTokenValues = array(
            'token1' => 'ok1',
            'token2' => 'ok2',
        );

        $testListener = new TestEventListener(array($this, 'eventListenerCallback'));
        $eventManager = EventManager::getInstance();
        $eventManager->register($testListener);
        $this->assertTrue($eventManager->hasListeners());

        $compiled = $treeCompiler->compileXref($xrefMain);


        $expected = array(
            'on-demand1' => 'ok1',
            'on-demand2' => 'ok2',
        );

        $this->assertEquals($expected, $compiled);

        $eventManager->remove($testListener);
        $this->assertFalse($eventManager->hasListeners());
    }
}