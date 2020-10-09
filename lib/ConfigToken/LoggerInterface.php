<?php

namespace ConfigToken;

interface LoggerInterface
{
    const DEBUG = 100;
    const INFO = 200;
    const NOTICE = 250;
    const WARNING = 300;
    const ERROR = 400;

    public function addRecord($level, $message, array $context = array());
}