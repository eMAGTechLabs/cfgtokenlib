<?php

namespace ConfigToken;


class DisposableElements
{
    /** @var DisposableInterface[]  */
    public $elements = array();

    public function append(DisposableInterface $element = null)
    {
        if (!isset($element)) {
            return;
        }
        $this->elements[] = $element;
    }

    public function release()
    {
        foreach ($this->elements as $element) {
            $element->release();
        }
        $this->elements = array();
    }
}