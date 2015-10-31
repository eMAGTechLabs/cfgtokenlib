<?php

namespace ConfigToken\Tests\File\Client;


use ConfigToken\File\Client\FileClientFactory;
use ConfigToken\Tests\File\Client\Mocks\TestLocalFileClient;

class FileClientFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFileClientFactory()
    {
        $this->assertFalse(FileClientFactory::isRegisteredFileClient(TestLocalFileClient::getId()));

        FileClientFactory::registerFileClient(TestLocalFileClient::getId(), TestLocalFileClient::getClassName());

        $this->assertTrue(FileClientFactory::isRegisteredFileClient(TestLocalFileClient::getId()));

        $testFileClient = FileClientFactory::makeFileClient(TestLocalFileClient::getId());

        $this->assertInstanceOf(TestLocalFileClient::getClassName(), $testFileClient);

        FileClientFactory::removeFileClient(TestLocalFileClient::getId());

        $this->assertFalse(FileClientFactory::isRegisteredFileClient(TestLocalFileClient::getId()));
    }

    /**
     * @expectedException \ConfigToken\File\Client\Exceptions\FileClientNotRegisteredException
     */
    public function testException1()
    {
        FileClientFactory::removeFileClient(TestLocalFileClient::getId());
    }

    /**
     * @expectedException \ConfigToken\File\Client\Exceptions\FileClientNotRegisteredException
     */
    public function testException2()
    {
        FileClientFactory::makeFileClient(TestLocalFileClient::getId());
    }
}