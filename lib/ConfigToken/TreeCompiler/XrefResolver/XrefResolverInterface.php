<?php

namespace ConfigToken\TreeCompiler\XrefResolver;

use ConfigToken\TreeCompiler\Xref;


interface XrefResolverInterface
{
    public static function getType();
    public static function resolve(Xref $xref, $force = false);
}