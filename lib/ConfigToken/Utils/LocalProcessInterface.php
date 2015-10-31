<?php

namespace ConfigToken\Utils;


use ConfigToken\Utils\Exceptions\LocalProcessException;

interface LocalProcessInterface
{
    /**
     * Get the command to be executed.
     *
     * @return string
     */
    public function getCommand();

    /**
     * Execute the given command and return the output, errors and exit code.
     *
     * @throws LocalProcessException
     * @return LocalProcessResult
     */
    public function executeAndGetResult();
}