<?php

namespace ConfigToken;


interface TokenParserInterface extends OptionsAggregateInterface
{
    /**
     * Parses the given data and returns a list of resolvable tokens.
     *
     * @param mixed &$data The data to be parsed.
     * @return ResolvableTokensInterface
     */
    public function parse(&$data);
}