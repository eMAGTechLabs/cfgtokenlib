<?php

namespace ConfigToken\TreeCompiler;


use ConfigToken\ConnectionSettings\Types\LocalFileConnectionSettings;
use ConfigToken\FileClient\FileClientFactory;

class XrefFactory
{
    public static function makeFromDefinition($definition)
    {

    }

    public static function makeFromLocalFile($fileName, $rootPath = null)
    {
        $connectionSettings = new LocalFileConnectionSettings();
        if (isset($rootPath)) {
            $connectionSettings->setRootPath($rootPath);
        }
        $fileClient = FileClientFactory::make('local', $connectionSettings);
        $xref = new Xref($fileName, $fileClient);
        return $xref;
    }
}