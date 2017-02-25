<?php

class MailerTest extends \PHPUnit\Framework\TestCase
{

    protected $config;

    protected $logger;

    protected $obj;

    protected function setUp()
    {

        $this->logger = \Mockery::mock('\JHM\LoggerInterface');
        $this->config = \Mockery::mock('\JHM\ConfigInterface');
        $this->config->shouldReceive('get')->with('mailTo')->andReturn('testmail@mail.com')->byDefault();
        $this->obj = \Mockery::mock('\JHM\Mailer[_send, _getTimeStamp]', array($this->config, $this->logger))->shouldAllowMockingProtectedMethods();
    }

    protected function tearDown()
    {
        $this->config = null;
        $this->obj = null;
        \Mockery::close();

    }

    public function testSendCalled()
    {
        $this->obj->shouldReceive('_getTimeStamp')->andReturn('10-6');
        $this->config->shouldReceive('get')->with('sendMail')->andReturn(true);
        $this->obj->shouldReceive('_send')->once()->andReturn(true);
        $result = $this->obj->send();
        $this->assertTrue($result);
    }

    public function testSendNotCalled()
    {
        $this->config->shouldReceive('get')->with('sendMail')->andReturn(false);
        $this->obj->shouldReceive('_send')->never();
        $this->obj->send();
        $this->assertTrue(true);
    }

    public function testMailArguments()
    {

        $body = "Date: 10-6\ntest body line 1\ntest body line 2";
        $from = '-fjoe.from@mail.com';
        $this->obj->shouldReceive('_getTimeStamp')->andReturn('10-6');
        $this->config->shouldReceive('get')->with('sendMail')->andReturn(true);
        $this->obj->shouldReceive('_send')->once()->with('testmail@mail.com', 'test subject', $body, $from)->andReturn(true);
        $this->obj->setSubject('test subject');
        $this->obj->setBody('test body line 1');
        $this->obj->setBody('test body line 2');
        $this->obj->setFromAddress('joe.from@mail.com');
        $result = $this->obj->send();
        $this->assertTrue($result);
        $this->assertTrue($this->obj->sent);
        $timestamp = $this->obj->timestamp;
        $this->assertTrue(!empty($timestamp));
    }

    public function testGetters()
    {
        $body = "Date: 10-6\ntest body line 1\ntest body line 2";
        $from = '-fjoe.from@mail.com';
        $this->obj->shouldReceive('_getTimeStamp')->andReturn('10-6');
        $this->obj->setSubject('test subject');
        $this->obj->setBody('test body line 1');
        $this->obj->setBody('test body line 2');
        $this->obj->setFromAddress('joe.from@mail.com');
        $this->assertEquals('10-6', $this->obj->timestamp);
        $this->assertEquals('joe.from@mail.com', $this->obj->from);
        $this->assertEquals('test subject', $this->obj->subject);
        $this->assertEquals($body, $this->obj->body);
        $this->assertEquals('testmail@mail.com', $this->obj->to);
    }

    public function testLogMailErrors()
    {
        $body = "Date: 10-6\ntest body line 1\ntest body line 2";
        $this->obj->shouldReceive('_getTimeStamp')->andReturn('10-6');
        $this->config->shouldReceive('get')->with('sendMail')->andReturn(true);
        $this->obj->shouldReceive('_send')->once()->andReturn(false);
        $this->logger->shouldReceive('log')->once()->with('WARNING', 'Could not send message');
        $result = $this->obj->send();
        $this->assertFalse($result);
    }

    public function testThrowBadTo()
    {
        $this->expectException(\JHM\JhmException::class);
        $this->config->shouldReceive('get')->with('mailTo')->andReturn('badMail');
        $obj = new \JHM\Mailer($this->config, $this->logger);
    }
}
