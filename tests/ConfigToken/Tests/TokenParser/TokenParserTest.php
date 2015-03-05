<?php

namespace ConfigToken\Tests\TreeSerializer;

use ConfigToken\TokenFilter\Exception\UnknownFilterException;
use ConfigToken\TokenInjector;
use ConfigToken\TokenParser;
use ConfigToken\TokenResolver\Exception\UnknownTokenException;
use ConfigToken\Token;
use ConfigToken\TokenCollection;
use ConfigToken\TokenResolver\Types\RegisteredTokenResolver;


class TokenParserTest extends \PHPUnit_Framework_TestCase
{
    public function testEmptyTokenParser()
    {
        $data = array();
        $jsonData = json_encode($data);
        $tokenParser = new TokenParser('~', TokenParser::getTokenRegexByDelimiters('{{', '}}'));
        $tokens = $tokenParser->parseString($jsonData);
        $this->assertTrue($tokens->isEmpty());
        $this->assertEquals('~', $tokenParser->getFilterDelimiter());
        $this->assertEquals('/\{\{+(.*?)\}\}/', $tokenParser->getTokenRegex());
    }

    public function testSimpleTokenParser()
    {
        $data = array(
            'a' => '[[token1]]',
            'b' => '[[token5]]',
            'c_[[token1~underscore~lower]]' => 'prefix-[[token1]]-suffix-[[token5]]',
            'j' => '[[token6~dash~lower]]',
            'd' => '[[token2~dash]]',
            'e' => '[[token3~dot]]',
            'f' => '[[token4~lower~unknown_filter]]',
            'g' => '[[token5~upper]]',
            'h' => '[[token5~underscore]]',
            'i' => '[[token5~underscore~upper]]',
        );
        $expected = array(
            'a' => 'The Value-of.Token1',
            'b' => 'The Value-of.Token5',
            'c_the_value_of_token1' => 'prefix-The Value-of.Token1-suffix-The Value-of.Token5',
            'j' => '[[token6~dash~lower]]',
            'd' => 'The-Value-of-Token2',
            'e' => 'The.Value.of.Token3',
            'f' => 'the value-of.token4',
            'g' => 'THE VALUE-OF.TOKEN5',
            'h' => 'The_Value_of_Token5',
            'i' => 'THE_VALUE_OF_TOKEN5',
        );
        $jsonData = json_encode($data);
        $jsonExpectedData = json_encode($expected);

        $tokenParser = new TokenParser('~');
        $tokens = $tokenParser->parseString($jsonData);
        $this->assertEquals(md5($jsonData), $tokens->getSourceHash());

        $result = $tokens->findByName('token1');
        $this->assertEquals(2, count($result));
        $result = $tokens->findByName('token2');
        $this->assertEquals(1, count($tokens->findByName('token2')));
        $this->assertEquals(0, count($tokens->findByName('token7')));
        $this->assertTrue(isset($result['[[token2~dash]]']));
        $result = current($result);
        /** @var Token $result */

        $filters = $result->getFilters();
        $this->assertEquals(1, count($filters));
        $this->assertEquals('dash', current($filters));

        $this->assertEquals(4, count($tokens->findByName('token5')));
        $this->assertEquals(10, $tokens->getCount());

        $tokenValues = array(
            'token1' => 'The Value-of.Token1',
            'token2' => 'The_Value of.Token2',
            'token3' => 'The Value-of_Token3',
            'token4' => 'The Value-of.Token4',
            'token5' => 'The Value-of.Token5',
            'token7' => 'The Value-of.Token7'
        );
        $tokenResolver = new RegisteredTokenResolver($tokenValues);
        $this->assertEquals($tokenValues, $tokenResolver->getRegisteredTokenValues());
        $this->assertEquals($tokenValues['token5'], $tokenResolver->getRegisteredTokenValue('token5'));
        $this->assertNull($tokenResolver->getRegisteredTokenValue('token6'));

        $tokens->resolve($tokenResolver);

        $resolvedTokens = $tokens->findResolved();
        $expectedResolved = array(
            '[[token1]]',
            '[[token5]]',
            '[[token1~underscore~lower]]',
            '[[token2~dash]]',
            '[[token3~dot]]',
            '[[token4~lower~unknown_filter]]',
            '[[token5~upper]]',
            '[[token5~underscore]]',
            '[[token5~underscore~upper]]',
        );
        $this->assertEquals($expectedResolved, array_keys($resolvedTokens));

        $resolvedAndNotInjectedTokens = $tokens->findResolvedAndNotInjected();
        $this->assertEquals($expectedResolved, array_keys($resolvedAndNotInjectedTokens));

        $this->assertTrue($tokens->hasUnresolved());
        $unresolvedTokens = $tokens->findUnresolved();
        $this->assertEquals(array('[[token6~dash~lower]]'), array_keys($unresolvedTokens));
        /** @var Token $unresolvedToken */
        $unresolvedToken = current($unresolvedTokens);
        $this->assertNull($unresolvedToken->getTokenValue());
        $this->assertEquals(array('dash','lower'), array_values($unresolvedToken->getUnresolvedFilters()));

        $withUnresolvedFilters = $tokens->findWithUnresolvedFilters();
        $this->assertEquals(2, count($withUnresolvedFilters));

        /** @var Token $token6 */
        $token6 = current($unresolvedTokens);
        $tokenResolver->registerTokenValue('token6', 'The Value of Token6');
        $token6->resolve($tokenResolver);
        $this->assertTrue($token6->getIsResolved());

        $tokenResolver->unRegisterTokenValue($token6->getTokenName());
        $this->assertFalse($tokenResolver->isTokenValueRegistered($token6->getTokenName()));

        try {
            $tokenResolver->unRegisterTokenValue($token6->getTokenName());
            $this->assertTrue(false);
        } catch (UnknownTokenException $e) {
            $this->assertTrue(true);
        }

        try {
            $tokenResolver->getTokenValue($token6->getTokenName());
            $this->assertTrue(false);
        } catch (UnknownTokenException $e) {
            $this->assertTrue(true);
        }

        $token6->resolve($tokenResolver);
        $this->assertTrue($token6->getIsResolved());
        $this->assertTrue($token6->getIsFiltered());
        $this->assertFalse($token6->hasUnresolvedFilters());

        $unresolvedTokens = $tokens->findUnresolved();
        $this->assertEmpty($unresolvedTokens);

        $tokens->remove($token6);
        $this->assertFalse($tokens->has($token6->getTokenString()));

        unset($result);
        $result = $tokens->findByName('token4');
        $token4 = current($result);
        $this->assertTrue($token4->getIsResolved());
        $this->assertTrue($token4->getIsFiltered());
        $this->assertTrue($token4->hasUnresolvedFilters());
        try {
            $token4->applyFilters(false);
            $this->assertTrue(false);
        } catch (UnknownFilterException $e) {
            $this->assertTrue(true);
        }


        $withUnresolvedFilters = $tokens->findWithUnresolvedFilters();
        $this->assertEquals(1, count($withUnresolvedFilters));

        $tokenCollectionImp = new TokenCollection(array($token6->getTokenString() => $token6));
        $tokens->import($tokenCollectionImp);
        $this->assertTrue($tokens->has($token6->getTokenString()));

        $tokens->remove($token6);

        /** @var Token $tokenWithUnresolvedFilters */
        $tokenWithUnresolvedFilters = current($withUnresolvedFilters);
        $this->assertEquals(array('unknown_filter'), array_values($tokenWithUnresolvedFilters->getUnresolvedFilters()));

        $result = TokenInjector::injectString($jsonData, $tokens, true);

        $this->assertEquals($jsonExpectedData, $result);

        $tokens->clear();
        $this->assertEquals(0, $tokens->getCount());
    }
}