<?php

namespace ConfigToken\TreeSerializer;

use ConfigToken\Exception\AlreadyRegisteredException;
use ConfigToken\TreeSerializer\Exception\UnknownContentTypeException;
use ConfigToken\TreeSerializer\Exception\UnknownFileExtensionException;
use ConfigToken\TreeSerializer\Types\IniTreeSerializer;
use ConfigToken\TreeSerializer\Types\JsonTreeSerializer;
use ConfigToken\TreeSerializer\Types\PhpTreeSerializer;
use ConfigToken\TreeSerializer\Types\XmlTreeSerializer;
use ConfigToken\TreeSerializer\Types\YmlTreeSerializer;


class TreeSerializerFactory
{
    /** @var TreeSerializerInterface[] */
    protected static $registeredByFileExtension = array();
    /** @var TreeSerializerInterface[] */
    protected static $registeredByContentType = array();

    protected static function registerKnownTypes()
    {
        if (!empty(static::$registeredByContentType)) {
            return;
        }
        static::internalRegister(new IniTreeSerializer());
        static::internalRegister(new JsonTreeSerializer());
        static::internalRegister(new PhpTreeSerializer());
        static::internalRegister(new XmlTreeSerializer());
        static::internalRegister(new YmlTreeSerializer());
    }

    /**
     * @param string $contentType
     * @return boolean
     */
    public static function isRegisteredByContentType($contentType)
    {
        static::registerKnownTypes();
        return isset(static::$registeredByContentType[$contentType]);
    }

    /**
     * @param string $contentType
     * @return TreeSerializerInterface
     *
     * @throws UnknownContentTypeException
     */
    public static function getByContentType($contentType)
    {
        static::registerKnownTypes();
        if (isset(static::$registeredByContentType[$contentType])) {
            return static::$registeredByContentType[$contentType];
        }
        throw new UnknownContentTypeException($contentType);
    }

    /**
     * @param string $fileExtension
     * @return boolean
     */
    public static function isRegisteredByFileExtension($fileExtension)
    {
        static::registerKnownTypes();
        return isset(static::$registeredByFileExtension[$fileExtension]);
    }

    /**
     * @param string $fileExtension
     * @return TreeSerializerInterface
     *
     * @throws UnknownFileExtensionException
     */
    public static function getByFileExtension($fileExtension)
    {
        static::registerKnownTypes();
        if (isset(static::$registeredByFileExtension[$fileExtension])) {
            return static::$registeredByFileExtension[$fileExtension];
        }
        throw new UnknownFileExtensionException($fileExtension);
    }

    public static function isRegistered(TreeSerializerInterface $treeSerializer)
    {
        try {
            static::getByContentType($treeSerializer::getContentType());
            return true;
        } catch (UnknownContentTypeException $e) {
            return false;
        }
    }

    /**
     * Used internally to register a new tree serializer implementation without performing checks.
     *
     * @param TreeSerializerInterface $treeSerializer
     */
    protected static function internalRegister(TreeSerializerInterface $treeSerializer)
    {
        static::$registeredByContentType[$treeSerializer::getContentType()] = $treeSerializer;
        static::$registeredByFileExtension[$treeSerializer::getFileExtension()] = $treeSerializer;
    }

    public static function register(TreeSerializerInterface $treeSerializer)
    {
        if (static::isRegistered($treeSerializer)) {
            throw new AlreadyRegisteredException(
                sprintf(
                    'Tree serializer for content type %s is already registered.',
                    $treeSerializer::getContentType()
                )
            );
        }
        static::internalRegister($treeSerializer);
    }
}