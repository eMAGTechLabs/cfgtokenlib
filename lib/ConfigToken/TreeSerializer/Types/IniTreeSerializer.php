<?php

namespace ConfigToken\TreeSerializer\Types;

use ConfigToken\TreeSerializer\Exception\TreeSerializerSyntaxException;


class IniTreeSerializer extends AbstractTreeSerializer
{
    /**
     * @codeCoverageIgnore
     * @return string
     */
    public static function getContentType()
    {
        return 'zz-application/zz-winassoc-ini';
    }

    /**
     * @codeCoverageIgnore
     * @return string
     */
    public static function getFileExtension()
    {
        return 'ini';
    }

    protected static function recursiveSerialize($data, $namespace = null)
    {
        $result = array();
        $resultValues = array();
        foreach ($data as $key => $value) {
            if (!is_array($value)) {
                $resultValues[] = sprintf('%s=%s', $key, $value);
            }
        }
        if (!empty($namespace)) {
            $result[] = sprintf('[%s]', implode(':', $namespace));
        }
        if (!empty($resultValues)) {
            $result[] = implode("\n", $resultValues);
        } else if (!empty($namespace)) {
            $result[] = '';
        }
        unset($resultValues);
        foreach ($data as $key => $value) {
            if (!is_array($value)) {
                continue;
            }
            if (!isset($namespace)) {
                $namespace = array();
            }
            $namespace[] = $key;
            if (count($result) > 0) {
                $result[] = '';
            }
            $result[] = static::recursiveSerialize($value, $namespace);
            array_pop($namespace);
        }
        return implode("\n", $result);
    }

    /**
     * @covers IniTreeSerializer::recursiveSerialize
     * @param $data
     * @return string
     */
    public static function serialize($data)
    {
        return static::recursiveSerialize($data);
    }

    public static function deserialize($string)
    {
        $result = array();
        try {
            $ini = parse_ini_string($string, true, INI_SCANNER_RAW);
        } catch (\Exception $e) {
            throw new TreeSerializerSyntaxException(sprintf('Unable to parse INI string: %s.', $e->getMessage()));
        }
        if (($ini === false) || (empty($ini) && (strlen(trim($string)) > 0))) {
            throw new TreeSerializerSyntaxException('Unable to parse INI string.');
        }
        foreach ($ini as $key => $value) {
            if (is_array($value)) {
                $namespace = explode(':', $key);
                $resultPtr = &$result;
                foreach ($namespace as $namespaceLevel) {
                    if (!isset($resultPtr[$namespaceLevel])) {
                        $resultPtr[$namespaceLevel] = array();
                    }
                    $resultPtr = &$resultPtr[$namespaceLevel];
                }
                if (!is_array($resultPtr)) {
                    throw new TreeSerializerSyntaxException(
                        sprintf(
                            'Namespace [%s] overlaps with value.',
                            $key
                        )
                    );
                }
                foreach ($value as $nsKey => $nsValue) {
                    $resultPtr[$nsKey] = $nsValue;
                }
                unset($resultPtr);
                continue;
            }
            $result[$key] = $value;
        }
        return $result;
    }
}