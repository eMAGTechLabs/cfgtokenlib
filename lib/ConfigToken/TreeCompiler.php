<?php

namespace ConfigToken;

use ConfigToken\Exception\NotRegisteredException;
use ConfigToken\TreeCompiler\XrefResolver\Types\UrlXrefResolver;
use ConfigToken\TreeCompiler\XrefResolver\Types\LocalFileXrefResolver;
use ConfigToken\TreeCompiler\Exceptions\XrefNotFoundException;
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

    const INCLUDE_TYPE_GROUP = 'group';
    const INCLUDE_TYPE_XREF = 'xref';


    protected function parseTreeXrefs($tree, $includeType, $includeKey)
    {
        $result = new XrefCollection();

        if ((!isset($tree[$this->includeKey])) || (!isset($tree[$this->includeXrefKey]))) {
            // nothing to resolve
            return $result;
        }

        $treeInclude = &$tree[$this->includeKey];
        $treeIncludeXrefs = &$tree[$this->includeKey][$this->includeXrefKey];

        switch ($includeType) {
            case static::INCLUDE_TYPE_GROUP:
                if (!isset($treeInclude[$includeKey])) {
                    throw new XrefNotFoundException(sprintf('The include group "%s" is missing.', $includeKey));
                }
                $result->parse($treeInclude[$includeKey]);
                break;
            case static::INCLUDE_TYPE_XREF:
                if (!isset($treeIncludeXrefs[$includeKey])) {
                    throw new XrefNotFoundException(sprintf('The include key "%s" is missing.', $includeKey));
                }
                $result->parse($treeIncludeXrefs[$includeKey]);
                break;
            default:
                throw new \Exception(
                    sprintf(
                        'Unknown include type "%s" for key "%s" when resolving tree Xrefs.',
                        $includeType,
                        $includeKey
                    )
                );
        }

        return $result;
    }

    protected function recursiveParseXrefs(XrefCollection $xrefCollection, XrefCollection $xrefMaster)
    {
        /** @var Xref $xref */
        foreach ($xrefCollection as $xref) {
            if (!$xrefMaster->has($xref)) {

            }
        }
    }

    protected function recursiveCompile($tree, $includeType = null, $includeKeyValue = null, XrefCollection $xrefMaster)
    {

    }

    public function compile($tree, $includeType = null, $includeKeyValue = null)
    {
        if (!isset($includeType)) {
            $includeType = static::INCLUDE_TYPE_GROUP;
        }
        if (($includeType == self::INCLUDE_TYPE_GROUP) && !isset($includeKeyValue)) {
            $includeKeyValue = $this->includeMainKey;
        }

        $xrefMaster = $this->parseTreeXrefs($tree, $includeType, $includeKeyValue);

        $this->recursiveCompile($tree, $includeType, $includeKeyValue, $xrefMaster);

        return array();
    }

    public function compileXref(Xref $xref, $includeType = null, $includeKeyValue = null)
    {
        $xref->resolve();
        if (!$xref->hasData()) {
            return array();
        }
        $data = $xref->getData();
        return $this->compile($data, $includeType, $includeKeyValue);
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

    public function save($tree, $fileName)
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