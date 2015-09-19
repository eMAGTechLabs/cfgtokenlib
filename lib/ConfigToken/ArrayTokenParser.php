<?php

namespace ConfigToken;


class ArrayTokenParser extends StringTokenParser
{
    protected function parseArrayRecursive(array &$array, ResolvableTokensInterface $result)
    {
        foreach ($array as $key => &$value) {
            $t = null;
            if (gettype($key) == 'string') {
                $tokens = $this->parseString($key);
                if (!$tokens->isEmpty()) {
                    $t = new ArrayKeyTokens();
                    $t->key = $key;
                    $t->tokens = $tokens;
                    $t->keyRef = &$array;
                }
            }
            $valueType = gettype($value);
            if ($valueType == 'string') {
                $tokens = $this->parseString($value);
                if (!$tokens->isEmpty()) {
                    $t = new ArrayValueTokens();
                    $t->value = $value;
                    $t->tokens = $tokens;
                    $t->valueRef = &$value;
                }
            }
            $result->append($t);
            if ($valueType == 'array') {
                $this->parseArrayRecursive($value, $result);
            }
        }
        unset($value);
    }

    public function parse(&$data)
    {
        $result = new ArrayTokensCollection();
        $this->parseArrayRecursive($array, $result);
        return $result;
    }
}