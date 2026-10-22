<?php

namespace ConfigToken\Tests\TokenFilter;

use ConfigToken\TokenFilter\TokenFilterFactory;
use ConfigToken\TokenParser;
use ConfigToken\TokenResolver\Types\RegisteredTokenResolver;


class TokenFilterTest extends \PHPUnit\Framework\TestCase
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

    public function testUnknownFilterExceptionForFilteredValue()
    {
        $this->expectException(\ConfigToken\TokenFilter\Exception\UnknownFilterException::class);

        TokenFilterFactory::getFilteredValue('unknown', 'value');
    }

    public function testUnknownFilterException()
    {
        $this->expectException(\ConfigToken\TokenFilter\Exception\UnknownFilterException::class);

        TokenFilterFactory::getByName('unknown');
    }

    public function testAlreadyRegistered()
    {
        $this->expectException(\ConfigToken\Exception\AlreadyRegisteredException::class);

        $tokenFilter = new CustomTokenFilter();
        TokenFilterFactory::register($tokenFilter);
        TokenFilterFactory::register($tokenFilter);
    }
}