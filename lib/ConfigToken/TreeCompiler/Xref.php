<?php

namespace ConfigToken\TreeCompiler;

use ConfigToken\FileClient\FileClientInterface;


class Xref
{
    /** @var FileClientInterface */
    protected $fileClient;

    /** @var string */
    protected $location;

    /** @var string */
    protected $contentType;

    /** @var array */
    protected $data;

    /** @var XrefTokenResolverCollection */
    protected $tokenResolvers;

    /** @var boolean */
    protected $resolved = false;

    public function __construct($fileName, FileClientInterface $fileClient,
                                XrefTokenResolverCollection $tokenResolvers = null)
    {
        $this->setTokenResolvers($tokenResolvers);
        $this->setLocation($fileName);
        $this->setFileClient($fileClient);
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

    public function getType()
    {
        if (!$this->hasFileClient()) {
            return null;
        }
        $this->getFileClient()->getServerType();
    }

    public function getId()
    {
        if ($this->hasFileClient()) {
            return $this->getFileClient()->getFileLocationHash($this->getLocation());
        } else {
            return md5($this->getLocation());
        }
    }

    /**
     * Check if the file client interface was set.
     *
     * @return boolean
     */
    public function hasFileClient()
    {
        return isset($this->fileClient);
    }

    /**
     * Get the file client interface.
     *
     * @return FileClientInterface|null
     */
    public function getFileClient()
    {
        if (!$this->hasFileClient()) {
            return null;
        }
        return $this->fileClient;
    }

    /**
     * Set the file client interface.
     *
     * @param FileClientInterface $value The new value.
     * @return $this
     */
    public function setFileClient($value)
    {
        $this->fileClient = $value;
        return $this;
    }

    /**
     * Check if the file name was set.
     *
     * @return boolean
     */
    public function hasLocation()
    {
        return isset($this->location);
    }

    /**
     * Get the file name.
     *
     * @return string|null
     */
    public function getLocation()
    {
        if (!$this->hasLocation()) {
            return null;
        }
        return $this->location;
    }

    /**
     * Set the file name.
     *
     * @param string $value The new value.
     * @return $this
     */
    public function setLocation($value)
    {
        $this->location = $value;
        return $this;
    }

    /**
     * Check if the token resolvers collection was set.
     *
     * @return boolean
     */
    public function hasTokenResolvers()
    {
        return isset($this->tokenResolvers);
    }

    /**
     * Get the token resolvers collection.
     *
     * @return XrefTokenResolverCollection|null
     */
    public function getTokenResolvers()
    {
        if (!$this->hasTokenResolvers()) {
            return null;
        }
        return $this->tokenResolvers;
    }

    /**
     * Set the token resolvers collection.
     *
     * @param XrefTokenResolverCollection $value The new value.
     * @return $this
     */
    public function setTokenResolvers($value)
    {
        $this->tokenResolvers = $value;
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

    public function resolve($force = false)
    {
        if ($this->isResolved() && (!$force)) {
            return;
        }
        list($contentType, $data) = $this->getFileClient()->readFile($this->location);
        $this->setData($data);
        $this->setContentType($contentType);
    }


}