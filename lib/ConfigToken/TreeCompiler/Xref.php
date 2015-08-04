<?php

namespace ConfigToken\TreeCompiler;

use ConfigToken\TreeCompiler\XrefResolver\XrefResolverFactory;


class Xref
{
    /** @var string */
    protected $type;

    /** @var string */
    protected $location;

    /** @var string */
    protected $contentType;

    /** @var array */
    protected $data;

    /** @var boolean */
    protected $resolved = false;


    public function __construct($type, $location)
    {
        $this->setType($type);
        $this->setLocation($location);
    }

    /**
     * Get the type and location from the definition string.
     *
     * @param string $typeAndLocation
     * @param string $delimiter
     * @return array Type and Location.
     * @throws \Exception
     */
    public static function parseDefinitionString($typeAndLocation, $delimiter)
    {
        $k = strpos($typeAndLocation, $delimiter);
        if ($k === false) {
            throw new \Exception(sprintf('Missing Xref type in "%s".', $typeAndLocation));
        }
        return array(substr($typeAndLocation, 0, $k), substr($typeAndLocation, $k + 1));
    }

    /**
     * Get the id from the definition string.
     *
     * @param string $typeAndLocation
     * @param string $delimiter
     * @return string
     * @throws \Exception
     */
    public static function getIdFromDefinitionString($typeAndLocation, $delimiter)
    {
        list($type, $location) = static::parseDefinitionString($typeAndLocation, $delimiter);
        return static::computeId($type, $location);
    }

    /**
     * Create Xref instance based on the given definition string.
     *
     * @param string $typeAndLocation
     * @param string $delimiter
     * @return Xref
     * @throws \Exception
     */
    public static function makeFromDefinitionString($typeAndLocation, $delimiter)
    {
        list($type, $location) = static::parseDefinitionString($typeAndLocation, $delimiter);
        $xref = new static($type, $location);
        return $xref;
    }

    public function isResolved()
    {
        return $this->resolved;
    }

    public function setResolved($value)
    {
        $this->resolved = $value;

        return $this;
    }

    public function getResolver()
    {
        return XrefResolverFactory::getByType($this->type);
    }

    public function resolve($force = false)
    {
        if ($this->isResolved() && (!$force)) {
            return;
        }
        if (!$this->hasType()) {
            throw new \Exception('Unable to resolve Xref without type.');
        }
        $resolver = $this->getResolver();
        $resolver::resolve($this, $force);
    }

    public function hasType()
    {
        return isset($this->type);
    }

    public function getType()
    {
        return $this->type;
    }

    public function setType($value)
    {
        $this->type = $value;

        return $this;
    }

    public function hasLocation()
    {
        return isset($this->location);
    }

    public function getLocation()
    {
        return $this->location;
    }

    public static function computeAbsoluteLocation($xrefType, $xrefLocation, $xrefPath)
    {
        $resolver = XrefResolverFactory::getByType($xrefType);
        return $resolver::getAbsoluteLocation($xrefLocation, $xrefPath);
    }

    public static function computeId($type, $location)
    {
        return md5($type . $location);
    }

    public function getId()
    {
        return static::computeId($this->type, $this->location);
    }

    public function setLocation($value)
    {
        if (isset($value)) {
            $resolver = $this->getResolver();
            if (isset($resolver)) {
                $value = $resolver->getPlatformSpecificLocation($value);
            }
        }
        $this->location = $value;

        return $this;
    }

    public function hasContentType()
    {
        return isset($this->contentType);
    }

    public function getContentType()
    {
        return $this->contentType;
    }

    public function setContentType($value)
    {
        $this->contentType = $value;
        return $this;
    }

    public function hasData()
    {
        return isset($this->data);
    }

    public function getData()
    {
        return $this->data;
    }

    public function setData($value)
    {
        $this->data = $value;

        return $this;
    }

    function __toString()
    {
        return $this->getId() . ':' . $this->getType() . ':' . $this->getLocation();
    }


}