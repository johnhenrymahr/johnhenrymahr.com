<?php

class CrsfTokenTest extends \PHPUnit\Framework\TestCase
{
    protected $sessionMock;

    protected $loggerMock;

    protected $obj;

    protected $token;

    protected function setUp()
    {
        $this->sessionMock = \Mockery::mock('\JHM\SessionInterface');
        $this->sessionMock->shouldReceive('start')->andReturn(true)->byDefault();
        $this->sessionMock->shouldReceive('set')->with('csrfKey', 'testkey')->byDefault();
        $this->sessionMock->shouldReceive('id')->andReturn('theid');
        $this->sessionMock->shouldReceive('get')->with('csrfKey')->andReturn(null)->byDefault();
        $this->loggerMock = \Mockery::mock('\JHM\LoggerInterface');
        $this->token = hash_hmac('sha256', 'theidfoo', 'testkey');
        $this->obj = new \JHM\CsrfToken($this->sessionMock, $this->loggerMock, 'testkey');
    }

    protected function tearDown()
    {
        \Mockery::close();
    }

    public function testGenerateToken()
    {
        $this->assertEquals($this->token, $this->obj->generateToken('foo '));
    }

    public function testCheckTokenPass()
    {
        $this->sessionMock->shouldReceive('get')->with('csrfKey')->andReturn('testkey');
        $this->assertTrue($this->obj->checkToken($this->token, 'foo'));
    }

    public function testGetFieldId()
    {
        $this->assertEquals('pr_id', $this->obj->getField());
    }

    public function testCheckTokenFail()
    {
        $this->sessionMock->shouldReceive('get')->with('csrfKey')->andReturn('testkey');
        $this->loggerMock->shouldReceive('log')->with('WARNING', 'CSRF: invalid token.', array('token' => 'wrong-token', 'formId' => 'foo'));
        $this->assertFalse($this->obj->checkToken('wrong-token', 'foo'));
    }

    public function testCheckInvalidTokenType()
    {
        $this->sessionMock->shouldReceive('get')->with('csrfKey')->andReturn('testkey');
        $this->loggerMock->shouldReceive('log')->with('WARNING', 'CSRF: invalid token format');
        $this->assertFalse($this->obj->checkToken(null, 'foo'));
    }

    public function testException()
    {
        $this->expectException(\JHM\JhmException::class);
        $this->sessionMock->shouldReceive('start')->andReturn(false);
        $obj = new \JHM\CsrfToken($this->sessionMock, $this->loggerMock);
        $obj->generateToken('foobar');

    }
}
