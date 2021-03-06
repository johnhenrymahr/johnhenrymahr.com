<?php
define('SERVER_ROOT', realpath(__DIR__ . '/data') . '/');

class ConfigTest extends \PHPUnit\Framework\TestCase
{
    protected $obj;

    protected function setUp()
    {
        $this->obj = new \JHM\Config('faketestdomain.net', '', false);
    }
    protected function tearDown()
    {
        \Mockery::close();
    }

    public function testSimpleGetter()
    {
        $this->assertEquals('tester/', $this->obj->get('testroot'));
    }

    public function testCompoundGetter()
    {
        $this->assertEquals('bar', $this->obj->get('files.foo'));
    }

    public function testResolveDustPath()
    {
        $this->assertEquals('app/dust/file1.dust', $this->obj->resolvePath('file1.dust'));
    }

    public function testResolveDataPath()
    {
        $this->assertEquals('data/file1.json', $this->obj->resolvePath('file1.json'));
    }

    public function testSetMethod()
    {
        $this->obj->set('foo1', 'bar2');
        $this->assertEquals('bar2', $this->obj->get('foo1'));
    }
    public function testDeTokenizer()
    {
        $this->obj->set('basepath', 'var/web/');
        $this->assertEquals('var/web/data/file1.json', $this->obj->resolvePath('file1.json'));
    }

    public function testLiveConfig()
    {
        $this->assertTrue($this->obj->usingLiveConfig);
        $this->assertTrue($this->obj->get('flags.testflag'));
    }

    public function testGetPageState()
    {
        $this->assertEquals('up', $this->obj->get('pagestate.homepage'));
    }
}
