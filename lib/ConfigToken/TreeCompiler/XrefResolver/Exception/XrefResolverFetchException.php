<?php

namespace ConfigToken\TreeCompiler\XrefResolver\Exception;

use ConfigToken\TreeCompiler\Xref;


class XrefResolverFetchException extends \Exception
{
    public function __construct(Xref $xref, $message = "", $code = 0, \Exception $previous = null)
    {
        if ($xref->hasLocation()) {
            $message = sprintf(
                'Unable to fetch external reference of type "%s" from location "%s"%s.',
                $xref->getType(),
                $xref->getLocation(),
                (($message == '') ? '' : (': ' . $message))
            );
        } else {
            $message = sprintf(
                'Unable to fetch external reference of type "%s": no location specified.',
                $xref->getType()
            );
        }
        parent::__construct($message, $code, $previous);
    }

}