<?php

namespace ConfigToken\TreeCompiler;

use ConfigToken\Exception\AlreadyRegisteredException;


class XrefCollection implements \IteratorAggregate, \ArrayAccess
{
    /** @var Xref[] */
    protected $collection;

    function __construct()
    {
        $this->clear();
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Retrieve an external iterator
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return \Traversable An instance of an object implementing <b>Iterator</b> or <b>Traversable</b>
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->collection);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Whether a offset exists
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset <p>
     * An offset to check for.
     * </p>
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     */
    public function offsetExists($offset)
    {
        return $this->hasById($offset);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to retrieve
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     * @return mixed Can return all value types.
     */
    public function offsetGet($offset)
    {
        return $this->getById($offset);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to set
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset <p>
     * The offset to assign the value to.
     * </p>
     * @param mixed $value <p>
     * The value to set.
     * </p>
     * @return void
     * @throws \Exception
     */
    public function offsetSet($offset, $value)
    {
        if (!$value instanceof Xref) {
            throw new \Exception('Value is not an Xref.');
        }
        if (!isset($offset)) {
            $this->add($value);
            return;
        }
        $this->collection[$offset] = $value;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to unset
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     * @return void
     */
    public function offsetUnset($offset)
    {
        if (!isset($offset)) {
            return;
        }
        if ($this->hasById($offset)) {
            $this->removeById($offset);
            return;
        }
        trigger_error(sprintf('Xref with id "%s" not in collection.', $offset), E_USER_WARNING);
    }

    public function isEmpty()
    {
        return empty($this->collection);
    }

    public function clear()
    {
        $this->collection = array();

        return $this;
    }

    public function add(Xref $xref)
    {
        $xrefId = $xref->getId();
        if ($this->hasById($xrefId)) {
            return $this->getById($xrefId);
        }
        $this->collection[] = $xref;
        return $xref;
    }

    public function removeById($xrefId)
    {
        $xref = null;
        if ($this->hasById($xrefId)) {
            foreach ($this->collection as $key => $xref) {
                if ($xref->getId() == $xrefId) {
                    unset($this->collection[$key]);
                }
            }
        } else {
            throw new \Exception(
                sprintf(
                    'Xref with id "%s" not in collection.',
                    $xrefId
                )
            );
        }
        return $xref;
    }

    public function remove(Xref $xref)
    {
        try {
            $this->removeById($xref->getId());
        } catch (\Exception $e) {
            if (strpos($e->getMessage(), $xref->getId())) {
                throw new \Exception(
                    sprintf(
                        'Xref of type "%s" with location "%s" not in collection.',
                        $xref->getType(),
                        $xref->getLocation()
                    )
                );
            }
            throw $e;
        }
    }

    public function hasById($xrefId)
    {
        foreach ($this->collection as $xref) {
            if ($xref->getId() == $xrefId) {
                return true;
            }
        }
        return false;
    }

    public function has(Xref $xref)
    {
        return $this->hasById($xref->getId());
    }

    public function getById($xrefId)
    {
        foreach ($this->collection as $xref) {
            if ($xref->getId() == $xrefId) {
                return $xref;
            }
        }
        throw new \Exception(sprintf('Xref with Id "%s" not in collection.', $xrefId));
    }

    public function parse($xrefs, $typeDelimiter, $overwrite = False, $ignore = True)
    {
        if (!is_array($xrefs)) {
            $xrefs = array($xrefs);
        }
        $parsed = array();
        foreach ($xrefs as $key => $value) {
            $xref = Xref::makeFromDefinitionString($value, $typeDelimiter);
            if ($this->has($xref)) {
                if (!$ignore) {
                    throw new AlreadyRegisteredException(
                        sprintf(
                            'Not allowed to overwrite Xref of type "%s" with location "%s" already in collection.',
                            $xref->getType(),
                            $xref->getLocation()
                        )
                    );
                }
                if ($overwrite && (!$ignore)) {
                    $this->add($xref);
                }
            } else {
                $this->add($xref);
            }
            $parsed[$key] = $xref;
        }
        return $parsed;
    }

    public function hasUnresolved()
    {
        foreach($this->collection as $xref) {
            if (!$xref->isResolved()) {
                return true;
            }
        }
        return false;
    }

    public function resolve($force = false)
    {
        foreach($this->collection as $xref) {
            $xref->resolve($force);
        }
    }
}