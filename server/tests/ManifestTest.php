<?php

class ManifestTest extends \PHPUnit\Framework\TestCase
{

    protected $obj;

    protected function setUp()
    {
        $dataPath = realpath(__DIR__) . '/data/mockManifest.json';
        $json = json_decode(file_get_contents($dataPath), true);
        if (!is_array($json)) {
            throw new Exception('Manifest Test: data not ready. Data path: ' . $dataPath);
        }
        $fileLoaderMock = \Mockery::mock('\JHM\FileLoaderInterface');
        $fileLoaderMock->shouldReceive('load')->with('manifest.json', true)->once()->andReturn($json);

        $this->obj = new \JHM\Manifest($fileLoaderMock);
    }
    protected function tearDown()
    {
        \Mockery::close();
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
