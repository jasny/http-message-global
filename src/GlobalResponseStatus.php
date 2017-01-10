<?php

namespace Jasny\HttpMessage;

use Jasny\HttpMessage\ResponseStatus;
use Jasny\HttpMessage\Wrap;

/**
 * PSR-7 methods for http response status that binds to the global environment
 */
class GlobalResponseStatus extends ResponseStatus
{
    use Wrap\Headers;
    
    /**
     * HTTP protocol version
     * @var string
     */
    protected $protocolVersion = '1.1';
    
    
    /**
     * Set the protocol version
     * 
     * @param string $version
     * @return static
     */
    public function withProtocolVersion($version)
    {
        if (!is_string($version)) {
            throw new \InvalidArgumentException("Expected protocol version to be a string");
        }
        
        $status = clone $this;
        $status->protocolVersion = $version;
        
        return $status;
    }
    
    
    /**
     * Gets the response status code.
     *
     * The status code is a 3-digit integer result code of the server's attempt
     * to understand and satisfy the request.
     *
     * @return int Status code.
     */
    public function getStatusCode()
    {
        return $this->httpResponseCode() ?: 200;
    }

    /**
     * Gets the response reason phrase associated with the status code.
     *
     * @return string
     */
    public function getReasonPhrase()
    {
        $code = $this->getStatusCode();

        return $this->code === $code && $this->phrase
            ? $this->phrase
            : (isset($this->defaultStatuses[$code]) ? $this->defaultStatuses[$code] : '');
    }

    
    /**
     * Set the specified status code and reason phrase.
     * 
     * @param int    $code
     * @param string $reasonPhrase
     */
    protected function setStatus($code, $reasonPhrase)
    {
        $this->assertHeadersNotSent();

        parent::setStatus($code, $reasonPhrase);
        
        $this->header($this->getHeader());
    }
    
    /**
     * Get the HTTP header to set the HTTP response.
     * 
     * @return string
     */
    protected function getHeader()
    {
        $code = $this->getStatusCode();
        $phrase = $this->getReasonPhrase();
        
        return "HTTP/{$this->protocolVersion} {$code} {$phrase}";
    }
}
