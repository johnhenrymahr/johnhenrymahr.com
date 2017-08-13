<?php

class MailerTest extends \PHPUnit\Framework\TestCase
{

    protected $config;

    protected $logger;

    protected $obj;

    protected $mailer;

    protected function setUp()
    {

        $this->logger = \Mockery::mock('\JHM\LoggerInterface');
        $this->mailer = \Mockery::mock('\PHPMailer');
        $this->mailer->shouldReceive('smtpClose');
        $this->config = \Mockery::mock('\JHM\ConfigInterface');
        $this->config->shouldReceive('get')->with('systemMailTo')->andReturn('testmail@mail.com')->byDefault();
        $this->config->shouldReceive('get')->with('systemMailToName')->andReturn('John Mahr')->byDefault();
        $this->config->shouldReceive('get')->with('smtp.enabled')->andReturn(false)->byDefault();
        $this->obj = new \JHM\Mailer($this->mailer, $this->config, $this->logger);
    }

    protected function tearDown()
    {
        $this->config = null;
        $this->obj = null;
        \Mockery::close();

    }

    public function testSetupSystemMailer()
    {
        $this->mailer->shouldReceive('addAddress')->with('testmail@mail.com', 'John Mahr');
        $this->obj->setupSystemMailer();
        $this->assertTrue(true);
    }

    public function testSetRecipient()
    {
        $this->mailer->shouldReceive('addAddress')->with('joe@test.com', 'bar');
        $this->obj->setRecipient('joe@test.com', 'bar');
        $this->assertEquals('joe@test.com', $this->obj->toAddress);
        $this->assertEquals('bar', $this->obj->toName);
    }
    public function testSetRecipientBadAddress()
    {
        $this->logger->shouldReceive('log')->once();
        $this->assertFalse($this->obj->setRecipient('foo', 'bar'));
        $this->assertEquals('', $this->obj->toAddress);
        $this->assertEquals('', $this->obj->toName);
    }
    public function testSetTextBody()
    {
        $this->obj->setBody('a test string');
        $this->assertEquals($this->mailer->Body, 'a test string');
    }
    public function testSetFromAddress()
    {
        $this->mailer->shouldReceive('setFrom')->with('test@test.com', 'tester');
        $this->obj->setFrom('test@test.com', 'tester');
        $this->assertEquals($this->obj->fromAddress, 'test@test.com');
        $this->assertEquals($this->obj->fromName, 'tester');
    }
    public function testResetInstance()
    {
        $this->mailer->shouldReceive('setFrom');
        $this->mailer->shouldReceive('addAddress');
        $this->mailer->shouldReceive('addReplyTo');
        $this->mailer->shouldReceive('clearAllRecipients');
        $this->mailer->shouldReceive('clearReplyTos');
        $this->mailer->shouldReceive('isHTML');
        $this->mailer->shouldReceive('addAttachment');
        $this->mailer->shouldReceive('clearAttachments')->once();
        $this->obj->setFrom('test@test.com', 'tester');
        $this->obj->setRecipient('testOther@test.com', 'testerOther');
        $this->obj->setBody('a test body');
        $this->config->shouldReceive('getStorage')->with('docs')->andReturn('/path/to/docs/');
        $this->mailer->shouldReceive('addAttachment')->with('/path/to/docs/testfile');
        $this->obj->addAttachment('testfile');
        $this->obj->setReplyTo('joe@ele.com', 'joe');
        $this->obj->reset();
        $this->assertEquals('', $this->obj->fromName);
        $this->assertEquals('', $this->obj->fromAddress);
        $this->assertEquals('', $this->obj->toAddress);
        $this->assertEquals('', $this->obj->toName);
        $this->assertEquals('', $this->obj->replyAddress);
        $this->assertEquals('', $this->obj->replyName);
        $this->assertEquals('', $this->obj->body);
        $this->assertEquals('', $this->obj->subject);
        $this->assertEquals('', $this->obj->get('from'));
        $this->assertEquals('', $this->obj->get('Body'));
        $this->assertEquals('', $this->obj->get('Subject'));
        $this->assertEquals('', $this->obj->get('AltBody'));
    }
    public function testSetUpNoReply()
    {
        $this->mailer->shouldReceive('setFrom')->with('test@mail.com', 'testmailer');
        $this->obj->noReplyAddress = 'test@mail.com';
        $this->obj->noReplyName = 'testmailer';
        $this->obj->setupNoReply();
        $this->assertEquals($this->obj->fromAddress, 'test@mail.com');
        $this->assertEquals($this->obj->fromName, 'testmailer');
    }
    public function testSetFromBadAddress()
    {
        $this->obj->setFrom('test', 'tester');
        $this->assertEquals($this->obj->fromAddress, '');
        $this->assertEquals($this->obj->fromName, '');
    }
    public function testAddAttachment()
    {
        $this->config->shouldReceive('getStorage')->with('docs')->andReturn('/path/to/docs/');
        $this->mailer->shouldReceive('addAttachment')->with('/path/to/docs/testfile');
        $this->obj->addAttachment('testfile');
        $this->assertTrue(true);
    }
    public function testSetReplyToAddress()
    {
        $this->mailer->shouldReceive('addReplyTo')->with('test@test.com', 'tester');
        $this->obj->setReplyTo('test@test.com', 'tester');
        $this->assertEquals($this->obj->replyAddress, 'test@test.com');
        $this->assertEquals($this->obj->replyName, 'tester');
    }
    public function testSetReplyToBadAddress()
    {
        $this->obj->setReplyTo('test', 'tester');
        $this->assertEquals($this->obj->fromAddress, '');
        $this->assertEquals($this->obj->fromName, '');
    }
    public function testSendHTML()
    {
        $this->mailer->shouldReceive('isHTML');
        $this->mailer->shouldReceive('send')->once()->andReturn(true);
        $this->obj->setHTML(true);
        $this->obj->setBody('<p>a test </p>');
        $this->assertTrue($this->obj->send());
        $this->assertEquals("a test\n", $this->mailer->AltBody);
    }
    public function testSendFail()
    {
        $this->mailer->shouldReceive('send')->once()->andReturn(false);
        $this->mailer->ErrorInfo = 'a test message';
        $this->obj->toAddress = 'joe@email.com';
        $this->logger->shouldReceive('log')->once();
        $this->assertFalse($this->obj->send());
    }
}
