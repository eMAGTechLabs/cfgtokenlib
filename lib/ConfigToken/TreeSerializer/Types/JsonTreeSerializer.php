<?php

namespace ConfigToken\TreeSerializer\Types;

use ConfigToken\TreeSerializer\Exception\TreeSerializerSyntaxException;


class JsonTreeSerializer extends AbstractTreeSerializer
{
    /**
     * @codeCoverageIgnore
     * @return string
     */
    public static function getContentType()
    {
        return 'application/json';
    }

    /**
     * @codeCoverageIgnore
     * @return string
     */
    public static function getFileExtension()
    {
        return 'json';
    }

    public static function serialize($data)
    {
        return json_encode($data, JSON_PRETTY_PRINT || JSON_UNESCAPED_SLASHES);
    }

    public static function deserialize($string)
    {
        static $errors = array(
            JSON_ERROR_DEPTH           => 'Maximum stack depth exceeded',
            JSON_ERROR_STATE_MISMATCH  => 'Underflow or the modes mismatch',
            JSON_ERROR_CTRL_CHAR       => 'Unexpected control character found',
            JSON_ERROR_SYNTAX          => 'Syntax error, malformed JSON',
            JSON_ERROR_UTF8            => 'Malformed UTF-8 characters, possibly incorrectly encoded'
        );

        $data = json_decode($string, true);

        $errorCode = json_last_error();
        if ((!isset($data)) && ($errorCode != JSON_ERROR_NONE)) {
            $errorMsg = array_key_exists($errorCode, $errors) ? $errors[$errorCode] : "Unknown error ({$errorCode})";
            throw new TreeSerializerSyntaxException(
                sprintf(
                    'Unable to deserialize json tree: %s',
                    $errorMsg
                )
            );
        }
        return $data;
    }
}