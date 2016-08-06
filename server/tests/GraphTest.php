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
            ->with('manifest.json', true)
            ->once()
            ->andReturn($json)
            ->getMock();

        $this->mockDeps('Manifest', 'JHM\\FileLoaderInterface', 'substitutions', $fileLoaderMock);
        $this->obj->addRule('Logger', [], ['constructParams' => ['testconfig', $config]]);
        $assembler = $this->obj->get('Assembler');
        $this->AssertInstanceOf(\JHM\Assembler::class, $assembler);
        $clone = (array) $assembler;
        $this->assertEquals("\0*\0manifest", array_Keys($clone)[0]); // protected propertie keys get prepended with \0*\0 when cast to Array, private \0Class_name\0
        $this->assertInstanceOf(\JHM\TemplateFactory::class, $clone["\0*\0templateFactory"]);
        $this->assertInstanceOf(\JHM\Manifest::class, $clone["\0*\0manifest"]);
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
        $this->assertInstanceOf(\JHM\Logger::class, $clone["\0*\0logger"]);
        $this->assertInstanceOf(\JHM\fileLoader::class, $clone["\0*\0fileLoader"]);
    }

}
