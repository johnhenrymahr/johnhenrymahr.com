<?php
class ActivateDownloadHandlerTest extends \PHPUnit\Framework\TestCase {
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
        $this->obj = new \JHM\ActivateDownloadHandler($this->mailer, $this->fileLoader, $this->storage, $this->config);
    }

    protected function tearDown()
    {
        \Mockery::close();
    }

     public function testProcess()
    {
        $token = '21354325';
        $request = \Symfony\Component\HttpFoundation\Request::create(
            '/hello-world',
            'GET',
            array(
                'component' => 'activate',
                't' => $token
            )
        );
        $this->fileLoader->shouldReceive('load')->once()->with('cv.html')->andReturn('  <div>a test string. A url {{webhost}}/path/to?token={{token}}</div>');
        $this->storage->shouldReceive('isReady')->andReturn(true);
        $this->storage->shouldReceive('getInactiveToken')->with($token)->andReturn(array(
            "email" => 'joe@email.com',
            "name" => 'joe',
            "id" => '23'        
            ));
        $this->storage->shouldReceive('activateDownloadToken')->with('23')->andReturn(true);
        $this->mailer->shouldReceive('setupNoReply')->once();
        $this->mailer->shouldReceive('reset')->once();
        $this->mailer->shouldReceive('setSubject')->once();
        $this->mailer->shouldReceive('setRecipient')->once()->with('joe@email.com', 'joe');
        $this->mailer->shouldReceive('setHTML')->once()->with(true);
        $this->mailer->shouldReceive('send')->once()->andReturn(true);
        $this->config->shouldReceive('get')->with('webhost')->andReturn('www.example.com');
        $this->mailer->shouldReceive('setBody')->times(1)->with('<div>a test string. A url www.example.com/path/to?token='.$token.'</div>');
        $this->storage->shouldReceive('close');
        $result = $this->obj->process($request);
        $this->assertEquals(200, $this->obj->status());
    }

    public function testFailSend()
    {
        $token = '21354325';
        $request = \Symfony\Component\HttpFoundation\Request::create(
            '/hello-world',
            'GET',
            array(
                'component' => 'activate',
                't' => $token
            )
        );
        $this->fileLoader->shouldReceive('load')->once()->with('cv.html')->andReturn('  <div>a test string. A url {{webhost}}/path/to?token={{token}}</div>');
        $this->storage->shouldReceive('isReady')->andReturn(true);
        $this->storage->shouldReceive('getInactiveToken')->with($token)->andReturn(array(
            "email" => 'joe@email.com',
            "name" => 'joe',
            "id" => '23'        
            ));
        $this->storage->shouldReceive('activateDownloadToken')->with('23')->andReturn(true);
        $this->mailer->shouldReceive('setupNoReply')->once();
        $this->mailer->shouldReceive('reset')->once();
        $this->mailer->shouldReceive('setSubject')->once();
        $this->mailer->shouldReceive('setRecipient')->once()->with('joe@email.com', 'joe');
        $this->mailer->shouldReceive('setHTML')->once()->with(true);
        $this->mailer->shouldReceive('send')->once()->andReturn(false);
        $this->config->shouldReceive('get')->with('webhost')->andReturn('www.example.com');
        $this->mailer->shouldReceive('setBody')->times(1)->with('<div>a test string. A url www.example.com/path/to?token='.$token.'</div>');
        $this->storage->shouldReceive('close');
        $result = $this->obj->process($request);
        $this->assertEquals(500, $this->obj->status());
    }

    public function testBadRequest()
    {
        $request = \Symfony\Component\HttpFoundation\Request::create(
            '/hello-world',
            'GET',
            array(
                'component' => 'activate'
            )
        );
        $result = $this->obj->process($request);
        $this->assertEquals(400, $this->obj->status());
    }

}