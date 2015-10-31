<?php

namespace ConfigToken\File\Client\Types;


use ConfigToken\File\Client\Exceptions\CredentialsManagerNotSetException;
use ConfigToken\File\ConnectionSettings\ConnectionSettingsInterface;
use ConfigToken\File\ConnectionSettings\Types\HttpFileClientConnectionSettings;
use ConfigToken\Utils\CredentialsManagerInterface;
use ConfigToken\Utils\CurlRequest;
use ConfigToken\Utils\HttpRequestInterface;

class HttpFileClient extends LocalFileClient
{
    /** @var CredentialsManagerInterface */
    protected $credentialsManager;
    /** @var string */
    protected $userAgent = 'liutec/cfgtokenlib';

    /**
     * Get the unique id of the implementation.
     * @return string
     */
    public static function getId()
    {
        return 'url';
    }

    /**
     * Return a new instance of the connection settings implementation class.
     *
     * @param ConnectionSettingsInterface|null $connectionSettings
     * @return HttpFileClientConnectionSettings
     */
    public static function makeConnectionSettings(ConnectionSettingsInterface $connectionSettings = null)
    {
        return new HttpFileClientConnectionSettings($connectionSettings);
    }

    /**
     * Check if the credentials manager was set.
     *
     * @return boolean
     */
    public function hasCredentialsManager()
    {
        return isset($this->credentialsManager);
    }

    /**
     * Get the credentials manager.
     *
     * @throws CredentialsManagerNotSetException
     * @return CredentialsManagerInterface|null
     */
    public function getCredentialsManager()
    {
        if (!$this->hasCredentialsManager()) {
            return null;
        }
        return $this->credentialsManager;
    }

    /**
     * Set the credentials manager.
     *
     * @param CredentialsManagerInterface $value The new value.
     * @return $this
     */
    public function setCredentialsManager(CredentialsManagerInterface $value)
    {
        $this->credentialsManager = $value;
        return $this;
    }

    /**
     * Factory method for HTTP requests.
     *
     * @param string $url
     * @throws CredentialsManagerNotSetException
     * @return HttpRequestInterface
     */
    protected static function makeHttpRequest($url)
    {
        return new CurlRequest($url);
    }

    protected static function applyAuthTypeToRequest(HttpRequestInterface $request,
                                                     HttpFileClientConnectionSettings $connectionSettings,
                                                     CredentialsManagerInterface $credentialsManager = null)
    {
        switch ($connectionSettings->getAuthType()) {
            case HttpFileClientConnectionSettings::AUTH_BASIC:
                if (!isset($credentialsManager)) {
                    throw new CredentialsManagerNotSetException();
                }
                $url = $request->getUrl();
                $username = $credentialsManager->getCredential($url, HttpFileClientConnectionSettings::USER);
                $password = $credentialsManager->getCredential($url, HttpFileClientConnectionSettings::PASSWORD);
                $request->setOption(CURLOPT_USERPWD, $username . ':' . $password);
                break;
            default:
                break;
        }
    }

    protected static function applyRequestMethodToRequest(HttpRequestInterface $request,
                                                          HttpFileClientConnectionSettings $connectionSettings,
                                                          $fileName)
    {
        switch ($connectionSettings->getRequestMethod()) {
            case HttpFileClientConnectionSettings::METHOD_GET:
                $url = str_replace(
                    sprintf('<%s>', $connectionSettings->getFieldName()),
                    urlencode($fileName),
                    $request->getUrl()
                );
                $request->setUrl($url);
                break;
            case HttpFileClientConnectionSettings::METHOD_POST:
                $postFields = urlencode(sprintf('%s=%s', $connectionSettings->getFieldName(), $fileName));
                $request->setOption(CURLOPT_POST, 1);
                $request->setOption(CURLOPT_POSTFIELDS, $postFields);
                break;
            default:
                break;
        }
    }

    /**
     * Override to implement other methods of fetching file contents.
     *
     * @param $fileName
     * @throws CredentialsManagerNotSetException
     * @return string
     */
    protected function getContents($fileName)
    {
        /** @var HttpFileClientConnectionSettings $connectionSettings */
        $connectionSettings = $this->getConnectionSettingsCheck();
        $url = $connectionSettings->getUrl();

        $request = static::makeHttpRequest($url);
        static::applyAuthTypeToRequest($request, $connectionSettings, $this->getCredentialsManager());
        static::applyRequestMethodToRequest($request, $connectionSettings, $fileName);
        $request->setOption(CURLOPT_USERAGENT, $this->userAgent);
        $request->setOption(CURLOPT_RETURNTRANSFER, 1);

        $response = $request->execute();
        return $response;
    }
}