<?php

namespace ConfigToken;

use ConfigToken\Exception\NotRegisteredException;
use ConfigToken\TreeCompiler\Exceptions\CircularReferenceException;
use ConfigToken\TreeCompiler\XrefResolver\Exception\UnknownXrefException;
use ConfigToken\TreeCompiler\XrefResolver\Exception\XrefResolverFormatException;
use ConfigToken\TreeCompiler\XrefResolver\Types\UrlXrefResolver;
use ConfigToken\TreeCompiler\XrefResolver\Types\LocalFileXrefResolver;
use ConfigToken\TreeCompiler\Xref;
use ConfigToken\TreeCompiler\XrefCollection;
use ConfigToken\TreeSerializer\TreeSerializerFactory;


class TreeCompiler
{
    /** @var string */
    protected $includeKey = 'include';
    /** @var string */
    protected $includeXrefKey = 'xref';
    /** @var string */
    protected $includeMainKey = 'main';
    /** @var string */
    protected $removeKey = 'remove';
    /** @var string */
    protected $addKey = 'add';

    /** @var XrefCollection */
    protected $xrefs;

    const INCLUDE_TYPE_GROUP = 'group';
    const INCLUDE_TYPE_XREF = 'xref';

    function __construct(XrefCollection $xrefs = null)
    {
        if (!isset($xrefs)) {
            $xrefs = new XrefCollection();
        }
        $this->xrefs = $xrefs;
    }

    public function getXrefs()
    {
        return $this->xrefs;
    }

    /**
     * Get include key.
     *
     * @return string
     */
    public function getIncludeKey()
    {
        return $this->includeKey;
    }

    /**
     * Set include key.
     *
     * @param string $value The new value.
     * @return $this
     */
    public function setIncludeKey($value)
    {
        $this->includeKey = $value;
        return $this;
    }

    /**
     * Recursively remove the keys of one array from another.
     *
     * @param array $toRemove The array containing the keys to be removed.
     * @param array $removeFrom The array from which to remove keys.
     */
    public function recursiveRemoveData(array &$toRemove, array &$removeFrom)
    {
        foreach ($toRemove as $keyToRemove => $childKeysToRemove) {
            if (is_array($childKeysToRemove)) {
                if (isset($removeFrom[$keyToRemove]) && is_array($removeFrom[$keyToRemove])) {
                    $this->recursiveRemoveData($toRemove[$keyToRemove], $removeFrom[$keyToRemove]);
                }
            } else if (array_key_exists($keyToRemove, $removeFrom)) {
                unset($removeFrom[$keyToRemove]);
            }
        }
    }

    /**
     * Recursively add and override keys from one array into another.
     *
     * @param array $addFrom The source array.
     * @param array $addTo The destination array.
     */
    public function recursiveAddData(array &$addFrom, array &$addTo)
    {
        if (empty($addTo)) {
            /** @noinspection PhpUnusedLocalVariableInspection */
            $addTo = $addFrom;
            return;
        }
        foreach ($addFrom as $keyToAdd => $childKeys) {
            if (is_array($childKeys)) {
                if (isset($addTo[$keyToAdd]) && is_array($addTo[$keyToAdd])) {
                    $this->recursiveAddData($addFrom[$keyToAdd], $addTo[$keyToAdd]);
                    continue;
                }
            }
            $addTo[$keyToAdd] = $addFrom[$keyToAdd];
        }
    }

    /**
     * Retrieve a list of Xref keys to be resolved.
     *
     * @param Xref $xref
     * @param array $xrefDataInclude
     * @param array $xrefDataIncludeXrefs
     * @param string $includeType
     * @param string|array $includeTypeValue
     * @return array
     *
     * @throws XrefResolverFormatException
     */
    protected function getXrefKeysToBeResolved(Xref $xref, $xrefDataInclude, $xrefDataIncludeXrefs,
                                               $includeType, $includeTypeValue) {
        $result = array();
        switch ($includeType) {
            case static::INCLUDE_TYPE_XREF:
                $missing = array();
                foreach ($includeTypeValue as $xrefKey) {
                    if (!isset($xrefDataIncludeXrefs[$xrefKey])) {
                        $missing[] = $xrefKey;
                    }
                }
                if (empty($missing)) {
                    unset($missing);
                } else {
                    throw new XrefResolverFormatException(
                        $xref,
                        sprintf(
                            "Required to explicitly include the list of Xref keys [\"%s\"] " .
                            "but unable to find [\"%s\"].",
                            implode('", "', $includeTypeValue),
                            implode('", "', $missing)
                        )
                    );
                }
                $result = $includeTypeValue;
                break;
            case static::INCLUDE_TYPE_GROUP:
                if (!isset($xrefDataInclude[$includeTypeValue])) {
                    throw new XrefResolverFormatException(
                        $xref,
                        sprintf(
                            "Unable to find the required include group of Xref keys named \"%s\"",
                            $includeTypeValue
                        )
                    );
                }
                $result = $xrefDataInclude[$includeTypeValue];
                break;
        }
        return $result;
    }

    /**
     * Recursively resolve Xrefs and compile data.
     *
     * @param Xref $xref
     * @param string|null $includeType
     * @param string|array|null $includeTypeValue
     * @param array $visited
     * @return array
     *
     * @throws CircularReferenceException
     * @throws Exception\AlreadyRegisteredException
     * @throws TreeCompiler\XrefResolver\Exception\UnknownXrefTypeException
     * @throws UnknownXrefException
     * @throws XrefResolverFormatException
     * @throws \Exception
     */
    protected function recursiveCompileXref(Xref $xref, $includeType = null, $includeTypeValue = null, &$visited)
    {
        if (!isset($includeType)) {
            $includeType = static::INCLUDE_TYPE_GROUP;
        }

        switch ($includeType) {
            case static::INCLUDE_TYPE_GROUP:
                if (!isset($includeTypeValue)) {
                    $includeTypeValue = $this->includeMainKey;
                } else if (gettype($includeTypeValue) != 'string') {
                    throw new XrefResolverFormatException(
                        $xref,
                        sprintf(
                            "Include type value must be a string representing the include group name. " .
                            "\"%s\" given instead.",
                            gettype($includeTypeValue)
                        )
                    );
                }
                break;
            case static::INCLUDE_TYPE_XREF:
                if (!is_array($includeTypeValue) || empty($includeTypeValue)) {
                    throw new XrefResolverFormatException(
                        $xref,
                        "Include type value must be a non-empty array of strings for named includes."
                    );
                }
                break;
            default:
                throw new \Exception(sprintf('Unknown include type "%s".', $includeType));
        }
        $mustIncludeSpecificGroup = $includeTypeValue != $this->includeMainKey;

        $xref->resolve();

        $xrefData = $xref->getData();
        if (empty($xrefData)) {
            if (!is_array($xrefData)) {
                throw new XrefResolverFormatException(
                    $xref,
                    "De-serialized data must be an array."
                );
            }
            return array();
        }

        if ((!isset($xrefData[$this->includeKey])) || (!is_array($xrefData[$this->includeKey]))) {
            switch ($includeType) {
                case static::INCLUDE_TYPE_XREF:
                    throw new XrefResolverFormatException(
                        $xref,
                        sprintf(
                            "Required to explicitly include the list of Xref keys [\"%s\"] " .
                            "but the \"%s\" key is missing from the first level.",
                            implode('", "', $includeTypeValue),
                            $this->includeKey
                        )
                    );
                case static::INCLUDE_TYPE_GROUP:
                    if (!$mustIncludeSpecificGroup) {
                        if (isset($xrefData[$this->addKey])) {
                            return $xrefData[$this->addKey];
                        } else {
                            if (isset($xrefData[$this->removeKey])) {
                                unset($xrefData[$this->removeKey]);
                            }
                            return $xrefData;
                        }
                    }
                    throw new XrefResolverFormatException(
                        $xref,
                        sprintf(
                            "Required to explicitly include the \"%s\" group of Xref keys " .
                            "but the \"%s\" key is missing from the first level.",
                            $includeTypeValue,
                            $this->includeKey
                        )
                    );
            }
        }
        $xrefDataInclude = &$xrefData[$this->includeKey];

        if ((!isset($xrefDataInclude[$this->includeXrefKey])) || (!is_array($xrefDataInclude[$this->includeXrefKey]))) {
            switch ($includeType) {
                case static::INCLUDE_TYPE_XREF:
                    throw new XrefResolverFormatException(
                        $xref,
                        sprintf(
                            "Required to explicitly include the list of Xref keys [\"%s\"] " .
                            "but the \"%s\" key is missing from the \"%s\" key on the first level.",
                            implode('", "', $includeTypeValue),
                            $this->includeXrefKey,
                            $this->includeKey
                        )
                    );
                case static::INCLUDE_TYPE_GROUP:
                    if (!$mustIncludeSpecificGroup) {
                        if (isset($xrefData[$this->addKey])) {
                            return $xrefData[$this->addKey];
                        } else {
                            if (isset($xrefData[$this->removeKey])) {
                                unset($xrefData[$this->removeKey]);
                            }
                            return $xrefData;
                        }
                    }
                    throw new XrefResolverFormatException(
                        $xref,
                        sprintf(
                            "Required to explicitly include the \"%s\" group of Xref keys " .
                            "but the \"%s\" key is missing from the \"%s\" key on the first level.",
                            $includeTypeValue,
                            $this->includeXrefKey,
                            $this->includeKey
                        )
                    );
            }
        }
        $xrefDataIncludeXrefs = &$xrefDataInclude[$this->includeXrefKey];

        $xrefKeysToBeResolved = $this->getXrefKeysToBeResolved(
            $xref,
            $xrefDataInclude,
            $xrefDataIncludeXrefs,
            $includeType,
            $includeTypeValue
        );

        $xrefsToBeParsed = array();
        foreach ($xrefKeysToBeResolved as $xrefKeyToBeResolved) {
            if (!isset($xrefDataIncludeXrefs[$xrefKeyToBeResolved])) {
                throw new UnknownXrefException(
                    sprintf(
                        "Unable to find the required Xref definition named \"%s\".",
                        $xrefKeyToBeResolved
                    )
                );
            }
            $xrefsToBeParsed[] = $xrefDataIncludeXrefs[$xrefKeyToBeResolved];
        }
        unset($xrefDataIncludeXrefs);

        $xrefId = $xref->getId();
        $visited[$xrefId] = sprintf('%s:%s', $xref->getType(), $xref->getLocation());
        /** @var Xref[] $includedXrefs */
        $includedXrefs = $this->xrefs->parse($xrefsToBeParsed);
        $result = array();
        foreach ($includedXrefs as $includedXref) {
            if (isset($visited[$includedXref->getId()])) {
                throw new CircularReferenceException(
                    sprintf(
                        'Tree compiler encountered circular reference at "%s" in path ["%s"].',
                        sprintf('%s:%s', $includedXref->getType(), $includedXref->getLocation()),
                        implode('", "', $visited)
                    )
                );
            }
            $includeData = $this->recursiveCompileXref(
                $includedXref,
                static::INCLUDE_TYPE_GROUP,
                $this->includeMainKey,
                $visited
            );
            $this->recursiveAddData($includeData, $result);
        }
        unset($visited[$xrefId]);

        if (isset($xrefData[$this->removeKey])) {
            $this->recursiveRemoveData($xrefData[$this->removeKey], $result);
        }

        if (isset($xrefData[$this->addKey])) {
            $this->recursiveAddData($xrefData[$this->addKey], $result);
        }

        return $result;
    }

    public function compileXref(Xref $xref, $includeType = null, $includeTypeValue = null)
    {
        $visited = array();
        $compiledData = $this->recursiveCompileXref($xref, $includeType, $includeTypeValue, $visited);
        return $compiledData;
    }

    public function compileLocalFile($inputFileName, $includeType = null, $includeKeyValue = null)
    {
        $xref = new Xref(LocalFileXrefResolver::getType(), $inputFileName);
        return $this->compileXref($xref, $includeType, $includeKeyValue);
    }

    public function compileUrl($inputUrl, $includeType = null, $includeKeyValue = null)
    {
        $xref = new Xref(UrlXrefResolver::getType(), $inputUrl);
        return $this->compileXref($xref, $includeType, $includeKeyValue);
    }

    public function save(array $tree, $fileName)
    {
        $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);
        if (!TreeSerializerFactory::isRegisteredByFileExtension($fileExtension)) {
            throw new NotRegisteredException(
                sprintf(
                    'Unable to find serializer for extension "%s".',
                    $fileExtension
                )
            );
        }
        $serializer = TreeSerializerFactory::getByFileExtension($fileExtension);
        $serializedTree = $serializer->serialize($tree);
        file_put_contents($fileName, $serializedTree);
    }
}