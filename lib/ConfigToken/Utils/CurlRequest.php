<?php

namespace ConfigToken\Utils;

/**
 * CurlRequest wrapper.
 *
 * @codeCoverageIgnore
 */
class CurlRequest implements HttpRequestInterface
{
    private $handle = null;
    private $url = null;

    public function __construct($url)
    {
        $this->open();
        $this->setUrl($url);
    }

    /**
     * Get the URL or null if not set.
     *
     * @return string|null
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set the URL.
     *
     * @param string $url
     * @return $this
     */
    public function setUrl($url)
    {
        $this->url = $url;
        return $this->setOption(CURLOPT_URL, $url);
    }

    /**
     * Set the value for the specified option.
     * @see curl_setopt
     *
     * @param integer $opt
     * @param mixed $value
     * @return $this
     */
    public function setOption($opt, $value)
    {
        curl_setopt($this->handle, $opt, $value);
        return $this;
    }

    /**
     * Initialize the request.
     */
    public function open()
    {
        $this->handle = curl_init();
    }

    /**
     * Execute the request and return the result or False.
     *
     * @return string|boolean
     */
    public function execute()
    {
        return curl_exec($this->handle);
    }

    /**
     * Get info about last request.
     * @see curl_getinfo
     *
     * @param int $opt
     * @return mixed
     */
    public function getInfo($opt = 0)
    {
        return curl_getinfo($this->handle, $opt);
    }

    /**
     * Close the request.
     */
    public function close()
    {
        curl_close($this->handle);
        return $this;
    }
}