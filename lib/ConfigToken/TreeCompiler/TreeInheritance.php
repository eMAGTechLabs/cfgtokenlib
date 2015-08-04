<?php

namespace ConfigToken\TreeCompiler;


class TreeInheritance
{
    /** @var string */
    protected $addKey;

    /** @var string */
    protected $removeKey;

    public function __construct($addKey = 'add', $removeKey = 'remove')
    {
        $this->addKey = $addKey;
        $this->removeKey = $removeKey;
    }

    /**
     * Check if "add" key was set.
     *
     * @return boolean
     */
    public function hasAddKey()
    {
        return isset($this->addKey);
    }

    /**
     * Get "add" key.
     *
     * @return string|null
     */
    public function getAddKey()
    {
        if (!$this->hasAddKey()) {
            return null;
        }
        return $this->addKey;
    }

    /**
     * Set "add" key.
     *
     * @param string $value The new value.
     * @return $this
     */
    public function setAddKey($value)
    {
        $this->addKey = $value;
        return $this;
    }

    /**
     * Check if "remove" key was set.
     *
     * @return boolean
     */
    public function hasRemoveKey()
    {
        return isset($this->removeKey);
    }

    /**
     * Get "remove" key.
     *
     * @return string|null
     */
    public function getRemoveKey()
    {
        if (!$this->hasRemoveKey()) {
            return null;
        }
        return $this->removeKey;
    }

    /**
     * Set "remove" key.
     *
     * @param string $value The new value.
     * @return $this
     */
    public function setRemoveKey($value)
    {
        $this->removeKey = $value;
        return $this;
    }

    /**
     * Recursively remove the keys of one array from another.
     *
     * @param array $toRemove The array containing the keys to be removed.
     * @param array $from The array from which to remove keys.
     */
    public function removeRecursive(array &$toRemove, array &$from)
    {
        foreach ($toRemove as $keyToRemove => $childKeysToRemove) {
            if (is_array($childKeysToRemove)) {
                if (isset($from[$keyToRemove]) && is_array($from[$keyToRemove])) {
                    $this->removeRecursive($toRemove[$keyToRemove], $from[$keyToRemove]);
                    continue;
                }
                return;
            }
            if (array_key_exists($keyToRemove, $from)) {
                unset($from[$keyToRemove]);
                continue;
            }
            return;
        }
    }

    /**
     * Recursively add and override keys from one array into another.
     *
     * @param array $from The source array.
     * @param array $to The destination array.
     */
    public function addRecursive(array &$from, array &$to)
    {
        if (empty($to)) {
            /** @noinspection PhpUnusedLocalVariableInspection */
            $to = &$from;
            return;
        }
        foreach ($from as $keyToAdd => $childKeys) {
            if (is_array($childKeys)) {
                if (isset($to[$keyToAdd]) && is_array($to[$keyToAdd])) {
                    $this->addRecursive($from[$keyToAdd], $to[$keyToAdd]);
                    continue;
                }
            }
            $to[$keyToAdd] = $from[$keyToAdd];
        }
    }


    /**
     * "Inherit" keys from a parent array in a child array.
     * The child dictates which keys to be removed from the parent array prior to inheriting.
     *
     * Both the child and parent arrays must have the following structure:
     * array(
     *   [removeKey] => array(),
     *   [addKey] => array(),
     * )
     * At least one of the above keys must be present.
     * If the parent array is missing both keys then it's contents will be considered to belong to the addKey.
     *
     * @param $childArray
     * @param $parentArray
     */
    public function inherit(&$childArray, $parentArray)
    {
        // remove child[removeKey] from parent[addKey]
        if ((!empty($childArray[$this->removeKey])) && (!empty($parentArray[$this->addKey]))) {
            $this->removeRecursive($childArray[$this->removeKey], $parentArray[$this->addKey]);
        }

        // add child[addKey] to parent[addKey] overriding final values if necessary
        if ((!empty($childArray[$this->addKey])) && (isset($parentArray[$this->addKey]))) {
            $this->addRecursive($childArray[$this->addKey], $parentArray[$this->addKey]);
        }

        // return modified parent[addKey]
        if (isset($parentArray[$this->addKey])) {
            $childArray = array(
                $this->addKey => $parentArray[$this->addKey],
            );
            return;
        }

        // return child[addKey]
        if (isset($childArray[$this->addKey])) {
            $childArray = array(
                $this->addKey, $childArray[$this->addKey]
            );
            return;
        }

        // initialize empty child array
        $childArray = array(
            $this->addKey => array()
        );
    }
}