<?php

class GaTest extends \PHPUnit\Framework\TestCase
{

    protected $config;

    protected $logger;

    protected $curl;

    protected $obj;

    protected $cookie;

    protected function setUp()
    {

        $this->logger = \Mockery::mock('\JHM\LoggerInterface');
        $this->logger->shouldReceive('log');
        $this->config = \Mockery::mock('\JHM\ConfigInterface');
        $this->config->shouldReceive('get')->with('ga_property_id')->andReturn('ga_key_now');
        $this->curl = \Mockery::mock('\Curl\Curl');
        $this->curl->shouldReceive('close');
        $this->cookie = \Mockery::mock('\JHM\Cookie');
        $this->obj = new \JHM\Ga($this->config, $this->logger, $this->curl, $this->cookie);
    }

    protected function tearDown()
    {
        \Mockery::close();
    }

    public function testTrackPageHitNotReady()
    {
        $this->expectException(\JHM\JhmException::class);
        $this->obj->trackPageHit();
    }

    public function testGenCID()
    {
        $request = array(
            array(), //GET
            array(), //POST,
            array(),
            array(),
            array(),
            array(
                'SERVER_ADDR' => ' 127.0.0.1',
                'SERVER_NAME' => 'testdomain.com',
                'HTTP_HOST' => 'testdomain.com/download',
                'HTTP_REFERER' => 'http://localhost/ref',
                'HTTP_USER_AGENT' => 'useragentstring',
                'SERVER_PORT' => '80 ',
                'QUERY_STRING' => 'foo=bar',
                'REQUEST_URI' => '/download',
                'REMOTE_ADDR' => 'testaddr',
            ),
        );
        $this->obj->init($request);
        $this->curl->shouldReceive('setUserAgent')->once()->with('useragentstring');
        $this->curl->shouldReceive('post');
        $this->curl->response = '200OK';
        $this->cookie->shouldReceive('set')->with('_jhm', Mockery::any(), Mockery::any());
        $this->assertEquals('200OK', $this->obj->trackPageHit('uiiiid', 'test title'));
    }

    public function testTrackPageHit()
    {
        $request = array(
            array(), //GET
            array(), //POST,
            array(),
            array(
                '_jhm' => 'testCookie',
            ),
            array(),
            array(
                'SERVER_ADDR' => ' 127.0.0.1',
                'SERVER_NAME' => 'testdomain.com',
                'HTTP_HOST' => 'testdomain.com/download',
                'HTTP_REFERER' => 'http://localhost/ref',
                'HTTP_USER_AGENT' => 'useragentstring',
                'SERVER_PORT' => '80 ',
                'QUERY_STRING' => 'foo=bar',
                'REQUEST_URI' => '/download',
                'REMOTE_ADDR' => 'testaddr',
            ),
        );
        $expected = array(
            'v' => '1',
            'ds' => 'web', //data source
            'tid' => 'ga_key_now',
            'cid' => 'testCookie',
            't' => 'pageView',
            'dl' => 'http://testdomain.com/download?foo=bar',
            'ua' => 'useragentstring', //user-agent
            'uip' => 'testaddr', //ip-address from client
            'dr' => 'http://localhost/ref', //referrer
            'dt' => 'test title',
            'uid' => 'uiiiid',
            'dh' => 'testdomain.com', // hostname
            'dp' => '/download',
        );
        $this->obj->init($request);
        $this->curl->shouldReceive('setUserAgent')->once()->with('useragentstring');
        $this->curl->shouldReceive('post')->with('https://www.google-analytics.com/collect', $expected);
        $this->curl->response = '200OK';
        $this->assertEquals('200OK', $this->obj->trackPageHit('uiiiid', 'test title'));
    }

}
