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


    function __construct($type, $location)
    {
        $this->type = $type;
        $this->location = $location;
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

    public function resolve()
    {
        if ($this->isResolved()) {
            return;
        }
        if (!$this->hasType()) {
            throw new \Exception('Unable to resolve Xref without type.');
        }
        $resolver = XrefResolverFactory::getByType($this->type);
        $resolver::resolve($this);
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

    public function getId()
    {
        return md5($this->type . $this->location);
    }

    public function setLocation($value)
    {
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
}