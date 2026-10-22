<?php

namespace ConfigToken\Tests\TokenResolver;

use ConfigToken\TokenParser;
use ConfigToken\TokenResolver\Types\ScopeTokenResolver;


class ScopeTokenResolverTest extends \PHPUnit\Framework\TestCase
{

    public function testOutOfScopeException()
    {
        $this->expectException(\ConfigToken\TokenResolver\Exception\OutOfScopeException::class);

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

    public function testUnknownTokenException()
    {
        $this->expectException(\ConfigToken\TokenResolver\Exception\UnknownTokenException::class);

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

    public function testScopeTokenValueSerializationException()
    {
        $this->expectException(\ConfigToken\TokenResolver\Exception\ScopeTokenValueSerializationException::class);

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

    public function testCustomSerializerException()
    {
        $this->expectException(\ConfigToken\TokenResolver\Exception\ScopeTokenValueSerializationException::class);

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

    public function testTokenFormatException()
    {
        $this->expectException(\ConfigToken\TokenResolver\Exception\TokenFormatException::class);

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