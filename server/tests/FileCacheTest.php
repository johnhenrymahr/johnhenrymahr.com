<?php
class FileCacheTest extends \PHPUnit\Framework\TestCase
{
    protected $obj;

    protected $cachePath;

    protected $loggerMock;

    protected $configMock;

    protected function setUp()
    {

        $this->cachePath = realpath(__DIR__ . '/data') . '/storage/cache';
        if (!file_exists($this->cachePath)) {
            mkdir($this->cachePath, 0777, true);
        }
        $this->loggerMock = \Mockery::mock('\JHM\LoggerInterface');
        $this->configMock = \Mockery::mock('\JHM\ConfigInterface');
        $this->configMock->shouldReceive('getStorage')->with('filecache')->andReturn($this->cachePath);
        $this->obj = new \JHM\FileCache($this->configMock, $this->loggerMock);

    }

    protected function tearDown()
    {
        $storagePath = dirname($this->cachePath);
        if (file_exists($storagePath)) {
            system('rm -rf ' . escapeshellarg($storagePath), $retval);
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

    public function testDirCreate()
    {
        $cachePath = dirname($this->cachePath);
        if (!file_exists($cachePath)) {
            mkdir($cachePath, 0777);
        }
        $cachePath = $cachePath . '/cachedir';
        $configMock = \Mockery::mock('\JHM\ConfigInterface');
        $configMock->shouldReceive('getStorage')->with('filecache')->andReturn($cachePath);
        $obj = new \JHM\FileCache($configMock, $this->loggerMock);
        $obj->set('foo', 'bar');
        $obj->save();
        $this->assertTrue(file_exists($cachePath));
    }

    public function testNullGet()
    {
        $result = $this->obj->get('foo');
        $this->assertEmpty($result);
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
