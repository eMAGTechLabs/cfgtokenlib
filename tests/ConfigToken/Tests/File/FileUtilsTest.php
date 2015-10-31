<?php

namespace ConfigToken\Tests\File;


use ConfigToken\Utils\FileUtils;

class FileUtilsTest extends \PHPUnit_Framework_TestCase
{
    public function testReplaceDirectorySeparator()
    {
        $input = 'C:\\Program Files\\Test\\file.txt';
        $output = FileUtils::replaceDirectorySeparator($input, '/');
        $expected = 'C:/Program Files/Test/file.txt';
        $this->assertEquals($expected, $output);

        $input = 'C:\\Program Files\\Test\\file.txt';
        $output = FileUtils::replaceDirectorySeparator($input, null);
        $expected = 'C:/Program Files/Test/file.txt';
        if (DIRECTORY_SEPARATOR == '\\') {
            $expected = str_replace('/', '\\', $expected);
        }
        $this->assertEquals($expected, $output);
    }

    public function testNormalizePath()
    {
        $input = 'C:\\.\\\\Program Files\\..\\Program Files\\.\\Test\\..\\.\\..\\';
        $output = FileUtils::normalizePath($input, false, '/');
        $expected = 'C:/';
        $this->assertEquals($expected, $output);

        $input = 'C:\\Temp\\..\\..\\..\\';
        $output = FileUtils::normalizePath($input, false, '/');
        $expected = 'C:/';
        $this->assertEquals($expected, $output);

        $input = '\\Temp\\..\\..\\..\\';
        $output = FileUtils::normalizePath($input, false, '/');
        $expected = '/';
        $this->assertEquals($expected, $output);

        $input = '\\Temp\\..\\..\\..';
        $output = FileUtils::normalizePath($input, false, '/');
        $expected = '/';
        $this->assertEquals($expected, $output);

        $input = '/../../../';
        $output = FileUtils::normalizePath($input, false, '/');
        $expected = '/';
        $this->assertEquals($expected, $output);

        $input = '/../../..';
        $output = FileUtils::normalizePath($input, false, '/');
        $expected = '/';
        $this->assertEquals($expected, $output);

        $input = '../../../';
        $output = FileUtils::normalizePath($input, false, '\\');
        $expected = '..\\..\\..\\';
        $this->assertEquals($expected, $output);

        $input = 'C:\\Temp\\..\\..\\..\\';
        $output = FileUtils::normalizePath($input, true, '/');
        $expected = 'C:/';
        $this->assertEquals($expected, $output);

        $input = '\\Temp\\..\\..\\..\\';
        $output = FileUtils::normalizePath($input, true, '/');
        $expected = '.';
        $this->assertEquals($expected, $output);
    }

    public function testJoinPaths()
    {
        $input = array('C:', 'Program Files', 'Test', 'file.txt');
        $output = FileUtils::joinPaths($input, '/');
        $expected = 'C:/Program Files/Test/file.txt';
        $this->assertEquals($expected, $output);

        $input = array('C:', '/Program Files/', 'Test\\', '/file.txt');
        $output = FileUtils::joinPaths($input);
        $expected = implode(DIRECTORY_SEPARATOR, array('C:', 'Program Files', 'Test', 'file.txt'));
        $this->assertEquals($expected, $output);

        $input = array('', DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, '', '');
        $output = FileUtils::joinPaths($input);
        $expected = DIRECTORY_SEPARATOR;
        $this->assertEquals($expected, $output);

        $input = array('', '', '');
        $output = FileUtils::joinPaths($input);
        $expected = '';
        $this->assertEquals($expected, $output);
    }

    public function testExtractPath()
    {
        $input = '/var/www/test';
        $output = FileUtils::extractPath($input);
        $expected = implode(DIRECTORY_SEPARATOR, array('', 'var', 'www'));
        $this->assertEquals($expected, $output);

        $input = '/var/www/test/';
        $output = FileUtils::extractPath($input);
        $expected = implode(DIRECTORY_SEPARATOR, array('', 'var', 'www', 'test'));
        $this->assertEquals($expected, $output);

        $input = '/var/www/test/.';
        $output = FileUtils::extractPath($input);
        $expected = implode(DIRECTORY_SEPARATOR, array('', 'var', 'www', 'test', '.'));
        $this->assertEquals($expected, $output);

        $input = '/var/www/test/../';
        $output = FileUtils::extractPath($input);
        $expected = implode(DIRECTORY_SEPARATOR, array('', 'var', 'www', 'test', '..'));
        $this->assertEquals($expected, $output);
    }

    public function testExtractFile()
    {
        $input = '/var/www/test';
        $output = FileUtils::extractFile($input);
        $expected = 'test';
        $this->assertEquals($expected, $output);

        $input = '/var/www/test/';
        $output = FileUtils::extractFile($input);
        $expected = '';
        $this->assertEquals($expected, $output);

        $input = '/var/www/test/.';
        $output = FileUtils::extractFile($input);
        $expected = '';
        $this->assertEquals($expected, $output);

        $input = '/var/www/test/../';
        $output = FileUtils::extractFile($input);
        $expected = '';
        $this->assertEquals($expected, $output);
    }

    public function testMakeRelative()
    {
        $path = implode(DIRECTORY_SEPARATOR, array('', 'var', 'www', 'folder', '.', ''));
        $rootPath = implode(DIRECTORY_SEPARATOR, array('', 'var', 'www', 'test', '..', ''));
        $expected = 'folder' . DIRECTORY_SEPARATOR;
        $output = FileUtils::makeRelative($path, $rootPath);
        $this->assertEquals($expected, $output);

        $output = FileUtils::makeRelative($rootPath, $path);
        $this->assertFalse($output);
    }
}