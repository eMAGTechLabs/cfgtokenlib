<?php

namespace ConfigToken\Utils;

/**
 * CurlRequest wrapper.
 */
class CurlRequest implements HttpRequestInterface
{
    private $handle = null;
    private $url;

    public function __construct($url)
    {
        $this->handle = curl_init();
        $this->setUrl($url);
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function setUrl($url)
    {
        $this->url = $url;
        return $this->setOption(CURLOPT_URL, $url);
    }

    public function setOption($name, $value)
    {
        curl_setopt($this->handle, $name, $value);
        return $this;
    }

    public function execute()
    {
        return curl_exec($this->handle);
    }

    public function getInfo($name)
    {
        return curl_getinfo($this->handle, $name);
    }

    public function close()
    {
        curl_close($this->handle);
        return $this;
    }
}