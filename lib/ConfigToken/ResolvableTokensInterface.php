<?php

namespace ConfigToken;


interface ResolvableTokensInterface extends DisposableInterface
{
    /**
     * Append the given resolvable token element to the collection.
     *
     * @param ResolvableTokenInterface $element
     * @return mixed
     */
    public function append(ResolvableTokenInterface $element);
}