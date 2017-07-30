<?php
class GraphTest extends \PHPUnit\Framework\TestCase
{

    protected $obj;

    protected function setUp()
    {
        $this->obj = new \JHM\Graph();
    }

    protected function tearDown()
    {
        \Mockery::close();
    }

    protected function mockDeps($className, $interface, $property, \Mockery\MockInterface $mock)
    {

        $map = [
            $interface => [
                "instance" => function () use ($mock) {
                    return $mock;
                },
            ],
        ];

        $this->obj->addRule($className, [], [$property => $map]);

    }

    public function testAssemberGet()
    {
        $dataPath = realpath(__DIR__) . '/data/mockManifest.json';
        $config = [
            "testconfig" => [
                "flags" => [
                    "loggingEnabled" => false,
                ],
            ],
        ];
        $json = json_decode(file_get_contents($dataPath), true);
        if (!is_array($json)) {
            throw new Exception('Manifest Test: data not ready. Data path: ' . $dataPath);
        }
        $fileLoaderMock = \Mockery::mock('JHM\\FileLoaderInterface')
            ->shouldReceive('load')
            ->with('viewManifest.json', true)
            ->once()
            ->andReturn($json)
            ->getMock();

        $this->mockDeps('Manifest', 'JHM\\FileLoaderInterface', 'substitutions', $fileLoaderMock);
        $this->obj->addRule('Logger', [], ['constructParams' => ['testconfig', $config]]);
        $assembler = $this->obj->get('Assembler');
        $this->AssertInstanceOf(\JHM\Assembler::class, $assembler);
        $clone = (array) $assembler;
        $this->assertEquals("\0*\0manifest", array_Keys($clone)[0]); // protected propertie keys get prepended with \0*\0 when cast to Array, private \0Class_name\0
        $this->assertInstanceOf(\JHM\TemplateFactoryInterface::class, $clone["\0*\0templateFactory"]);
        $this->assertInstanceOf(\JHM\ManifestInterface::class, $clone["\0*\0manifest"]);
    }

    public function testDataProviderGet()
    {
        $fileLoaderMock = \Mockery::mock('JHM\\FileLoaderInterface');
        $loggerMock = \Mockery::mock('JHM\\LoggerInterface');
        $this->mockDeps('Config', 'JHM\\FileLoaderInterface', 'substitutions', $fileLoaderMock);
        $this->mockDeps('Config', 'JHM\\LoggerInterface', 'substitutions', $loggerMock);
        $dataProvider = $this->obj->get('DataProvider');
        $this->assertInstanceOf(\JHM\DataProvider::class, $dataProvider);
        $clone = (array) $dataProvider;
        $this->assertInstanceOf(\JHM\LoggerInterface::class, $clone["\0*\0logger"]);
        $this->assertInstanceOf(\JHM\FileLoaderInterface::class, $clone["\0*\0fileLoader"]);
    }

    public function testOutputGet()
    {
        $cacheMock = \Mockery::mock('JHM\\CacheInterface');
        $cacheMock->shouldReceive('cacheReady')->andReturn(true);
        $configMock = \Mockery::mock('\JHM\ConfigInterface');
        $configMock->shouldReceive('get')->with('flags.cacheEnabled')->andReturn(true)->byDefault();
        $this->mockDeps('Output', 'JHM\\CacheInterface', 'substitutions', $cacheMock);
        $this->mockDeps('Output', 'JHM\\ConfigInterface', 'substitutions', $configMock);
        $output = $this->obj->get('Output');
        $this->assertInstanceOf(\JHM\Output::class, $output);
        $clone = (array) $output;
        $this->assertInstanceOf(\JHM\CacheInterface::class, $clone["\0*\0cacheInterface"]);
    }
}
