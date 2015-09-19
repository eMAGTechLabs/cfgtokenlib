<?php

namespace ConfigToken;


use ConfigToken\TokenResolver\TokenResolverInterface;

/**
 * Aggregates a token parser with a token resolver to replace tokens in strings or associative arrays.
 *
 * @package ConfigToken
 */
class TokenReplacer implements TokenReplacerInterface
{
    /** @var TokenParserInterface */
    protected $tokenParser;

    /** @var TokenResolverInterface */
    protected $tokenResolver;

    /**
     * Build a token replacer aggregate.
     *
     * @param TokenParserInterface $parser The aggregated token parser.
     * @param TokenResolverInterface $resolver The aggregated token resolver.
     */
    public function __construct(TokenParserInterface $parser, TokenResolverInterface $resolver)
    {
        $this->tokenParser = $parser;
        $this->tokenResolver = $resolver;
    }

    /**
     * Get the aggregated token parser.
     *
     * @return TokenParserInterface
     */
    public function getTokenParser()
    {
        return $this->tokenParser;
    }

    /**
     * Get the aggregated token resolver.
     *
     * @return TokenResolverInterface
     */
    public function getTokenResolver()
    {
        return $this->tokenResolver;
    }

    /**
     * Find and replace tokens in the given associative array.
     *
     * @param array $array
     * @param boolean|null $ignoreUnknownTokens Override ignore unknown tokens flag in token resolver. Null for default.
     * @param boolean|null $ignoreUnknownFilters Override ignore unknown filters flag in token resolver. Null for default.
     */
    public function replaceTokensInArray(array &$array, $ignoreUnknownTokens = null, $ignoreUnknownFilters = null)
    {
    }

    /**
     * Find and replace tokens in the given string.
     *
     * @param $string
     * @param boolean|null $ignoreUnknownTokens Override ignore unknown tokens flag in token resolver. Null for default.
     * @param boolean|null $ignoreUnknownFilters Override ignore unknown filters flag in token resolver. Null for default.
     */
    public function replaceTokensInString(&$string, $ignoreUnknownTokens = null, $ignoreUnknownFilters = null)
    {
    }

    /**
     * Find and replace tokens in either the given string or array.
     *
     * @param mixed &$data The data where the tokens will be replaced with values from the resolvers.
     * @param boolean|null $ignoreUnknownTokens Override ignore unknown tokens flag in token resolver. Null for default.
     * @param boolean|null $ignoreUnknownFilters Override ignore unknown filters flag in token resolver. Null for default.
     * @throws \UnexpectedValueException
     */
    public function replace(&$data, $ignoreUnknownTokens = null, $ignoreUnknownFilters = null)
    {
        $tokens = $this->tokenParser->parse($data);

        if (is_array($data)) {
            $this->replaceTokensInArray($data, $ignoreUnknownTokens, $ignoreUnknownFilters);
            return;
        }
        if (is_string($data)) {
            $this->replaceTokensInString($data, $ignoreUnknownTokens, $ignoreUnknownFilters);
            return;
        }
        throw new \UnexpectedValueException(
            sprintf(
                'Unable to replace tokens in variables of type "%s"',
                gettype($data)
            )
        )
    }
}