<?php
class CvHandlerTest extends \PHPUnit\Framework\TestCase
{

    protected $obj;

    protected $mailer;

    protected $fileLoader;

    protected $request;

    protected $storage;

    protected $config;

    protected function setUp()
    {
        $this->mailer = \Mockery::mock('\JHM\MailerInterface');
        $this->fileLoader = \Mockery::mock('\JHM\FileLoaderInterface');
        $this->fileLoader->shouldReceive('load')->byDefault();
        $this->request = \Mockery::mock('\Symfony\Component\HttpFoundation\Request');
        $this->storage = \Mockery::mock('\JHM\ContactStorageInterface');
        $this->storage->shouldReceive('isReady')->andReturn(false)->byDefault();
        $this->config = \Mockery::mock('\JHM\ConfigInterface');
        $this->config->shouldReceive('get')->byDefault()->andReturn(false);
        $this->obj = new \JHM\CvHandler($this->mailer, $this->fileLoader, $this->storage, $this->config);
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
            )
        );
        $this->fileLoader->shouldReceive('load')->once()->with('cv.html')->andReturn('  <div>a test string. A url /path/to?token={{token}}</div>');
        $this->storage->shouldReceive('isReady')->andReturn(true);
        $this->mailer->shouldReceive('setupNoReply')->once();
        $this->mailer->shouldReceive('reset')->once();
        $this->mailer->shouldReceive('setSubject')->once();
        $this->mailer->shouldReceive('setHTML')->once()->with(true);
        $this->mailer->shouldReceive('send')->once()->andReturn(true);
        $this->config->shouldReceive('get')->with('downloads.cvFileName')->andReturn('testFile.tst');
        $this->storage->shouldReceive('addContact')->once()->withArgs(array('joe@mail.com', 'Joe', 'RN Company'))->andReturn('32');
        $this->storage->shouldReceive('addDownloadRecord')->once()->with('32', 'joe@mail.com', 'testFile.tst')->andReturn('213nkjn');
        $this->mailer->shouldReceive('setBody')->times(1)->with('<div>a test string. A url /path/to?token=213nkjn</div>');
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
            )
        );
        $this->fileLoader->shouldReceive('load')->once()->with('cv.html')->andReturn('  <div>a test string. A url /path/to?token={{token}}</div>');
        $this->storage->shouldReceive('isReady')->andReturn(true);
        $this->mailer->shouldReceive('setupNoReply')->once();
        $this->mailer->shouldReceive('reset')->once();
        $this->mailer->shouldReceive('setSubject')->once();
        $this->mailer->shouldReceive('setHTML')->once()->with(true);
        $this->mailer->shouldReceive('send')->once()->andReturn(false);
        $this->config->shouldReceive('get')->with('downloads.cvFileName')->andReturn('testFile.tst');
        $this->storage->shouldReceive('addContact')->once()->withArgs(array('joe@mail.com', 'Joe', 'RN Company'))->andReturn('32');
        $this->storage->shouldReceive('addDownloadRecord')->once()->with('32', 'joe@mail.com', 'testFile.tst')->andReturn('213nkjn');
        $this->mailer->shouldReceive('setBody')->times(1)->with('<div>a test string. A url /path/to?token=213nkjn</div>');
        $this->storage->shouldReceive('removeDownloadToken')->once()->with('213nkjn');
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
