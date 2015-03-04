<?php

namespace ConfigToken\TreeCompiler;

use ConfigToken\Exception\AlreadyRegisteredException;
use ConfigToken\TreeCompiler\XrefResolver\Exception\UnknownXrefTypeException;


class XrefCollection implements \IteratorAggregate
{
    /** @var Xref[] */
    protected $collection;

    /** @var string */
    protected $typeDelimiter = ':';

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
        $this->collection[$xref->getId()] = $xref;
        return $xref;
    }

    public function remove(Xref $xref)
    {
        if ($this->has($xref)) {
            $xrefKey = $xref->getId();
            $xref = $this->collection[$xrefKey];
            unset($this->collection[$xrefKey]);
        } else {
            throw new \Exception(
                sprintf(
                    'Xref of type "%s" with location "%s" not in collection.',
                    $xref->getType(),
                    $xref->getLocation()
                )
            );
        }
        return $xref;
    }

    public function hasById($xrefId)
    {
        return isset($this->collection[$xrefId]);
    }

    public function has(Xref $xref)
    {
        return $this->hasById($xref->getId());
    }

    public function parse($xrefs, $overwrite = False, $ignore = True)
    {
        if (!is_array($xrefs)) {
            $xrefs = array($xrefs);
        }
        $parsed = array();
        foreach ($xrefs as $key => $value) {
            $k = strpos($value, $this->typeDelimiter);
            if ($k === false) {
                throw new UnknownXrefTypeException(sprintf('Missing Xref type for %s = %s.', $key, $value));
            }
            $xrefType = substr($value, 0, $k);
            $xrefLocation = substr($value, $k + 1);
            $xref = new Xref($xrefType, $xrefLocation);
            if (isset($this->collection[$xref->getId()])) {
                if (!$ignore) {
                    throw new AlreadyRegisteredException(
                        sprintf(
                            'Not allowed to overwrite Xref of type "%s" with location "%s" already in collection.',
                            $xrefType,
                            $xrefLocation
                        )
                    );
                }
                if ($overwrite && (!$ignore)) {
                    $this->collection[$xref->getId()] = $xref;
                }
            } else {
                $this->collection[$xref->getId()] = $xref;
            }
            $parsed[$key] = $this->collection[$xref->getId()];
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

    public function resolve()
    {
        foreach($this->collection as $xref) {
            $xref->resolve();
        }
    }
}