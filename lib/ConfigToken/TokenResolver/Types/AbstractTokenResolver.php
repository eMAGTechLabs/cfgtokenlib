<?php

namespace ConfigToken\TokenResolver\Types;

use ConfigToken\TokenResolver\TokenResolverInterface;


abstract class AbstractTokenResolver implements TokenResolverInterface
{
    protected $ignoreUnknownTokens = True;

    /**
     * Get the name of the implementation class.
     *
     * @return string
     */
    public static function getClassName()
    {
        return get_called_class();
    }

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
     * @param boolean $value
     * @return boolean
     */
    public function setIgnoreUnknownTokens($value)
    {
        $this->ignoreUnknownTokens = $value;

        return $this;
    }

}