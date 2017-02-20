<?php

class MailerTest extends \PHPUnit\Framework\TestCase
{

    protected $config;

    protected $obj;

    protected function setUp()
    {
        $this->config = \Mockery::mock('\JHM\ConfigInterface');
        $this->config->shouldReceive('get')->with('mailTo')->andReturn('testmail@mail.com')->byDefault();
        $this->obj = \Mockery::mock('\JHM\Mailer[_send]', array($this->config))->shouldAllowMockingProtectedMethods();
    }

    protected function tearDown()
    {
        $this->config = null;
        $this->obj = null;
        \Mockery::close();

    }

    public function testSendCalled()
    {
        $this->config->shouldReceive('get')->with('sendMail')->andReturn(true);
        $this->obj->shouldReceive('_send')->once();
        $this->obj->send();
        $this->assertTrue(true);
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

        $body = "test body line 1\ntest body line 2";
        $from = '-fjoe.from@mail.com';
        $this->config->shouldReceive('get')->with('sendMail')->andReturn(true);
        $this->obj->shouldReceive('_send')->once()->with('testmail@mail.com', 'test subject', $body, $from);
        $this->obj->setSubject('test subject');
        $this->obj->setBody('test body line 1');
        $this->obj->setBody('test body line 2');
        $this->obj->setFromAddress('joe.from@mail.com');
        $this->obj->send();
        $this->assertTrue(true);
    }

    public function testThrowBadTo()
    {
        $this->expectException(\JHM\JhmException::class);
        $this->config->shouldReceive('get')->with('mailTo')->andReturn('badMail');
        $obj = new \JHM\Mailer($this->config);
    }
}
