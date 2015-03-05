<?php

namespace ConfigToken;

use ConfigToken\TokenResolver\Exception\UnknownTokenSourceException;

/**
 * Provides a method to inject the resolved token values collection back into the original string.
 */
class TokenInjector
{
    /**
     * Inject the resolved token values in the given string.
     *
     * @param string $string The string where to inject the resolved token values.
     * @param TokenCollection $tokens The tokens.
     * @param boolean $checkHash If true, the hash of the string must match the source hash of the tokens.
     * @throws UnknownTokenSourceException
     * @return string
     */
    public static function injectString($string, TokenCollection $tokens, $checkHash = False) {
        if ($checkHash) {
            $hash = md5($string);
            if ($tokens->hasSourceHash() && ($tokens->getSourceHash() !== $hash)) {
                throw new UnknownTokenSourceException('Unable to inject tokens.');
            }
        }

        $offsets = array();
        $allTokens = $tokens->getArray();
        foreach ($allTokens as $tokenString => $token) {
            $tokenOffsets = $token->getOffsets();
            foreach ($tokenOffsets as $offset) {
                $offsets[$offset] = $tokenString;
            }
        }
        ksort($offsets);

        $blocks = array();
        $lastOffset = 0;
        $offsetDelta = 0;
        /** @var Token[] $injected */
        $injected = array();
        foreach ($offsets as $offset => $tokenString) {
            /** @var Token $token */
            $token = $allTokens[$tokenString];
            if (!$token->getIsInjected()) {
                $token->adjustOffset($offset, $offsetDelta);
                if ($token->getIsResolved()) {
                    $tokenValue = $token->getTokenValue();
                    $blocks[] = substr($string, $lastOffset, $offset - $lastOffset);
                    $blocks[] = $tokenValue;
                    $lastOffset = $offset + strlen($tokenString);
                    $offsetDelta += strlen($tokenValue) - strlen($tokenString);
                    $injected[$tokenString] = $token;
                }
            }
        }

        if ($lastOffset > 0) {
            $blocks[] = substr($string, $lastOffset);
        }

        foreach ($injected as $token) {
            $token->setIsInjected(true);
        }

        if (empty($blocks)) {
            $newString = $string;
        } else {
            $newString = implode('', $blocks);
        }

        unset($string);
        unset($blocks);

        if ($checkHash) {
            $tokens->setSourceHash(md5($newString));
        }

        return $newString;
    }
}