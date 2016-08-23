<?php

namespace Jasny\HttpMessage\DerivedAttribute;

use PHPUnit_Framework_TestCase;
use PHPUnit_Framework_MockObject_MockObject;

use Jasny\HttpMessage\DerivedAttribute\ClientIp;
use Jasny\HttpMessage\ServerRequest;

/**
 * @covers \Jasny\HttpMessage\DerivedAttribute\ClientIp
 */
class ClientIpTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var ServerRequest|PHPUnit_Framework_MockObject_MockObject
     */
    protected $request;
    
    
    /**
     * Run before each test
     */
    protected function setUp()
    {
        $this->request = $this->getMockBuilder(ServerRequest::class)
            ->setMethods(['getServerParams', 'getHeaderLine'])
            ->disableOriginalConstructor()
            ->disableProxyingToOriginalMethods()
            ->getMock();
        
        $this->request->expects($this->once())->method('getServerParams')
            ->willReturn(['REMOTE_ADDR' => '10.0.0.1']);
    }
    
    
    /**
     * No proxy trusted, no proxy used
     */
    public function testNoTrustedProxy()
    {
        $clientIp = new ClientIp();
        
        $this->assertEquals('10.0.0.1', $clientIp($this->request));
    }
    
    
    /**
     * No trusted proxy, Client-Ip header is used
     */
    public function testNoTrustedProxyClientIp()
    {
        $this->request->expects($this->any())->method('getHeaderLine')
            ->willReturnMap([['Client-Ip', '192.168.0.1'], ['X-Forwarded-For', null]]);
        
        $clientIp = new ClientIp();
        
        $this->assertEquals('10.0.0.1', $clientIp($this->request));
    }
    
    /**
     * No trusted proxy, X-Forwarded-For header is used
     */
    public function testNoTrustedProxyForwardedFor()
    {
        $this->request->expects($this->any())->method('getHeaderLine')
            ->willReturnMap([['Client-Ip', null], ['X-Forwarded-For', '192.168.0.1, 192.168.1.100']]);
        
        $clientIp = new ClientIp();
        
        $this->assertEquals('10.0.0.1', $clientIp($this->request));
    }

    
    /**
     * Trust any proxy, no proxy is used
     */
    public function testTrustAnyProxy()
    {
        $clientIp = new ClientIp(true);
        
        $this->assertEquals('10.0.0.1', $clientIp($this->request));
    }
    
    /**
     * Trust any proxy, Client-Ip header is used
     */
    public function testTrustAnyProxyClientIp()
    {
        $this->request->expects($this->any())->method('getHeaderLine')
            ->willReturnMap([['Client-Ip', '192.168.0.1'], ['X-Forwarded-For', null]]);
        
        $clientIp = new ClientIp(true);
        
        $this->assertEquals('192.168.0.1', $clientIp($this->request));
    }
    
    /**
     * Trust any proxy, X-Forwarded-For header is used
     */
    public function testTrustAnyProxyForwardedFor()
    {
        $this->request->expects($this->any())->method('getHeaderLine')
            ->willReturnMap([['Client-Ip', null], ['X-Forwarded-For', '192.168.0.1, 192.168.1.100']]);
        
        $clientIp = new ClientIp(true);
        
        $this->assertEquals('192.168.1.100', $clientIp($this->request));
    }

    
    /**
     * Trust connected client as proxy, no proxy is used
     */
    public function testTrustConnectedIp()
    {
        $clientIp = new ClientIp('10.0.0.1');
        
        $this->assertEquals('10.0.0.1', $clientIp($this->request));
    }
    
    /**
     * Trust connected client as proxy, Client-Ip header is used
     */
    public function testTrustConnectedIpClientIp()
    {
        $this->request->expects($this->any())->method('getHeaderLine')
            ->willReturnMap([['Client-Ip', '192.168.0.1'], ['X-Forwarded-For', null]]);
        
        $clientIp = new ClientIp('10.0.0.1');
        
        $this->assertEquals('192.168.0.1', $clientIp($this->request));
    }
    
    /**
     * Trust connected client as proxy, X-Forwarded-For header is used
     */
    public function testTrustConnectedIpForwardedFor()
    {
        $this->request->expects($this->any())->method('getHeaderLine')
            ->willReturnMap([['Client-Ip', null], ['X-Forwarded-For', '192.168.0.1, 192.168.1.100']]);
        
        $clientIp = new ClientIp('10.0.0.1');
        
        $this->assertEquals('192.168.0.1', $clientIp($this->request));
    }

    
    /**
     * Trust connected client in CIDR as proxy, no proxy is used
     */
    public function testTrustConnectedCidr()
    {
        $clientIp = new ClientIp('10.0.0.0/24');
        
        $this->assertEquals('10.0.0.1', $clientIp($this->request));
    }
    
    /**
     * Trust connected client in CIDR as proxy, Client-Ip header is used
     */
    public function testTrustConnectedCidrClientIp()
    {
        $this->request->expects($this->any())->method('getHeaderLine')
            ->willReturnMap([['Client-Ip', '192.168.0.1'], ['X-Forwarded-For', null]]);
        
        $clientIp = new ClientIp('10.0.0.0/24');
        
        $this->assertEquals('192.168.0.1', $clientIp($this->request));
    }
    
    /**
     * Trust connected client in CIDR as proxy, X-Forwarded-For header is used
     */
    public function testTrustConnectedCidrForwardedFor()
    {
        $this->request->expects($this->any())->method('getHeaderLine')
            ->willReturnMap([['Client-Ip', null], ['X-Forwarded-For', '192.168.0.1, 192.168.1.100']]);
        
        $clientIp = new ClientIp('10.0.0.0/24');
        
        $this->assertEquals('192.168.0.1', $clientIp($this->request));
    }
   
    
    /**
     * Trust irrelevant proxy, no proxy is used
     */
    public function testTrustIrrelevant()
    {
        $clientIp = new ClientIp('172.0.0.0/24');
        
        $this->assertEquals('10.0.0.1', $clientIp($this->request));
    }
    
    /**
     * Trust irrelevant proxy, Client-Ip header is used
     */
    public function testTrustIrrelevantClientIp()
    {
        $this->request->expects($this->any())->method('getHeaderLine')
            ->willReturnMap([['Client-Ip', '192.168.0.1'], ['X-Forwarded-For', null]]);
        
        $clientIp = new ClientIp('172.0.0.0/24');
        
        $this->assertEquals('10.0.0.1', $clientIp($this->request));
    }
    
    /**
     * Trust irrelevant proxy, X-Forwarded-For header is used
     */
    public function testTrustIrrelevantForwardedFor()
    {
        $this->request->expects($this->any())->method('getHeaderLine')
            ->willReturnMap([['Client-Ip', null], ['X-Forwarded-For', '192.168.0.1, 192.168.1.100']]);
        
        $clientIp = new ClientIp('172.0.0.0/24');
        
        $this->assertEquals('10.0.0.1', $clientIp($this->request));
    }
}