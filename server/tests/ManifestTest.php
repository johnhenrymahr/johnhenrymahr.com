<?php

class ManifestTest extends \PHPUnit\Framework\TestCase
{

    protected $obj;

    protected $fileLoader;

    protected function setUp()
    {
        $dataPath = realpath(__DIR__) . '/data/mockManifest.json';
        $json = json_decode(file_get_contents($dataPath), true);
        if (!is_array($json)) {
            throw new Exception('Manifest Test: data not ready. Data path: ' . $dataPath);
        }
        $this->fileLoader = \Mockery::mock('\JHM\FileLoaderInterface');
        $this->fileLoader->shouldReceive('load')->with('viewManifest.json', true)->andReturn($json)->byDefault();

        $this->obj = new \JHM\Manifest($this->fileLoader);
    }
    protected function tearDown()
    {
        \Mockery::close();
    }
    public function testException () {
        $this->fileLoader->shouldReceive('load')->with('viewManifest.json', true)->andReturn('{}');
        $this->expectException('\JHM\JhmException');
        $obj = new \JHM\Manifest($this->fileLoader);
    }
    public function testGetter()
    {
        $this->assertEquals('mainTpl.dust', $this->obj->template);
    }
    public function testGetSections()
    {
        $this->assertTrue(is_array($this->obj->getSections()));
    }
    public function testGetChildren()
    {
        $section = [];
        $this->assertTrue(is_array($this->obj->getChildren($section)));
    }
}
