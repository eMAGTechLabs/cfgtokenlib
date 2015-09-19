<?php

namespace ConfigToken;


use ConfigToken\TokenResolver\TokenResolverInterface;

interface ResolvableTokenInterface extends DisposableInterface
{

    /**
     * Attempt to resolve the values for all tokens in the list.
     *
     * @param TokenResolverInterface $tokenResolver
     * @param boolean|null $ignoreUnknownTokens Null to use token resolver option.
     * @param boolean|null $ignoreUnknownFilters Null to use collection option.
     * @return $this
     */
    public function resolve(TokenResolverInterface $tokenResolver, $ignoreUnknownTokens = null,
                            $ignoreUnknownFilters = null);
}