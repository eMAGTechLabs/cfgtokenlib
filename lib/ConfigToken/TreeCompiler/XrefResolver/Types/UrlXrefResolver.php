<?php

namespace ConfigToken\TreeCompiler\XrefResolver\Types;

use ConfigToken\Event;
use ConfigToken\EventManager;
use ConfigToken\TreeCompiler\XrefResolver\Exception\UnknownXrefTypeException;
use ConfigToken\TreeCompiler\XrefResolver\Exception\XrefResolverFetchException;
use ConfigToken\TreeCompiler\Xref;
use ConfigToken\TreeSerializer\TreeSerializerFactory;


class UrlXrefResolver extends AbstractXrefResolver
{
    const EVENT_ID_RESOLVE_URL = 'resolve-url';
    const EVENT_XREF = 'xref';

    /**
     * Get the resolver type identifier string.
     *
     * @return string
     */
    public static function getType()
    {
        return 'url';
    }

    /**
     * Fetch the data from the specified location of the Xref.
     *
     * @param Xref $xref
     * @param boolean $force If true and Xref already fetched, force the resolver to fetch the data again.
     * @throws UnknownXrefTypeException
     * @throws XrefResolverFetchException
     * @throws \ConfigToken\TreeCompiler\XrefResolver\Exception\InvalidXrefTypeException
     * @throws \ConfigToken\TreeSerializer\Exception\UnknownContentTypeException
     * @throws \ConfigToken\TreeSerializer\Exception\UnknownFileExtensionException
     */
    public static function resolve(Xref $xref, $force = false)
    {
        if ($xref->isResolved() && (!$force)) {
            return;
        }
        static::matchType($xref);
        if (!$xref->hasLocation()) {
            throw new XrefResolverFetchException($xref);
        }
        $eventManager = EventManager::getInstance();
        if ($eventManager->hasListeners()) {
            $event = new Event(self::EVENT_ID_RESOLVE_URL);
            $event->data[self::EVENT_XREF] = $xref;
            $event->data[Event::RESULT] = false;
            $eventManager->dispatch($event);
            unset($event->data[self::EVENT_XREF]);
            if (isset($event->data[Event::RESULT])) {
                if ($event->data[Event::RESULT] === true) {
                    return;
                }
                if ($event->data[Event::RESULT] !== false) {
                    throw new XrefResolverFetchException(
                        $xref,
                        sprintf(
                            'Got error message from listener: %s',
                            $event->data[Event::RESULT]
                        )
                    );
                }
            }
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $xref->getLocation());
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        $data = curl_exec($ch);

        $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        if ($httpCode != 200) {
            throw new XrefResolverFetchException($xref, sprintf('Got response code %d', $httpCode));
        }
        if (TreeSerializerFactory::isRegisteredByContentType($contentType)) {
            $serializer = TreeSerializerFactory::getByContentType($contentType);
            $xref->setContentType($contentType);
        } else {
            $path = parse_url($xref->getLocation(), PHP_URL_PATH);
            $fileExtension = pathinfo($path, PATHINFO_EXTENSION);
            if (!TreeSerializerFactory::isRegisteredByFileExtension($fileExtension)) {
                throw new UnknownXrefTypeException(
                    sprintf(
                        'Unable to find resolver for Xref content type "%s" or file extension "%s" for location "%s".',
                        $contentType,
                        $fileExtension,
                        $xref->getLocation()
                    )
                );
            }
            $serializer = TreeSerializerFactory::getByFileExtension($fileExtension);
            $xref->setContentType($serializer::getContentType());
        }
        // TODO: catch exception, show xref location in error message
        $data = $serializer::deserialize($data);
        $xref->setData($data);
        $xref->setResolved(true);
    }
}