<?php

namespace ConfigToken\Tests\File\Client\Mocks;


class TestCurlRequest extends \ConfigToken\Utils\CurlRequest
{
    protected $expectedResult = null;
    protected $expectedInfo = array();

    /**
     * Get expected result.
     *
     * @return mixed|null
     */
    public function getExpectedResult()
    {
        return $this->expectedResult;
    }

    /**
     * Set expected result.
     *
     * @param mixed $value The new value.
     * @return $this
     */
    public function setExpectedResult($value)
    {
        $this->expectedResult = $value;
        return $this;
    }

    /**
     * Get expected info.
     *
     * @param integer $opt
     * @return mixed|null
     */
    public function getExpectedInfo($opt)
    {
        if (isset($this->expectedInfo[$opt])) {
            return $this->expectedInfo[$opt];
        }
        return null;
    }

    /**
     * Set expected info.
     *
     * @param integer $opt
     * @param mixed $value The new value.
     * @return $this
     */
    public function setExpectedInfo($opt, $value)
    {
        $this->expectedInfo[$opt] = $value;
        return $this;
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
        return $this;
    }

    /**
     * Initialize the request.
     */
    public function open() {}

    /**
     * Execute the request and return the result or False.
     *
     * @return string|boolean
     */
    public function execute()
    {
        return $this->expectedResult;
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
        return $this->getExpectedInfo($opt);
    }

    /**
     * Close the request.
     */
    public function close() {}

}