<?php

namespace ConfigToken\Utils;


interface HttpRequestInterface
{
    public function getUrl();
    public function setUrl($url);
    public function setOption($name, $value);
    public function execute();
    public function getInfo($name);
    public function close();
}