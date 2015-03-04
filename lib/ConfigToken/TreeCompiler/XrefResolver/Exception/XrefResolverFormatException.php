<?php

namespace ConfigToken\TreeCompiler\XrefResolver\Exception;

use ConfigToken\TreeCompiler\Xref;


class XrefResolverFormatException extends \Exception
{
    public function __construct(Xref $xref, $message = "", $code = 0, \Exception $previous = null)
    {
        if ($xref->hasLocation()) {
            $message = sprintf(
                "Error resolving Xref of type \"%s\" from location \"%s\"%s",
                $xref->getType(),
                $xref->getLocation(),
                (($message == '') ? '' : (': ' . $message))
            );
        }
        parent::__construct($message, $code, $previous);
    }
}