<?php

namespace ConfigToken\Tests\File\Client;


use ConfigToken\File\Client\FileClientFactory;
use ConfigToken\File\Client\Types\LocalFileClient;
use ConfigToken\File\ConnectionSettings\Types\LocalFileClientConnectionSettings;
use ConfigToken\Tests\File\Client\Mocks\TestLocalFileClient;
use ConfigToken\Tests\File\Client\Mocks\TestLocalFileClientConnectionSettings;

class LocalFileClientTest extends \PHPUnit_Framework_TestCase
{
    public function testLocalFileClientConnectionSettings()
    {
        $cs = new TestLocalFileClientConnectionSettings();
        $this->assertEquals(DIRECTORY_SEPARATOR, $cs->getDirectorySeparator());
        $this->assertEquals(getcwd(), $cs->getRootPath());
        $this->assertTrue($cs->hasRootPath());
        $cs->setRootPath('C:/Test');
        $this->assertEquals('C:\\Test', $cs->getRootPath('\\'));
        $cs->setDirectorySeparator('\\');
        $this->assertEquals('\\', $cs->getDirectorySeparator());
    }

    /**
     * @expectedException \ConfigToken\Options\Exceptions\OptionValueException
     */
    public function testLocalFileClientConnectionSettingsException()
    {
        $cs = new TestLocalFileClientConnectionSettings();
        $cs->setDirectorySeparator('.');
    }

    public function testInit()
    {
        TestLocalFileClient::$overrideConnectionSettings = false;

        $lfc = new TestLocalFileClient();
        $this->assertFalse($lfc->isConnected());
        $this->assertNull($lfc->getConnectionSettings());
        $this->assertTrue($lfc->connect());
        $this->assertNotNull($lfc->getConnectionSettings());
        $this->assertEquals(getcwd(), $lfc->getConnectionSettings()->getRootPath());

        $expected = str_replace('\\', DIRECTORY_SEPARATOR, getcwd());
        $output = $lfc->getPath(false, null);
        $this->assertEquals($expected, $output);

        if (DIRECTORY_SEPARATOR == '\\') {
            $expected = str_replace('\\', '/', getcwd());
            $output = $lfc->getPath(false, '/');
            $this->assertEquals($expected, $output);
        } else {
            $expected = str_replace('/', '\\', getcwd());
            $output = $lfc->getPath(false, '\\');
            $this->assertEquals($expected, $output);
        }

        $path = '/tests/ConfigToken/Tests/File/Client/Data/';
        $lfc->setPath($path);

        $expected = $lfc->getConnectionSettings()->getRootPath('/') . substr($path, 0, -1);
        $this->assertEquals($expected, $lfc->getPath(false, '/'));

        $lfc->setPath($expected, false);
        $output = $lfc->getPath(true, '/');
        $expected = trim($path, '/');

        $this->assertEquals($expected, $output);

        $lfc->disconnect();
        $this->assertFalse($lfc->isConnected());
        $this->assertNull($lfc->getConnectionSettings());
    }

    public function testReadFile()
    {
        /** @var LocalFileClient $fileClient */
        $fileClient = FileClientFactory::makeFileClient(LocalFileClient::getId());
        $connectionSettings = new LocalFileClientConnectionSettings();
        $rootPath = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Data';
        $connectionSettings->setRootPath($rootPath);
        $fileClient->connect($connectionSettings);
        $fileName = 'root-file.txt';
        $fullFileName = $rootPath . DIRECTORY_SEPARATOR . $fileName;
        $expected = file_get_contents($fullFileName);
        $actual = $fileClient->readFile($fileName);
        $this->assertEquals($expected, $actual);

        $actual = $fileClient->readFile($fullFileName, false);
        $this->assertEquals($expected, $actual);
    }

    /**
     * @expectedException \ConfigToken\File\Client\Exceptions\NotConnectedException
     */
    public function testNotConnectedException()
    {
        $fileClient = FileClientFactory::makeFileClient(LocalFileClient::getId());
        $fileClient->readFile('something');
    }
}