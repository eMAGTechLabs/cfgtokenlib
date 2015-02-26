<?php

namespace ConfigToken\Tests\TokenFilter;

use ConfigToken\TokenFilter\TokenFilterFactory;
use ConfigToken\TokenParser;
use ConfigToken\TokenResolver\Types\RegisteredTokenResolver;


class TokenFilterTest extends \PHPUnit_Framework_TestCase
{
    public function testCustomFilter()
    {
        $tokenFilter = new CustomTokenFilter();
        TokenFilterFactory::register($tokenFilter);
        $this->assertTrue(TokenFilterFactory::isRegisteredByName($tokenFilter::getName()));

        $data = '[[token|custom_test_filter|lower]]';
        $tokenResolver = new RegisteredTokenResolver(array('token' => 'Class.Method'));
        $tokenParser = new TokenParser();
        $tokens = $tokenParser->parseString($data);
        $tokens->resolve($tokenResolver);
        $token = $tokens->get($data);
        $this->assertEquals('class->method', $token->getTokenValue());
    }

    public function testDefaultValueForUnknown()
    {
        $this->assertEquals('default', TokenFilterFactory::getFilteredValue('unknown', 'value', true, 'default'));
    }

    /**
     * @expectedException \ConfigToken\TokenFilter\Exception\UnknownFilterException
     */
    public function testUnknownFilterExceptionForFilteredValue()
    {
        TokenFilterFactory::getFilteredValue('unknown', 'value');
    }

    /**
     * @expectedException \ConfigToken\TokenFilter\Exception\UnknownFilterException
     */
    public function testUnknownFilterException()
    {
        TokenFilterFactory::getByName('unknown');
    }

    /**
     * @expectedException \ConfigToken\Exception\AlreadyRegisteredException
     */
    public function testAlreadyRegistered()
    {
        $tokenFilter = new CustomTokenFilter();
        TokenFilterFactory::register($tokenFilter);
        TokenFilterFactory::register($tokenFilter);
    }
}