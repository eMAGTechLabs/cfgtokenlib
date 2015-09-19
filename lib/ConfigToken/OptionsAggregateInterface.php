<?php

namespace ConfigToken;
use ConfigToken\Exception\OptionsMissingException;
use ConfigToken\Exception\OptionValueException;

/**
 * Implemented by classes aggregating the Options bag class.
 *
 * @package ConfigToken
 */
interface OptionsAggregateInterface
{
    /**
     * Get the aggregated options bag.
     *
     * @return Options
     */
    public function getOptions();

    /**
     * Validate the given options bag.
     *
     * @param Options $options
     * @throws OptionsMissingException
     * @throws OptionValueException
     */
    public static function validateOptions(Options $options);
}