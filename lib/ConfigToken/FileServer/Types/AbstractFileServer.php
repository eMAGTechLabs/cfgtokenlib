<?php

namespace ConfigToken\FileServer;


use ConfigToken\ConnectionSettings\ConnectionSettingsInterface;
use ConfigToken\FileServer\Exception\RequestFormatException;

abstract class AbstractFileServer implements FileServerInterface
{
    /** @var ConnectionSettingsInterface */
    protected $connectionSettings;

    function __construct(ConnectionSettingsInterface $connectionSettings)
    {
        $this->setConnectionSettings($connectionSettings);
    }

    /**
     * Check if the connection settings interface was set.
     *
     * @return boolean
     */
    public function hasConnectionSettings()
    {
        return isset($this->connectionSettings);
    }

    /**
     * Get the connection settings interface.
     *
     * @return ConnectionSettingsInterface|null
     */
    public function getConnectionSettings()
    {
        if (!$this->hasConnectionSettings()) {
            return null;
        }
        return $this->connectionSettings;
    }

    /**
     * Set the connection settings interface.
     *
     * @param ConnectionSettingsInterface $value The new value.
     * @return $this
     */
    public function setConnectionSettings($value)
    {
        $this->connectionSettings = $value;
        return $this;
    }

    /**
     * Set the HTTP response code and optionally print it.
     *
     * @param integer|null $code The desired response code. Default is the previous one or 200.
     * @param bool|true $output If true, the response will be printed.
     * @param string $outputTemplate The output template to use when printing the response.
     * @return integer|null The response code.
     * @throws \Exception For unknown HTTP status codes.
     */
    public function setHttpResponseCode($code=null, $output=true, $outputTemplate='<h1>HTTP <code/> <message/></h1>')
    {
        if (!isset($code)) {
            if (function_exists('http_response_code')) {
                $code = http_response_code();
            } else {
                $code = (isset($GLOBALS['http_response_code']) ? $GLOBALS['http_response_code'] : 200);
            }
        }
        switch ($code) {
            case 100: $text = 'Continue'; break;
            case 101: $text = 'Switching Protocols'; break;
            case 200: $text = 'OK'; break;
            case 201: $text = 'Created'; break;
            case 202: $text = 'Accepted'; break;
            case 203: $text = 'Non-Authoritative Information'; break;
            case 204: $text = 'No Content'; break;
            case 205: $text = 'Reset Content'; break;
            case 206: $text = 'Partial Content'; break;
            case 300: $text = 'Multiple Choices'; break;
            case 301: $text = 'Moved Permanently'; break;
            case 302: $text = 'Moved Temporarily'; break;
            case 303: $text = 'See Other'; break;
            case 304: $text = 'Not Modified'; break;
            case 305: $text = 'Use Proxy'; break;
            case 400: $text = 'Bad Request'; break;
            case 401: $text = 'Unauthorized'; break;
            case 402: $text = 'Payment Required'; break;
            case 403: $text = 'Forbidden'; break;
            case 404: $text = 'Not Found'; break;
            case 405: $text = 'Method Not Allowed'; break;
            case 406: $text = 'Not Acceptable'; break;
            case 407: $text = 'Proxy Authentication Required'; break;
            case 408: $text = 'Request Time-out'; break;
            case 409: $text = 'Conflict'; break;
            case 410: $text = 'Gone'; break;
            case 411: $text = 'Length Required'; break;
            case 412: $text = 'Precondition Failed'; break;
            case 413: $text = 'Request Entity Too Large'; break;
            case 414: $text = 'Request-URI Too Large'; break;
            case 415: $text = 'Unsupported Media Type'; break;
            case 500: $text = 'Internal Server Error'; break;
            case 501: $text = 'Not Implemented'; break;
            case 502: $text = 'Bad Gateway'; break;
            case 503: $text = 'Service Unavailable'; break;
            case 504: $text = 'Gateway Time-out'; break;
            case 505: $text = 'HTTP Version not supported'; break;
            default:
                throw new \Exception('Unknown http status code "' . htmlentities($code) . '"');
                break;
        }
        if (function_exists('http_response_code')) {
            http_response_code($code);
        } else {
            $protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');
            header($protocol . ' ' . $code . ' ' . $text);
            $GLOBALS['http_response_code'] = $code;
        }
        if ($output) {
            echo str_replace(array('<code/>', '<message/>'), array($code, $text), $outputTemplate);
        }
        return $code;
    }

    /**
     * Override to specify a different request file key.
     *
     * @return string
     */
    public function getRequestFileKey()
    {
        return 'fileName';
    }

    /**
     * Implement to return the default request or null if required to be specified.
     *
     * @return array|null
     */
    public abstract function getDefaultRequest();

    /**
     * Implement to return the default request method or null if required to be specified.
     *
     * @return string|null
     */
    public abstract function getDefaultRequestMethod();

    /**
     * Implement to validate the given request format.
     *
     * @param array $request
     * @return boolean
     */
    public function isRequestFormatValid($request) {
        return isset($request[$this->getRequestFileKey()]);
    }

    /**
     * Implement to return array of allowed request methods.
     *
     * @return array
     */
    public abstract function getAllowedRequestMethods();

    /**
     * Process either the current request or the request with the specified parameters and method.
     *
     * @param boolean $output If true, standard error messages will be printed.
     * @param array $request The request parameters.
     * @param string $requestMethod The request method.
     * @throws RequestFormatException
     * @return boolean
     */
    public function processRequest($output = true, $request = null, $requestMethod = null)
    {
        if (!isset($request)) {
            $request = $this->getDefaultRequest();
        }
        if (!isset($request)) {
            throw new RequestFormatException('Request not specified and no default available.');
        }
        if (!isset($requestMethod)) {
            $requestMethod = $this->getDefaultRequestMethod();
        }
        if (!isset($requestMethod)) {
            throw new RequestFormatException('Request method not specified and no default available.');
        }
        $allowedMethods = $this->getAllowedRequestMethods();
        if (!in_array($requestMethod, $allowedMethods)) {
            $allowedMethodsStr = implode(', ', $allowedMethods);
            header('Allow: ' . $allowedMethodsStr);
            $this->setHttpResponseCode(405, $output); // method not allowed
            return false;
        }
        if (!$this->isRequestFormatValid($request)) {
            $this->setHttpResponseCode(400, $output); // bad request
            return false;
        }
        return $this->serveFile($request[$this->getRequestFileKey()], $output);
    }

    /**
     * Write the file attachment HTTP response headers.
     *
     * @param string $contentType
     * @param integer $fileSize
     * @param string $filePath
     */
    protected function writeHeaders($contentType, $fileSize, $filePath)
    {
        header('Cache-Control: private');
        header('Content-Type: ' . $contentType);
        if ($fileSize !== false) {
            header('Content-Length: ' . $fileSize);
        }
        header('Content-Disposition: attachment; filename=' . basename($filePath));
    }
}