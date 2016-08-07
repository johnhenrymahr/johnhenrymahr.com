<?php
class OuputTest extends \PHPUnit\Framework\TestCase
{
    protected $obj;

    protected $callback;

    protected $cacheMock;

    protected function setUp()
    {
        $this->cacheMock = \Mockery::mock('\JHM\CacheInterface');
        $this->cacheMock->shouldReceive('cacheReady')->andReturn(true)->byDefault();
        $this->cacheMock->shouldReceive('get')->with('key1')->andReturn('')->byDefault();
        $this->cacheMock->shouldReceive('set')->once()->byDefault();
        $this->cacheMock->shouldReceive('save')->once()->byDefault();
        $this->obj = new \JHM\Output($this->cacheMock);
    }
    protected function tearDown()
    {
        \Mockery::close();
    }

    public function testOutputString()
    {
        $this->cacheMock->shouldReceive('set')->once()->with('key1', 'Test string');
        $obj = new TestContainer();
        $ref = $this->obj;
        $result = $ref(array($obj, 'stringCallable'), 'key1');
        $this->assertEquals('Test string', $result);
        $obj = '';
    }

    public function testOutputArray()
    {
        $obj = new TestContainer();
        $this->cacheMock->shouldReceive('set')->once()->with('key1', $obj->arrayCallable());
        $ref = $this->obj;
        $result = $ref(array($obj, 'arrayCallable'), 'key1')->toJSON();
        $this->assertEquals('[{"foo":"bar"},{"foo2":"bar2"}]', $result);
        $obj = '';
    }

    public function testOutputCache()
    {
        $this->cacheMock->shouldReceive('get')->with('key2')->andReturn('a cached test string');
        $this->cacheMock->shouldReceive('set')->never();
        $this->cacheMock->shouldReceive('save')->never();
        $c = new TestContainer();
        $obj = $this->obj;
        $result = $obj(array($c, 'stringCallable'), 'key2')->toString();
        $this->assertEquals('a cached test string', $result);
        $obj = '';
        $c = '';
    }

    public function testCacheDisabled()
    {
        $this->cacheMock->shouldReceive('set')->never();
        $this->cacheMock->shouldReceive('save')->never();
        $this->cacheMock->shouldReceive('get')->never();
        $this->cacheMock->shouldReceive('cacheReady')->andReturn(false);
        $c = new TestContainer();
        $obj = new \JHM\Output($this->cacheMock);
        $result = $obj(array($c, 'stringCallable'), 'key2')->toString();
        $this->assertEquals('Test string', $result);
        $obj = '';
        $c = '';
    }

    public function testNoCacheKey()
    {
        $this->cacheMock->shouldReceive('set')->never();
        $this->cacheMock->shouldReceive('save')->never();
        $this->cacheMock->shouldReceive('get')->never();
        $c = new TestContainer();
        $obj = $this->obj;
        $result = $obj(array($c, 'stringCallable'))->toString();
        $this->assertEquals('Test string', $result);
        $obj = '';
        $c = '';
    }
}
class TestContainer
{
    public function stringCallable()
    {
        return 'Test string';
    }

    public function arrayCallable()
    {
        return [['foo' => 'bar'], ['foo2' => 'bar2']];
    }
}
