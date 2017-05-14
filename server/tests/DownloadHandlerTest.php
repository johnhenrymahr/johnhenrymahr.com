<?php
class DownloadHandlerTest extends \PHPUnit\Framework\TestCase
{

    protected $config;

    protected $storage;

    protected $hash;

    protected $ga;

    protected $obj;

    protected $root;

    protected function setUp()
    {
        $this->config = \Mockery::mock('\JHM\ConfigInterface');
        $this->root = \org\bovigo\vfs\vfsStream::setup('downloads');
        $this->config->shouldReceive('getStorage')->with('downloads')->andReturn($this->root->url() . '/');
        $this->storage = \Mockery::mock('\JHM\ContactStorageInterface');
        $this->ga = \Mockery::mock('\JHM\GA');
        $this->ga->shouldReceive('init');
        $this->hash = \Mockery::mock('\JHM\Hash');
        $this->obj = new \JHM\DownloadHandler($this->storage, $this->config, $this->ga, $this->hash);
    }

    protected function tearDown()
    {
        \Mockery::close();
    }

    public function testBadRequest()
    {
        $request = \Symfony\Component\HttpFoundation\Request::create(
            '/hello-world',
            'GET',
            array(
            )
        );
        $this->obj->process($request);
        $this->assertEquals('400', $this->obj->status());
    }

    public function testInvalidToken()
    {
        $request = \Symfony\Component\HttpFoundation\Request::create(
            '/hello-world',
            'GET',
            array(
                't' => 'fsdfse32',
            )
        );
        $this->storage->shouldReceive('validateDownloadToken')->with('fsdfse32')->andReturn([]);
        $this->obj->process($request);
        $this->assertEquals('412', $this->obj->status());
    }

    public function testDownloadNotFound()
    {
        $request = \Symfony\Component\HttpFoundation\Request::create(
            '/hello-world',
            'GET',
            array(
                't' => 'fsdfse32',
            )
        );
        $data = array(
            'fileId' => 'testFile',
            'md5_hash' => '123',
            'fileMimeType' => 'text/test',
        );
        $this->storage->shouldReceive('validateDownloadToken')->with('fsdfse32')->andReturn($data);
        $this->obj->process($request);
        $this->assertEquals('500', $this->obj->status());

    }

    public function testHashMiss()
    {
        $request = \Symfony\Component\HttpFoundation\Request::create(
            '/hello-world',
            'GET',
            array(
                't' => 'fsdfse32',
            )
        );
        $data = array(
            'fileId' => 'testFile',
            'md5_hash' => '123',
            'fileMimeType' => 'text/test',
        );
        \org\bovigo\vfs\vfsStream::newFile('testFile')->at($this->root);
        $this->storage->shouldReceive('validateDownloadToken')->with('fsdfse32')->andReturn($data);
        $this->hash->shouldReceive('md5File')->andReturn('321');
        $this->obj->process($request);
        $this->assertEquals('401', $this->obj->status());

    }

    public function testProcess()
    {
        $request = \Symfony\Component\HttpFoundation\Request::create(
            '/hello-world',
            'GET',
            array(
                't' => 'fsdfse32',
            )
        );
        $data = array(
            'fileId' => 'testFile',
            'md5_hash' => '123',
            'fileMimeType' => 'text/test',
        );
        \org\bovigo\vfs\vfsStream::newFile('testFile')->at($this->root);
        $this->storage->shouldReceive('validateDownloadToken')->with('fsdfse32')->andReturn($data);
        $this->hash->shouldReceive('md5File')->andReturn('123');
        $this->obj->process($request);
        $this->assertEquals('200', $this->obj->status());
    }

    public function testCallback()
    {
        $request = \Symfony\Component\HttpFoundation\Request::create(
            '/hello-world',
            'GET',
            array(
                't' => 'fsdfse32',
            )
        );
        $data = array(
            'fileId' => 'testFile',
            'md5_hash' => '123',
            'fileMimeType' => 'text/test',
        );
        \org\bovigo\vfs\vfsStream::newFile('testFile')->at($this->root);
        $this->storage->shouldReceive('validateDownloadToken')->with('fsdfse32')->andReturn($data);
        $this->hash->shouldReceive('md5File')->andReturn('123');
        $this->ga->shouldReceive('trackPageHit')->with('fsdfse32', 'JohnHenryMahr: Download a File');
        $logger = Mockery::mock('\JHM\Logger');
        $api = new \JHM\Api($logger);
        $api->defaultHandler($this->obj);
        $api->init($request);
        $response = $api->respond();
        $this->assertTrue(is_object($response));
    }

}
