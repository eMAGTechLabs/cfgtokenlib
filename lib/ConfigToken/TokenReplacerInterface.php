<?php

namespace ConfigToken;


use ConfigToken\TokenResolver\TokenResolverInterface;

interface TokenReplacerInterface
{
    /**
     * Build a token replacer aggregate.
     *
     * @param TokenParserInterface $parser The aggregated token parser.
     * @param TokenResolverInterface $resolver The aggregated token resolver.
     */
    public function __construct(TokenParserInterface $parser, TokenResolverInterface $resolver);

    /**
     * Get the aggregated token parser.
     *
     * @return TokenParserInterface
     */
    public function getTokenParser();

    /**
     * Get the aggregated token resolver.
     *
     * @return TokenResolverInterface
     */
    public function getTokenResolver();

    /**
     * Find and replace tokens in either the given string or array.
     *
     * @param mixed &$data The data where the tokens will be replaced with values from the resolvers.
     * @param boolean|null $ignoreUnknownTokens Override ignore unknown tokens flag in token resolver. Null for default.
     * @param boolean|null $ignoreUnknownFilters Override ignore unknown filters flag in token resolver. Null for default.
     * @throws \UnexpectedValueException
     */
    public function replace(&$data, $ignoreUnknownTokens = null, $ignoreUnknownFilters = null);

}