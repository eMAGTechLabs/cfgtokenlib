<?php

namespace ConfigToken\Utils;


use ConfigToken\Utils\Exceptions\LocalProcessException;

class LocalProcess implements LocalProcessInterface
{
    /**
     * @var string
     */
    protected $command;

    /**
     * @param string $command The command.
     */
    public function __construct($command)
    {
        $this->setCommand($command);
    }
    /**
     * Check if the command to be executed was set.
     *
     * @return boolean
     */
    public function hasCommand()
    {
        return isset($this->command);
    }

    /**
     * Get the command to be executed.
     *
     * @return string|null
     */
    public function getCommand()
    {
        if (!$this->hasCommand()) {
            return null;
        }
        return $this->command;
    }

    /**
     * Set the command to be executed.
     *
     * @param string $value The new value.
     * @return $this
     */
    public function setCommand($value)
    {
        $this->command = $value;
        return $this;
    }
    /**
     * Execute the given command and return the output, errors and exit code.
     *
     * @throws LocalProcessException
     * @return LocalProcessResult
     */
    public function executeAndGetResult()
    {
        if (!$this->hasCommand()) {
            throw new LocalProcessException('The command was not set.');
        }

        $result = new LocalProcessResult();
        $descriptorSpec = array(
            1 => array('pipe', 'w'),
            2 => array('pipe', 'w'),
        );
        $pipes = array();

        $result->setStartTimeNow();
        $resource = proc_open($this->getCommand(), $descriptorSpec, $pipes);
        $result->setStdout(stream_get_contents($pipes[1]));
        $result->setStderr(stream_get_contents($pipes[2]));
        foreach ($pipes as $pipe) {
            fclose($pipe);
        }
        $status = proc_get_status($resource);
        $result->setExitCode($status['exitcode']);
        proc_close($resource);
        $result->setEndTimeNow();

        return $result;
    }
}