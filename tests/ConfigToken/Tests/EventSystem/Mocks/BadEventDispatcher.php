<?php

namespace ConfigToken\Tests\EventSystem\Mocks;


class BadEventDispatcher
{
    public static function getClassName()
    {
        return get_called_class();
    }
}