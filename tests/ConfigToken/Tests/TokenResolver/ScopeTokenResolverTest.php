<?php

namespace ConfigToken\Tests\TokenResolver;

use ConfigToken\TokenParser;
use ConfigToken\TokenResolver\Types\ScopeTokenResolver;


class ScopeTokenResolverTest extends  \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \ConfigToken\TokenResolver\Exception\OutOfScopeException
     */
    public function testOutOfScopeException()
    {
        $scope = array(
            'a' => 'value of a',
        );
        $lines = array(
            'Value of a is [[json:c.|upper|underscore]]',
        );
        $string = implode("\n", $lines);

        $tokenParser = new TokenParser();
        $tokens = $tokenParser->parseString($string);

        $tokenResolver = new ScopeTokenResolver('json', $scope);
        $tokenResolver->setIgnoreOutOfScope(false);

        $tokens->resolve($tokenResolver);
    }

    /**
     * @expectedException \ConfigToken\TokenResolver\Exception\UnknownTokenException
     */
    public function testUnknownTokenException()
    {
        $scope = array(
            'a' => 'value of a',
        );
        $lines = array(
            'Value of a is [[other:a]]',
        );
        $string = implode("\n", $lines);

        $tokenParser = new TokenParser();
        $tokens = $tokenParser->parseString($string);

        $tokenResolver = new ScopeTokenResolver('json', $scope);

        $tokens->resolve($tokenResolver, false);
    }

    /**
     * @expectedException \ConfigToken\TokenResolver\Exception\ScopeTokenValueSerializationException
     */
    public function testScopeTokenValueSerializationException()
    {
        $scope = array(
            'a' => 'value of a',
            'b' => array('b'),
        );
        $lines = array(
            'Value of a is [[json:a]]',
            'Value of b is [[json:b]]',
        );
        $string = implode("\n", $lines);

        $tokenParser = new TokenParser();
        $tokens = $tokenParser->parseString($string);

        $tokenResolver = new ScopeTokenResolver('json', $scope);

        $tokens->resolve($tokenResolver, false);
    }

    /**
     * @expectedException \ConfigToken\TokenResolver\Exception\ScopeTokenValueSerializationException
     */
    public function testCustomSerializerException()
    {
        $scope = array(
            'a' => 'value of a',
            'b' => array('c'),
        );
        $lines = array(
            'Value of a is [[json:a]]',
            'Value of b is [[json:b]]',
        );
        $string = implode("\n", $lines);

        $tokenParser = new TokenParser();
        $tokens = $tokenParser->parseString($string);

        $tokenResolver = new ScopeTokenResolver('json', $scope);
        $tokenResolver->setSerializer(new CustomScopeTokenValueSerializer());

        $tokens->resolve($tokenResolver, false);
    }

    /**
     * @expectedException \ConfigToken\TokenResolver\Exception\TokenFormatException
     */
    public function testTokenFormatException()
    {
        $scope = array(
            'a' => 'value of a',
        );
        $lines = array(
            'Value of a is [[json:]]',
        );
        $string = implode("\n", $lines);

        $tokenParser = new TokenParser();
        $tokens = $tokenParser->parseString($string);

        $tokenResolver = new ScopeTokenResolver('json', $scope);

        $tokens->resolve($tokenResolver, false);
    }
}