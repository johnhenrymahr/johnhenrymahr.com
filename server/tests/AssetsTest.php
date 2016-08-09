<?php

class AssetsTest extends \PHPUnit\Framework\TestCase
{

    protected $obj;

    protected $fileLoaderMock;

    protected $configMock;

    protected function setUp()
    {
        $dataPath = realpath(__DIR__) . '/data/webpack-assets.json';
        $json = json_decode(file_get_contents($dataPath), true);
        if (!is_array($json)) {
            throw new Exception('Assets Test: data not ready. Data path: ' . $dataPath);
        }
        $this->fileLoaderMock = \Mockery::mock('\JHM\FileLoaderInterface');
        $this->fileLoaderMock
            ->shouldReceive('load')
            ->with('webpack-assets.json', true)
            ->once()
            ->andReturn($json)
            ->byDefault();
        $this->configMock = \Mockery::mock('\JHM\ConfigInterface');
        $this->configMock
            ->shouldReceive('get')
            ->with('assetroot')
            ->andReturn('rsc/')
            ->byDefault();

        $this->obj = new \JHM\Assets($this->fileLoaderMock, $this->configMock);
    }
    protected function tearDown()
    {
        \Mockery::close();
    }

    public function testGet() {
        $this->assertEquals('rsc/js/app_980baf01023cff785b9ebundle.js', $this->obj->get('js'));
        $this->assertEquals('rsc/css/980baf01023cff785b9estyles.css', $this->obj->get('CSS'));
    }

    public function testCustomRoot() {
        $this->configMock
            ->shouldReceive('get')
            ->with('assetroot')
            ->andReturn('public/');
        $this->assertEquals('public/js/app_980baf01023cff785b9ebundle.js', $this->obj->get('js'));
    }
    public function testNullKey() {
         $this->assertEquals('', $this->obj->get('kt'));
    }
    public function testThrowForInvalidData() {
        $this->expectException(\JHM\JhmException::class);
        $this->fileLoaderMock
            ->shouldReceive('load')
            ->with('webpack-assets.json', true)
            ->once()
            ->andReturn(['nothing' => 'here']);

        $obj = new \JHM\Assets($this->fileLoaderMock, $this->configMock);    
    }

}
