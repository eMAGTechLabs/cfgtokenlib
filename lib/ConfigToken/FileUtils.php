<?php

namespace ConfigToken;


use ConfigToken\TreeSerializer\Types\IniTreeSerializer;
use ConfigToken\TreeSerializer\Types\JsonTreeSerializer;
use ConfigToken\TreeSerializer\Types\PhpTreeSerializer;
use ConfigToken\TreeSerializer\Types\XmlTreeSerializer;
use ConfigToken\TreeSerializer\Types\YmlTreeSerializer;

class FileUtils
{

    /**
     * Normalizes the given path containing '.' and '..' while optionally restricting navigation above root folder.
     * WARNING: The directory separator will be replaced.
     *
     * @param string $path The file path to be normalized.
     * @param boolean|false $restrictToCurrent Above root restriction flag.
     * @param string|null $directorySeparator The directory separator or null for system default.
     * @return string
     */
    public static function normalizePath($path, $restrictToCurrent = false, $directorySeparator = null)
    {
        if ($restrictToCurrent) {
            $path = ltrim($path, '\/');
        }
        $parts = array();
        $path = str_replace('\\', '/', $path);
        $path = preg_replace('/\/+/', '/', $path);
        $segments = explode('/', $path);
        foreach ($segments as $segment) {
            if ($segment == '.') {
                continue;
            }
            $test = array_pop($parts);
            if (is_null($test)) {
                $parts[] = $segment;
                continue;
            }
            if ($segment == '..') {
                $testIsBack = $test == '..';
                if ($testIsBack) {
                    $parts[] = $test;
                }
                if ($testIsBack || empty($test)) {
                    $parts[] = $segment;
                }
                continue;
            }
            $parts[] = $test;
            $parts[] = $segment;
        }
        if ($restrictToCurrent) {
            reset($parts);
            while ((!empty($parts)) && (current($parts) == '..')) {
                array_shift($parts);
            }
        }
        return implode(isset($directorySeparator) ? $directorySeparator : DIRECTORY_SEPARATOR, $parts);
    }

    /**
     * Return the MIME content type from the given file name.
     *
     * @param string $fileName The file name.
     * @param string $default The default MIME type to return for unknown extensions
     * @return string
     */
    public static function getContentTypeFromFileName($fileName, $default = 'application/octet-stream')
    {
        if (empty($fileName)) {
            return $default;
        }
        $ext = pathinfo($fileName, PATHINFO_EXTENSION);
        switch ($ext) {
            case 'ini':
                return IniTreeSerializer::getContentType();
            case 'json':
                return JsonTreeSerializer::getContentType();
            case 'php':
                return PhpTreeSerializer::getContentType();
            case 'xml':
                return XmlTreeSerializer::getContentType();
            case 'yml':
                return YmlTreeSerializer::getContentType();
            default:
                return $default;
        }
    }
}