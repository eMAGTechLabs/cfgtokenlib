<?php

namespace ConfigToken;


class StringTokenParser extends AbstractTokenParser
{
    /**
     * Parse the given string and extract all tokens.
     *
     * @param string $string The string to parse.
     * @return TokenCollection
     */
    protected function parseString($string)
    {
        $result = new TokenCollection();

        $pregResults = array();
        $tokenRegex = $this->getTokenRegex();
        $pregResult = preg_match_all($tokenRegex, $string, $pregResults, PREG_OFFSET_CAPTURE);
        if (!$pregResult) {
            return $result;
        }
        unset($pregResult);

        $result->setSourceHash(md5($string));

        $filterDelimiter = $this->getFilterDelimiter();
        foreach ($pregResults[1] as $key => $pregResult) {
            $tokenString = $pregResults[0][$key][0];
            if ($result->has($tokenString)) {
                $result->get($tokenString)->addOffset($pregResults[0][$key][1]);
                continue;
            }
            $filters = explode($filterDelimiter, $pregResult[0]);
            $tokenName = $filters[0];
            unset($filters[0]);

            $token = new Token(
                $pregResults[0][$key][1],
                $tokenString,
                $tokenName,
                null, // $tokenValue
                $filters
            );

            $result->add($token);
        }

        return $result;
    }

    public function parse(&$data)
    {
        $result = $this->parseString($data);
        return $result;
    }
}