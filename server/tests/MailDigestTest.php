<?php
class MailDigestTest extends \PHPUnit\Framework\TestCase
{
    protected $configMock;

    protected $loggerMock;

    protected $mailerMock;

    protected $digest;

    protected $obj;

    protected $logfile;

    protected function setUp()
    {
        $this->loggerMock = \Mockery::mock('\JHM\LoggerInterface');
        $this->configMock = \Mockery::mock('\JHM\ConfigInterface');
        $root = \org\bovigo\vfs\vfsStream::setup('digest');
        $datadir = \org\bovigo\vfs\vfsStream::newDirectory('data')->at($root);
        $storagedir = \org\bovigo\vfs\vfsStream::newDirectory('data')->at($datadir);
        $logdir = \org\bovigo\vfs\vfsStream::newDirectory('digest')->at($storagedir);
        $this->digest = \org\bovigo\vfs\vfsStream::newFile('2017_12__digest')->at($logdir);
        $this->digestFail = \org\bovigo\vfs\vfsStream::newFile('2017_13__digest', 0000)->at($logdir);
        $this->configMock->shouldReceive('getStorage')->with('digest')->once()->andReturn($logdir->url() . '/')->byDefault();
        $this->obj = \Mockery::mock('\JHM\MailDigest[_getDigestName]', array($this->configMock, $this->loggerMock))->shouldAllowMockingProtectedMethods();
        $this->obj->shouldReceive('_getDigestName')->andReturn('2017_12__digest')->byDefault();
        $this->mailerMock = \Mockery::mock('\JHM\MailerInterface');
    }

    protected function tearDown()
    {
        \Mockery::close();
    }

    public function testWrite()
    {
        $this->mailerMock->body = "Body Line1\nBodyLine2";
        $this->mailerMock->subject = "Subject Line";
        $this->mailerMock->timestamp = '2017';
        $this->mailerMock->from = "from@mail.com";
        $this->mailerMock->to = "to@mailerMock";
        $result = $this->obj->writeMessage($this->mailerMock);
        $message = "Date: 2017\n";
        $message .= "From: from@mail.com\n";
        $message .= "To: to@mailerMock\n";
        $message .= "Subject: Subject Line\n\n";
        $message .= "Body Line1\nBodyLine2\n";
        $message .= "------------------------------\n\n";

        $content = $this->digest->getContent();

        $this->assertEquals($message, $content);
        $this->assertTrue($result);
    }

    public function testWriteFail()
    {
        $this->mailerMock->body = "Body Line1\nBodyLine2";
        $this->mailerMock->subject = "Subject Line";
        $this->mailerMock->timestamp = '2017';
        $this->mailerMock->from = "from@mail.com";
        $this->mailerMock->to = "to@mailerMock";
        $this->obj->shouldReceive('_getDigestName')->andReturn('2017_13__digest');
        $this->loggerMock->shouldReceive('log')->with('WARNING', 'Could not write mail digest.');
        $result = $this->obj->writeMessage($this->mailerMock);
        $this->assertFalse($result);
    }

    public function testFailedSetup()
    {
        $this->expectException(\JHM\JhmException::class);
        $this->configMock->shouldReceive('getStorage')->with('digest')->once()->andReturn('/path/to/nothing');
        $this->loggerMock->shouldReceive('log')->with('WARNING', 'Could not write mail digest. Path: /path/to/nothing');
        $obj = new \JHM\MailDigest($this->configMock, $this->loggerMock);
    }
}
