<?php
class ConfigTest extends \PHPUnit\Framework\TestCase
{
    protected $obj;

    protected function setUp()
    {
        $this->obj = new \JHM\Config('test', '', false);
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
        $this->assertEquals('dust/file1.dust', $this->obj->resolvePath('file1.dust'));
    }

    public function testResolveDataPath()
    {
        $this->assertEquals('data/file1.json', $this->obj->resolvePath('file1.json'));
    }

    public function testCustomHostConfig()
    {
        $o = new \JHM\Config('arg1', ['arg1' => ["files" => ["dust" => 'tester/one/', "foo4" => "bar5"]]]);
        $this->assertEquals('tester/one/file1.dust', $o->resolvePath('file1.dust'));
        $this->assertEquals('bar5', $o->get('files.foo4'));
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
}
