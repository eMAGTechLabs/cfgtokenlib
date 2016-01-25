<?php

namespace ConfigToken\Utils;


interface HttpRequestInterface
{
    /**
     * Get the URL or null if not set.
     *
     * @return string|null
     */
    public function getUrl();

    /**
     * Set the URL.
     *
     * @param string $url
     * @return $this
     */
    public function setUrl($url);

    /**
     * Set the value for the specified option.
     * @see curl_setopt
     *
     * @param integer $opt
     * @param mixed $value
     * @return $this
     */
    public function setOption($opt, $value);

    /**
     * Initialize the request.
     */
    public function open();

    /**
     * Execute the request and return the result or False.
     *
     * @return string|boolean
     */
    public function execute();

    /**
     * Get info about last request.
     * @see curl_getinfo
     *
     * @param int $opt
     * @return mixed
     */
    public function getInfo($opt = 0);

    /**
     * Close the request.
     */
    public function close();
}