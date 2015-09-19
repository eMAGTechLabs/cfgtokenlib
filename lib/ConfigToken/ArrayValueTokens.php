<?php

namespace ConfigToken;


class ArrayValueTokens implements DisposableInterface
{
    /** @var TokenCollection */
    public $tokens;
    public $value;
    public $valueRef;

    public function release()
    {
        $this->tokens = null;
        $this->value = null;
        $this->valueRef = null;
    }
}