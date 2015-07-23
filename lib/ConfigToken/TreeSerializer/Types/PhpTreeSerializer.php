<?php

namespace ConfigToken\TreeSerializer\Types;


use ConfigToken\TreeSerializer\Exception\TreeSerializerSyntaxException;

class PhpTreeSerializer extends AbstractTreeSerializer
{
    /**
     * @codeCoverageIgnore
     * @return string
     */
    public static function getContentType()
    {
        return 'application/x-php';
    }

    /**
     * @codeCoverageIgnore
     * @return string
     */
    public static function getFileExtension()
    {
        return 'php';
    }

    public static function serialize($data)
    {
        return "<?php\n\n\$data = " . var_export($data, true) . ';';
    }

    /**
     * Check syntax of the given PHP code string.
     * @param string $code The PHP code for which to perform syntax checking.
     * @return array|boolean Array of syntax errors or true/false to indicate success/unknown failure.
     * @codeCoverageIgnore
     */
    protected static function syntaxCheck($code){
        error_reporting(E_ALL);
        $braces=0;
        $inString=0;
        foreach (token_get_all('<?php ' . $code) as $token) {
            if (is_array($token)) {
                switch ($token[0]) {
                    case T_CURLY_OPEN:
                    case T_DOLLAR_OPEN_CURLY_BRACES:
                    case T_START_HEREDOC: ++$inString; break;
                    case T_END_HEREDOC:   --$inString; break;
                }
            } else if ($inString & 1) {
                switch ($token) {
                    case '`': case '\'':
                    case '"': --$inString; break;
                }
            } else {
                switch ($token) {
                    case '`': case '\'':
                    case '"': ++$inString; break;
                    case '{': ++$braces; break;
                    case '}':
                        if ($inString) {
                            --$inString;
                        } else {
                            --$braces;
                            if ($braces < 0) break 2;
                        }
                        break;
                }
            }
        }
        $inString = @ini_set('log_errors', false);
        $token = @ini_set('display_errors', true);
        ob_start();
        $braces || $code = "if(0){" . $code . "\n}";
        if (class_exists('ParseError')) { // PHP 7
            try {
                $evalResult = eval($code);
            } /** @noinspection PhpUndefinedClassInspection */ catch(\ParseError $e) {
                return array($e->getMessage(), $e->getCode());
            }
        } else {
            $evalResult = eval($code);
        }
		if ($evalResult === false) {
            if ($braces) {
                $braces = PHP_INT_MAX;
            } else {
                false !== strpos($code,"\r") && $code = strtr(str_replace("\r\n", "\n", $code), "\r", "\n");
                $braces = substr_count($code,"\n");
            }
            $code = ob_get_clean();
            $code = strip_tags($code);
            // todo: fix regex
            if (@preg_match("'syntax error, (.+) in .+ on line (.+)$'s", $code, $code)) {
                $code[2] = (int) $code[2];
                $code = $code[2] <= $braces
                    ? array($code[1], $code[2])
                    : array('unexpected $end' . substr($code[1], 14), $braces);
            } else $code = array('syntax error', 0);
        } else {
            ob_end_clean();
            $code = false;
        }
		@ini_set('display_errors', $token);
		@ini_set('log_errors', $inString);
		return $code;
	}

    public static function deserialize($string)
    {
        $data = array();
        $code = static::syntaxCheck(str_replace('<?php', '', $string));
        if ($code !== false) {
            throw new TreeSerializerSyntaxException(sprintf('Unable to deserialize PHP tree: %s', implode(', ', $code)));
        }
        if (class_exists('ParseError')) { // PHP 7
            try {
                eval(str_replace('<?php', '', $string));
            } /** @noinspection PhpUndefinedClassInspection */ catch(\ParseError $e) {
                throw new TreeSerializerSyntaxException($e->getMessage());
            }
        } else {
            eval(str_replace('<?php', '', $string));
        }
        return $data;
    }
}