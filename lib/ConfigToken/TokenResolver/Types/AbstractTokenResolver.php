<?php

namespace ConfigToken\TokenResolver\Types;

use ConfigToken\TokenResolver\TokenResolverInterface;


abstract class AbstractTokenResolver implements TokenResolverInterface
{
    protected $ignoreUnknownTokens = False;

    /**
     * Get the state of the ignore unknown tokens flag.
     *
     * @return boolean
     */
    public function getIgnoreUnknownTokens()
    {
        return $this->ignoreUnknownTokens;
    }

    /**
     * Set the state of the ignore unknown tokens flag.
     *
     * @return boolean
     */
    public function setIgnoreUnknownTokens($value)
    {
        $this->ignoreUnknownTokens = $value;

        return $this;
    }

}