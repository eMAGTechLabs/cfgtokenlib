<?php

namespace ConfigToken\Utils;


class FileUtils
{
    public static $DIRECTORY_SEPARATORS = array('/', '\\');

    /**
     * Replace the directory separators within the given path.
     *
     * @param string $path The path where to replace the directory separators.
     * @param string|null $directorySeparator Defaults to DIRECTORY_SEPARATOR
     * @return mixed
     */
    public static function replaceDirectorySeparator($path, $directorySeparator=null)
    {
        if (!isset($directorySeparator)) {
            $directorySeparator = DIRECTORY_SEPARATOR;
        }
        $path = str_replace('\\', '/', $path);
        $path = preg_replace('/\/+/', '/', $path);

        if ($directorySeparator == '/') {
            return $path;
        }
        return str_replace('/', $directorySeparator, $path);
    }

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
        $path = static::replaceDirectorySeparator($path, '/');
        $segments = explode('/', $path);
        $absoluteWin = !empty($segments) && (strpos(current($segments), ':') !== false);
        $absoluteLinux = !empty($segments) && (current($segments) === '');
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
                if (($absoluteWin || $absoluteLinux) && (!$testIsBack) && empty($parts)) {
                    $parts[] = $test;
                    $lastSegment = array_pop($segments);
                    if (($lastSegment == '') || $absoluteLinux) {
                        $parts[] = '';
                    }
                    break;
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
        $result = implode(isset($directorySeparator) ? $directorySeparator : DIRECTORY_SEPARATOR, $parts);
        if ($result == '') {
            $result = '.';
        }
        return $result;
    }

    public static function joinPaths(array $paths, $directorySeparator=null)
    {
        if (!isset($directorySeparator)) {
            $directorySeparator = DIRECTORY_SEPARATOR;
        }
        while (!empty($paths)) {
            end($paths);
            if (!empty(current($paths))) {
                break;
            }
            array_pop($paths);
        }
        $result = static::replaceDirectorySeparator(implode($directorySeparator, $paths), $directorySeparator);

        if (strlen($result) > 1) {
            $result = rtrim($result, $directorySeparator);
        }

        return $result;
    }

    protected static function extract($fileName, $directorySeparator=null, $path=true)
    {
        if (!isset($directorySeparator)) {
            $directorySeparator = DIRECTORY_SEPARATOR;
        }
        $fileName = static::replaceDirectorySeparator($fileName, $directorySeparator);
        $parts = explode($directorySeparator, $fileName);
        if (!empty($parts)) {
            $lastPart = array_pop($parts);
            if (in_array($lastPart, array('.', '..'))) {
                if ($path) {
                    return $fileName;
                }
            } else if (!$path) {
                return $lastPart;
            }
        }
        return $path ? implode($directorySeparator, $parts) : '';
    }

    public static function extractPath($fileName, $directorySeparator=null)
    {
        return static::extract($fileName, $directorySeparator, true);
    }

    public static function extractFile($fileName, $directorySeparator=null)
    {
        return static::extract($fileName, $directorySeparator, false);
    }

    public static function makeRelative($path, $rootPath, $directorySeparator=null)
    {
        if (!isset($directorySeparator)) {
            $directorySeparator = DIRECTORY_SEPARATOR;
        }
        $path = static::normalizePath($path, true, $directorySeparator);
        $pathLen = strlen($path);
        $rootPath = static::normalizePath($rootPath, true, $directorySeparator);;
        $rootPathLen = strlen($rootPath);
        if (($pathLen < $rootPathLen) || (substr($path, 0, $rootPathLen) !== $rootPath)) {
            return false;
        }
        $path = ltrim(substr($path, $rootPathLen), $directorySeparator);
        return $path;
    }

    public static function getContents($fileName)
    {
        return file_get_contents($fileName);
    }
}