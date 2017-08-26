<?php
class CvHandlerTest extends \PHPUnit\Framework\TestCase
{

    protected $obj;

    protected $mailer;

    protected $request;

    protected $storage;

    protected $config;

    protected $logger;

    protected $digest;

    protected function setUp()
    {
        $this->mailer = \Mockery::mock('\JHM\MailerInterface');
        $this->request = \Mockery::mock('\Symfony\Component\HttpFoundation\Request');
        $this->storage = \Mockery::mock('\JHM\ContactStorageInterface');
        $this->storage->shouldReceive('isReady')->andReturn(false)->byDefault();
        $this->digest = \Mockery::mock('\JHM\MailDigestInterface');
        $this->digest->shouldReceive('writeMessage');
        $this->config = \Mockery::mock('\JHM\ConfigInterface');
        $this->config->shouldReceive('get')->byDefault()->andReturn(false);
        $this->logger = \Mockery::mock('\JHM\LoggerInterface');
        $this->logger->shouldReceive('log')->byDefault();
        $this->obj = new \JHM\CvHandler($this->mailer, $this->storage, $this->config, $this->digest, $this->logger);
    }

    protected function tearDown()
    {
        \Mockery::close();
    }

    public function testProcess()
    {
        $request = \Symfony\Component\HttpFoundation\Request::create(
            '/hello-world',
            'POST',
            array(
                'name' => 'Joe',
                'email' => 'joe@mail.com',
                'company' => 'RN Company',
                'phone' => '6127572323',
            )
        );
        $this->storage->shouldReceive('isReady')->andReturn(true);
        $this->config->shouldReceive('get')->with('webhost')->andReturn('www.example.com');
        $this->config->shouldReceive('get')->with('downloads.cvFileName')->andReturn('testFile.tst');
        $this->config->shouldReceive('get')->with('downloads.cvMimeType')->andReturn('text/domain');
        $this->mailer->shouldReceive('setupSystemMailer')->once();
        $this->mailer->shouldReceive('reset')->once();
        $this->mailer->shouldReceive('setSubject')->once();
        $this->mailer->shouldReceive('setFrom')->once()->with('joe@mail.com', 'Joe');
        $this->mailer->shouldReceive('setReplyTo')->once()->with('joe@mail.com', 'Joe');
        $this->mailer->shouldReceive('send')->once()->andReturn(true);
        $this->storage->shouldReceive('addContact')->once()->withArgs(array('joe@mail.com', 'Joe', 'RN Company', '6127572323'))->andReturn('32');
        $this->storage->shouldReceive('addDownloadRecord')->once()->with('32', 'joe@mail.com', 'testFile.tst', 'text/domain')->andReturn('213nkjn');
        $this->mailer->shouldReceive('setBody');
        $this->mailer->shouldReceive('setBody')->with('http://www.example.com/api?component=activateDownload&t=213nkjn');
        $this->storage->shouldReceive('close');
        $result = $this->obj->process($request);
        $this->assertEquals(200, $this->obj->status());
    }

    public function testFailSend()
    {
        $request = \Symfony\Component\HttpFoundation\Request::create(
            '/hello-world',
            'POST',
            array(
                'name' => 'Joe',
                'email' => 'joe@mail.com',
                'company' => 'RN Company',
                'phone' => '6127572323',
            )
        );
        $this->storage->shouldReceive('isReady')->andReturn(true);
        $this->storage->shouldReceive('removeDownloadToken')->with('213nkjn');
        $this->config->shouldReceive('get')->with('webhost')->andReturn('www.example.com');
        $this->config->shouldReceive('get')->with('downloads.cvFileName')->andReturn('testFile.tst');
        $this->config->shouldReceive('get')->with('downloads.cvMimeType')->andReturn('text/domain');
        $this->mailer->shouldReceive('setupSystemMailer')->once();
        $this->mailer->shouldReceive('reset')->once();
        $this->mailer->shouldReceive('setSubject')->once();
        $this->mailer->shouldReceive('setFrom')->once()->with('joe@mail.com', 'Joe');
        $this->mailer->shouldReceive('setReplyTo')->once()->with('joe@mail.com', 'Joe');
        $this->mailer->shouldReceive('send')->once()->andReturn(false);
        $this->storage->shouldReceive('addContact')->once()->withArgs(array('joe@mail.com', 'Joe', 'RN Company', '6127572323'))->andReturn('32');
        $this->storage->shouldReceive('addDownloadRecord')->once()->with('32', 'joe@mail.com', 'testFile.tst', 'text/domain')->andReturn('213nkjn');
        $this->mailer->shouldReceive('setBody');
        $this->mailer->shouldReceive('setBody')->with('http://www.example.com/api?component=activateDownload&t=213nkjn');
        $this->storage->shouldReceive('close');
        $result = $this->obj->process($request);
        $this->assertEquals(500, $this->obj->status());
    }

    public function testBadRequest()
    {
        $request = \Symfony\Component\HttpFoundation\Request::create(
            '/hello-world',
            'POST',
            array(
                'name' => 'Joe',
            )
        );
        $result = $this->obj->process($request);
        $this->assertEquals(400, $this->obj->status());
    }

    public function testHoneyPotFailure() // honey pot is populated then

    {
        $request = \Symfony\Component\HttpFoundation\Request::create(
            '/hello-world',
            'POST',
            array(
                'name' => 'Joe',
                'email' => 'james@mail.com',
                'screenName' => 'honey',
            )
        );
        $result = $this->obj->process($request);
        $this->assertEquals(400, $this->obj->status());
    }

    public function testInvalidEmail()
    {
        $request = \Symfony\Component\HttpFoundation\Request::create(
            '/hello-world',
            'POST',
            array(
                'name' => 'Joe',
                'email' => 'joe@mail',
            )
        );

        $result = $this->obj->process($request);
        $this->assertEquals(400, $this->obj->status());
    }

    public function testEmptyProp()
    {
        $request = \Symfony\Component\HttpFoundation\Request::create(
            '/hello-world',
            'POST',
            array(
                'email' => '',
            )
        );

        $result = $this->obj->process($request);
        $this->assertEquals(400, $this->obj->status());
    }

}
