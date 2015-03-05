<?php

namespace ConfigToken\Tests\TokenResolver;

use ConfigToken\TokenInjector;
use ConfigToken\TokenParser;
use ConfigToken\TokenResolver\ScopeValueSerializers\JsonScopeTokenValueSerializer;
use ConfigToken\TokenResolver\Types\JsonScopeTokenResolver;


class JsonScopeTokenResolverTest extends \PHPUnit_Framework_TestCase
{

    public function testScopeResolver()
    {
        $o = new TestObjectValue();
        $scope = array(
            'a' => 'value of a',
            'b' => null,
            'bool' => false,
            'dbl' => 3.14159265358979323,
            'o' => $o,
            'nonassoc' => array(
                'value0',
                'value1',
                'value2',
                array(
                    'c' => 'value of c',
                ),
            ),
            'assocval' => array(
                'array' => array(
                    'd' => 'value of d',
                    'e' => 'value of e',
                )
            ),
            'nonassocval' => array(
                'array' => array(
                    100,
                    101,
                    'x',
                    null,
                )
            )
        );
        $lines = array(
            'Value of a is [[json->a|upper|underscore]]',
            'Value of b is [[json->b|upper]]',
            'Value of bool is [[json->bool]]',
            'Value of dbl is [[json->dbl|underscore]]',
            'Value of o is [[json->o]]',
            'Value of nonassoc is [[json->nonassoc]]',
            'Value of nonassoc.1 is [[json->nonassoc/1]]',
            'Value of nonassoc.3 is [[json->nonassoc/3]]',
            'Value of nonassoc.3.c is [[json->nonassoc/3/c]]',
            'Value of assocval is [[json->assocval]]',
            'Value of assocval.array is [[json->assocval/array]]',
            'Value of assocval.array.e is [[json->assocval/array/e]]',
            'Value of nonassocval is [[json->nonassocval]]',
            'Value of nonassocval.array is [[json->nonassocval/array]]',
            'Value of nonassocval.array.0 is [[json->nonassocval/array/0]]',
            'Value of nonassocval.array.2 is [[json->nonassocval/array/2]]',
        );
        $expectedLines = array(
            'Value of a is VALUE_OF_A',
            'Value of b is NULL',
            'Value of bool is false',
            'Value of dbl is 3_14159',
            'Value of o is {"a":"a","b":["a","b",null]}',
            'Value of nonassoc is ["value0","value1","value2",{"c":"value of c"}]',
            'Value of nonassoc.1 is value1',
            'Value of nonassoc.3 is {"c":"value of c"}',
            'Value of nonassoc.3.c is value of c',
            'Value of assocval is {"array":{"d":"value of d","e":"value of e"}}',
            'Value of assocval.array is {"d":"value of d","e":"value of e"}',
            'Value of assocval.array.e is value of e',
            'Value of nonassocval is {"array":[100,101,"x",null]}',
            'Value of nonassocval.array is [100,101,"x",null]',
            'Value of nonassocval.array.0 is 100',
            'Value of nonassocval.array.2 is x',
        );
        $string = implode("\n", $lines);
        $expectedString = implode("\n", $expectedLines);

        $tokenParser = new TokenParser();
        $tokens = $tokenParser->parseString($string);

        $tokenResolver = new JsonScopeTokenResolver('json', $scope);
        $this->assertTrue($tokenResolver->getSerializer() instanceof JsonScopeTokenValueSerializer);
        $this->assertEquals('json', $tokenResolver->getScopeTokenName());
        $this->assertEquals($scope, $tokenResolver->getScope());
        $this->assertEquals(false, $tokenResolver->getIgnoreOutOfScope());

        $tokenResolver->setScopeTokenNameDelimiter('->');
        $this->assertEquals('->', $tokenResolver->getScopeTokenNameDelimiter());

        $tokenResolver->setScopeLevelDelimiter('/');
        $this->assertEquals('/', $tokenResolver->getScopeLevelDelimiter());

        $this->assertTrue($tokenResolver->isTokenValueRegistered('json->nonassoc'));
        $this->assertFalse($tokenResolver->isTokenValueRegistered('json->nonassoc/unknown'));
        $this->assertFalse($tokenResolver->isTokenValueRegistered('other'));

        $tokens->resolve($tokenResolver);


        $result = TokenInjector::injectString($string, $tokens);

        $this->assertEquals($expectedString, $result);
    }

    /**
     * @expectedException \ConfigToken\TokenResolver\Exception\ScopeTokenValueSerializationException
     */
    public function testUnserializable()
    {
        $scope = array(
            'a' => fopen('php://stdout', 'w'),
        );
        $lines = array(
            'Value of a is [[json:a]]',
        );
        $string = implode("\n", $lines);

        $tokenParser = new TokenParser();
        $tokens = $tokenParser->parseString($string);

        $tokenResolver = new JsonScopeTokenResolver('json', $scope);
        $tokens->resolve($tokenResolver);
    }
}