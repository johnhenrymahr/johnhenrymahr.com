<?php
class FileLoaderTest extends \PHPUnit\Framework\TestCase
{
    protected $configMock;

    protected $obj;

    protected $root;

    protected function setUp()
    {
        $this->configMock = \Mockery::mock('\JHM\ConfigInterface');
        $this->root = \org\bovigo\vfs\vfsStream::setup('server');
        $this->obj = new \JHM\FileLoader($this->configMock);
    }

    protected function tearDown()
    {
        \Mockery::close();
    }

    public function testLoadDataSuccess()
    {
        $rawData = '{"foo": "bar"}';
        $expected = ["foo" => "bar"];

        $dir = \org\bovigo\vfs\vfsStream::newDirectory('data')->at($this->root);
        $file = \org\bovigo\vfs\vfsStream::newFile('manifest.json')->at($dir)->setContent($rawData);

        $this->configMock
            ->shouldReceive('resolvePath')
            ->with('manifest.json')
            ->once()
            ->andReturn($file->url());

        $result = $this->obj->load('manifest.json');
        $this->assertEquals($expected, $result);
    }

    public function testLoadDataStrictFailure()
    {

        $this->expectException(\JHM\JhmException::class);

        $this->configMock
            ->shouldReceive('resolvePath')
            ->with('manifest.json')
            ->once()
            ->andReturn('path/to/nothing');

        $this->obj->load('manifest.json', true);
    }

    public function testLoadDataFailure()
    {

        $this->configMock
            ->shouldReceive('resolvePath')
            ->with('manifest.json')
            ->once()
            ->andReturn('path/to/nothing');

        $result = $this->obj->load('manifest.json', false);
        $this->assertEquals(false, $result);
    }

    public function testParseDataFailure()
    {
        $rawData = '{foo": "bar"}';

        $dir = \org\bovigo\vfs\vfsStream::newDirectory('data')->at($this->root);
        $file = \org\bovigo\vfs\vfsStream::newFile('manifest.json')->at($dir)->setContent($rawData);

        $this->configMock
            ->shouldReceive('resolvePath')
            ->with('manifest.json')
            ->once()
            ->andReturn($file->url());

        $result = $this->obj->load('manifest.json', false);
        $this->assertEquals(false, $result);
    }

    public function testParseDataFailureCustomDefault()
    {
        $rawData = '{foo": "bar"}';

        $dir = \org\bovigo\vfs\vfsStream::newDirectory('data')->at($this->root);
        $file = \org\bovigo\vfs\vfsStream::newFile('manifest.json')->at($dir)->setContent($rawData);

        $this->configMock
            ->shouldReceive('resolvePath')
            ->with('manifest.json')
            ->once()
            ->andReturn($file->url());

        $result = $this->obj->load('manifest.json', false, []);
        $this->assertEquals([], $result);
        $this->assertTrue(empty($result));
    }

    public function testParseDataStrictFailure()
    {
        $this->expectException(\JHM\JhmException::class);
        $rawData = '{foo": "bar"}';

        $dir = \org\bovigo\vfs\vfsStream::newDirectory('data')->at($this->root);
        $file = \org\bovigo\vfs\vfsStream::newFile('manifest.json')->at($dir)->setContent($rawData);

        $this->configMock
            ->shouldReceive('resolvePath')
            ->with('manifest.json')
            ->once()
            ->andReturn($file->url());

        $result = $this->obj->load('manifest.json', true);
    }

    public function testLoadYamlConfigSuccess()
    {
        $expected = ['foo' => 'bar'];
        $rawData = "foo: bar";
        $dir = \org\bovigo\vfs\vfsStream::newDirectory('cfg')->at($this->root);
        $file = \org\bovigo\vfs\vfsStream::newFile('cfg.yaml')->at($dir)->setContent($rawData);

        $this->configMock
            ->shouldReceive('resolvePath')
            ->with('cfg.yaml')
            ->once()
            ->andReturn($file->url());

        $result = $this->obj->load('cfg.yaml');
        $this->assertEquals($expected, $result);
    }

    public function testLoadYmlConfigSuccess()
    {
        $expected = ['foo' => 'bar'];
        $rawData = "foo: bar";
        $dir = \org\bovigo\vfs\vfsStream::newDirectory('cfg')->at($this->root);
        $file = \org\bovigo\vfs\vfsStream::newFile('cfg.yml')->at($dir)->setContent($rawData);

        $this->configMock
            ->shouldReceive('resolvePath')
            ->with('cfg.yml')
            ->once()
            ->andReturn($file->url());

        $result = $this->obj->load('cfg.yml');
        $this->assertEquals($expected, $result);
    }

    public function testLoadListConfigSuccess()
    {
        $expected = ['foo', 'bar'];
        $rawData = "foo\nbar";
        $dir = \org\bovigo\vfs\vfsStream::newDirectory('cfg')->at($this->root);
        $file = \org\bovigo\vfs\vfsStream::newFile('cfg.list')->at($dir)->setContent($rawData);

        $this->configMock
            ->shouldReceive('resolvePath')
            ->with('cfg.list')
            ->once()
            ->andReturn($file->url());

        $result = $this->obj->load('cfg.list');
        $this->assertEquals($expected, $result);
    }

    public function testLoadListConfigLoadFail()
    {
        $expected = false;
        $rawData = "foo\nbar";
        $dir = \org\bovigo\vfs\vfsStream::newDirectory('cfg')->at($this->root);
        $file = \org\bovigo\vfs\vfsStream::newFile('cfg.list')->at($dir)->setContent($rawData);

        $this->configMock
            ->shouldReceive('resolvePath')
            ->with('cfg.list')
            ->once()
            ->andReturn('/path/to/nothing');

        $result = $this->obj->load('cfg.list');
        $this->assertEquals($expected, $result);
    }

    public function testLoadListConfigBlankLines()
    {
        $expected = ['foo', 'bar', 'test'];
        $rawData = "foo\n\n\nbar\ntest";
        $dir = \org\bovigo\vfs\vfsStream::newDirectory('cfg')->at($this->root);
        $file = \org\bovigo\vfs\vfsStream::newFile('cfg.list')->at($dir)->setContent($rawData);

        $this->configMock
            ->shouldReceive('resolvePath')
            ->with('cfg.list')
            ->once()
            ->andReturn($file->url());

        $result = $this->obj->load('cfg.list');
        $this->assertEquals($expected, $result);
    }

    public function testLoadYamlConfigParseFailure()
    {
        $expected = false;
        $rawData = "foo:bar\r\n$$";
        $dir = \org\bovigo\vfs\vfsStream::newDirectory('cfg')->at($this->root);
        $file = \org\bovigo\vfs\vfsStream::newFile('cfg.yaml')->at($dir)->setContent($rawData);

        $this->configMock
            ->shouldReceive('resolvePath')
            ->with('cfg.yaml')
            ->once()
            ->andReturn($file->url());

        $result = $this->obj->load('cfg.yaml');
        $this->assertEquals($expected, $result);
    }

    public function testLoadConfigFileSuccess()
    {
        $expected = ['foo' => 'bar'];
        $rawData = "foo=bar";

        $dir = \org\bovigo\vfs\vfsStream::newDirectory('cfg')->at($this->root);
        $file = \org\bovigo\vfs\vfsStream::newFile('conftst.ini')->at($dir)->setContent($rawData);

        $this->configMock
            ->shouldReceive('resolvePath')
            ->with('conftst.ini')
            ->once()
            ->andReturn($file->url());

        $result = $this->obj->load('conftst.ini');
        $this->assertEquals($expected, $result);
    }

    public function testLoadConfigFileFailure()
    {
        $expected = ['foo' => 'bar'];
        $rawData = "foo=bar";

        $dir = \org\bovigo\vfs\vfsStream::newDirectory('cfg')->at($this->root);
        $file = \org\bovigo\vfs\vfsStream::newFile('conftst.ini')->at($dir)->setContent($rawData);

        $this->configMock
            ->shouldReceive('resolvePath')
            ->with('conftst.ini')
            ->once()
            ->andReturn('/path/to/nothing');

        $result = $this->obj->load('conftst.ini');
        $this->assertEquals(false, $result);
    }

    public function testLoadConfigFileStrictFailure()
    {
        $this->expectException(\JHM\JhmException::class);

        $expected = ['foo' => 'bar'];
        $rawData = "foo=bar";

        $dir = \org\bovigo\vfs\vfsStream::newDirectory('cfg')->at($this->root);
        $file = \org\bovigo\vfs\vfsStream::newFile('conftst.ini')->at($dir)->setContent($rawData);

        $this->configMock
            ->shouldReceive('resolvePath')
            ->with('conftst.ini')
            ->once()
            ->andReturn('/path/to/nothing');

        $result = $this->obj->load('conftst.ini', true);
    }

    public function testParseConfigFailure()
    {
        $rawData = "foo==bar";

        $dir = \org\bovigo\vfs\vfsStream::newDirectory('cfg')->at($this->root);
        $file = \org\bovigo\vfs\vfsStream::newFile('conftst.ini')->at($dir)->setContent($rawData);

        $this->configMock
            ->shouldReceive('resolvePath')
            ->with('conftst.ini')
            ->once()
            ->andReturn($file->url());

        $result = $this->obj->load('conftst.ini', false);
        $this->assertEquals(false, $result);
    }

    public function testParseConfigStrictFailure()
    {
        $this->expectException(\JHM\JhmException::class);
        $rawData = "foo==bar";

        $dir = \org\bovigo\vfs\vfsStream::newDirectory('cfg')->at($this->root);
        $file = \org\bovigo\vfs\vfsStream::newFile('conftst.ini')->at($dir)->setContent($rawData);

        $this->configMock
            ->shouldReceive('resolvePath')
            ->with('conftst.ini')
            ->once()
            ->andReturn($file->url());

        $result = $this->obj->load('conftst.ini', true);
    }

}
