<?php

namespace ConfigToken\Utils;


class LocalProcessResult
{
    /** @var integer */
    protected $exitCode= null;
    /** @var string */
    protected $stdout = null;
    /** @var string */
    protected $stderr = null;
    /** @var float|null */
    protected $startTime = null;
    /** @var float|null */
    protected $endTime = null;

    /**
     * Check if the exit code was set.
     *
     * @return boolean
     */
    public function hasExitCode()
    {
        return isset($this->exitCode);
    }

    /**
     * Get the exit code.
     *
     * @return integer|null
     */
    public function getExitCode()
    {
        if (!$this->hasExitCode()) {
            return null;
        }
        return $this->exitCode;
    }

    /**
     * Set the exit code.
     *
     * @param integer $value The new value.
     * @return $this
     */
    public function setExitCode($value)
    {
        $this->exitCode = $value;
        return $this;
    }

    /**
     * Check if the stdout content was set.
     *
     * @return boolean
     */
    public function hasStdout()
    {
        return isset($this->stdout);
    }

    /**
     * Get the stdout content.
     *
     * @return string|null
     */
    public function getStdout()
    {
        if (!$this->hasStdout()) {
            return null;
        }
        return $this->stdout;
    }

    /**
     * Set the stdout content.
     *
     * @param string $value The new value.
     * @return $this
     */
    public function setStdout($value)
    {
        $this->stdout = $value;
        return $this;
    }

    /**
     * Check if the stderr content was set.
     *
     * @return boolean
     */
    public function hasStderr()
    {
        return isset($this->stderr);
    }

    /**
     * Get the stderr content.
     *
     * @return string|null
     */
    public function getStderr()
    {
        if (!$this->hasStderr()) {
            return null;
        }
        return $this->stderr;
    }

    /**
     * Set the stderr content.
     *
     * @param string $value The new value.
     * @return $this
     */
    public function setStderr($value)
    {
        $this->stderr = $value;
        return $this;
    }

    /**
     * Check if the start time was set.
     *
     * @return boolean
     */
    public function hasStartTime()
    {
        return isset($this->startTime);
    }

    /**
     * Get the start time.
     *
     * @return float|null
     */
    public function getStartTime()
    {
        if (!$this->hasStartTime()) {
            return null;
        }
        return $this->startTime;
    }

    /**
     * Set the start time.
     *
     * @param float $value The new value.
     * @return $this
     */
    public function setStartTime($value)
    {
        $this->startTime = $value;
        return $this;
    }

    /**
     * Set the start time to now.
     *
     * @return $this
     */
    public function setStartTimeNow()
    {
        $this->setStartTime(microtime(true));
        return $this;
    }

    /**
     * Check if the end time was set.
     *
     * @return boolean
     */
    public function hasEndTime()
    {
        return isset($this->endTime);
    }

    /**
     * Get the end time.
     *
     * @return float|null
     */
    public function getEndTime()
    {
        if (!$this->hasEndTime()) {
            return null;
        }
        return $this->endTime;
    }

    /**
     * Set the end time.
     *
     * @param float $value The new value.
     * @throws \Exception
     * @return $this
     */
    public function setEndTime($value)
    {
        if (!$this->hasStartTime()) {
            throw new \Exception('Unable to set end time because start time not set.');
        }
        $this->endTime = $value;
        return $this;
    }

    /**
     * Set the end time to now.
     *
     * @throws \Exception
     * @return $this
     */
    public function setEndTimeNow()
    {
        $this->setEndTime(microtime(true));
        return $this;
    }

    /**
     * Check if the actual run time is available.
     *
     * @return boolean
     */
    public function hasActualRunTime()
    {
        return $this->hasStartTime() && $this->hasEndTime();
    }

    /**
     * Get the run time.
     *
     * @param float|null $untilTime If null use either current time or end time, otherwise use supplied time.
     * @throws \Exception
     * @return float|null
     */
    public function getRunTime($untilTime = null)
    {
        if (!$this->hasStartTime()) {
            throw new \Exception('Unable to get the run time because start time not set.');
        }
        if (!isset($untilTime)) {
            if ($this->hasEndTime()) {
                $untilTime = $this->getEndTime();
            } else {
                $untilTime = microtime(true);
            }
        }
        return $untilTime - $this->getStartTime();
    }
}