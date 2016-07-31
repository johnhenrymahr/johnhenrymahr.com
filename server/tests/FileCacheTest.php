<?php
class FileCacheTest extends \PHPUnit\Framework\TestCase
{
    protected $obj;

    protected $cachePath;

    protected $loggerMock;

    protected $configMock;

    protected function setUp()
    {

        $this->cachePath = realpath(__DIR__ . '/data/') . '/cache';
        if (!file_exists($this->cachePath)) {
            mkdir($this->cachePath);
        }
        $this->loggerMock = \Mockery::mock('\JHM\LoggerInterface');
        $this->configMock = \Mockery::mock('\JHM\ConfigInterface');
        $this->configMock->shouldReceive('getStorage')->with('filecache')->andReturn($this->cachePath);
        $this->obj = new \JHM\FileCache($this->configMock, $this->loggerMock);

    }

    protected function tearDown()
    {
        if (file_exists($this->cachePath)) {
            system('rm -rf ' . escapeshellarg($this->cachePath), $retval);
        }
    }

    public function testInstance()
    {
        $this->assertTrue(is_object($this->obj));
        $this->assertTrue($this->obj->cacheReady());
    }

    public function testInstanceFallback()
    {
        $this->configMock->shouldReceive('getStorage')->with('filecache')->andReturn('/path/to/nothing');
        $obj = new \JHM\FileCache($this->configMock, $this->loggerMock);
        $this->assertTrue(is_object($obj));
        $this->assertTrue($obj->cacheReady());
    }

    public function testNullGet()
    {
        $result = $this->obj->get('foo');
        $this->assertNull($result);
    }

    public function testSave()
    {
        $this->obj->set('foo', 'bar');
        $this->obj->save();
    }

    public function testGet()
    {
        $this->obj->set('foo', 'bar');
        $this->obj->save();
        $this->obj = null;
        $obj = new \JHM\FileCache($this->configMock, $this->loggerMock);
        $value = $obj->get('foo');
        $this->assertEquals('bar', $value);
    }

}
