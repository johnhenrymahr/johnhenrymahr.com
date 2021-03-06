<?php
class ContactHandlerTest extends \PHPUnit\Framework\TestCase
{

    protected $obj;

    protected $mailer;

    protected $digest;

    protected $fileLoader;

    protected $request;

    protected $storage;

    protected $config;

    protected $logger;

    protected $csrfMock;

    protected function setUp()
    {
        $this->mailer = \Mockery::mock('\JHM\MailerInterface');
        $this->csrfMock = \Mockery::mock('\JHM\CsrfTokenInterface');
        $this->csrfMock->shouldReceive('checkToken')->with('token', 'contact')->andReturn(true)->byDefault();
        $this->csrfMock->shouldReceive('getField')->andReturn('pr_id');
        $this->digest = \Mockery::mock('\JHM\MailDigestInterface');
        $this->fileLoader = \Mockery::mock('\JHM\FileLoaderInterface');
        $this->fileLoader->shouldReceive('load')->byDefault();
        $this->logger = \Mockery::mock('\JHM\LoggerInterface');
        $this->logger->shouldReceive('log');
        $this->request = \Mockery::mock('\Symfony\Component\HttpFoundation\Request');
        $this->storage = \Mockery::mock('\JHM\ContactStorageInterface');
        $this->storage->shouldReceive('isReady')->andReturn(false);
        $this->config = \Mockery::mock('\JHM\ConfigInterface');
        $this->config->shouldReceive('get')->byDefault()->andReturn(false);
        $this->obj = new \JHM\ContactHandler($this->mailer, $this->digest, $this->fileLoader, $this->storage, $this->config, $this->logger, $this->csrfMock);
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
                'pr_id' => 'token',
                'name' => 'Joe',
                'email' => 'joe@mail.com',
                'phoneNumber' => '212-323-2232',
                'company' => 'RN Company',
                'topic' => 'A test topic',
                'message' => 'a test message.',
            )
        );
        $this->mailer->shouldReceive('setupSystemMailer')->once();
        $this->mailer->shouldReceive('reset')->once();
        $this->mailer->shouldReceive('setupNoReply')->never();
        $this->mailer->shouldReceive('setReplyTo')->once();
        $this->mailer->shouldReceive('setSubject')->once();
        $this->mailer->shouldReceive('setBody')->times(6);
        $this->mailer->shouldReceive('send')->once()->andReturn(true);
        $this->mailer->shouldReceive('setupNoReply');
        $this->digest->shouldReceive('writeMessage')->once();
        $result = $this->obj->process($request);
        $this->assertEquals(200, $this->obj->status());
    }

    public function testProcessWithThankyou()
    {
        $this->fileLoader->shouldReceive('load')->with('thankYou.html')->once()->andReturn('a string');
        $request = \Symfony\Component\HttpFoundation\Request::create(
            '/hello-world',
            'POST',
            array(
                'pr_id' => 'token',
                'name' => 'Joe',
                'email' => 'joe@mail.com',
                'phoneNumber' => '212-323-2232',
                'company' => 'RN Company',
                'topic' => 'A test topic',
                'message' => 'a test message.',
            )
        );
        $this->mailer->shouldReceive('setupSystemMailer')->once();
        $this->mailer->shouldReceive('reset')->twice();
        $this->mailer->shouldReceive('setHTML')->once()->with(true);
        $this->mailer->shouldReceive('setReplyTo')->once();
        $this->mailer->shouldReceive('setSubject')->twice();
        $this->mailer->shouldReceive('setRecipient')->with('joe@mail.com', 'Joe')->once();
        $this->mailer->shouldReceive('setBody')->times(7);
        $this->mailer->shouldReceive('send')->twice()->andReturn(true);
        $this->mailer->shouldReceive('setupNoReply');
        $this->digest->shouldReceive('writeMessage')->once();
        $this->config->shouldReceive('get')->andReturn(true);
        $result = $this->obj->process($request);
        $this->assertEquals(200, $this->obj->status());
    }

    public function testFailProcess()
    {
        $request = \Symfony\Component\HttpFoundation\Request::create(
            '/hello-world',
            'POST',
            array(
                'pr_id' => 'token',
                'name' => 'Joe',
                'email' => 'joe@mail.com',
                'phoneNumber' => '212-323-2232',
                'company' => 'RN Company',
                'topic' => 'A test topic',
                'message' => 'a test message.',
            )
        );
        $this->mailer->shouldReceive('setupSystemMailer')->once();
        $this->mailer->shouldReceive('reset')->once();
        $this->mailer->shouldReceive('setReplyTo')->once();
        $this->mailer->shouldReceive('setSubject')->once();
        $this->mailer->shouldReceive('setBody')->times(6);
        $this->mailer->shouldReceive('send')->once()->andReturn(false);
        $this->digest->shouldReceive('writeMessage')->once();
        $result = $this->obj->process($request);
        $this->assertEquals(500, $this->obj->status());
    }

    public function testBadRequest()
    {

        $this->csrfMock->shouldReceive('checkToken')->with(null, 'contact')->andReturn(true);
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

    public function testInvalidCsrfToken()
    {

        $this->csrfMock->shouldReceive('checkToken')->with('badtoken', 'contact')->andReturn(false);
        $request = \Symfony\Component\HttpFoundation\Request::create(
            '/hello-world',
            'POST',
            array(
                'pr_id' => 'badtoken',
                'name' => 'Joe',
                'email' => 'james@mail.com',
                'topic' => 'test topic',
                'message' => "a test message",
                'screenName' => 'honey',
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
                'pr_id' => 'token',
                'name' => 'Joe',
                'email' => 'james@mail.com',
                'topic' => 'test topic',
                'message' => "a test message",
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
                'pr_id' => 'token',
                'name' => 'Joe',
                'email' => 'joe@mail',
                'phoneNumber' => '212-323-2232',
                'company' => 'RN Company',
                'topic' => 'A test topic',
                'message' => 'a test message.',
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
                'pr_id' => 'token',
                'name' => 'Joe',
                'email' => 'joe@mail',
                'phoneNumber' => '212-323-2232',
                'company' => 'RN Company',
                'topic' => 'A test topic',
                'message' => '',
            )
        );

        $result = $this->obj->process($request);
        $this->assertEquals(400, $this->obj->status());
    }

}
