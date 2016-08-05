<?php
class GraphTest extends \PHPUnit\Framework\TestCase {
	
	protected $obj;

	protected function setUp() {

		$this->obj = new \JHM\Graph();
	}

	protected function mockDeps($className, $interface, $mockInstance) {
		$map =  [
				$interface => [
					"instance" => function() use ($mockInstance) {
						return $mockInstance;
					}
				]		
			];
		$this->obj->mergeSubstitution($className, $map);
	}

	public function testAssemberGet() {
		$dataPath = realpath(__DIR__) . '/data/mockManifest.json';
        $json = json_decode(file_get_contents($dataPath), true);
        if (!is_array($json)) {
            throw new Exception('Manifest Test: data not ready. Data path: ' . $dataPath);
        }
        $fileLoaderMock = \Mockery::mock('\JHM\FileLoaderInterface');
        $fileLoaderMock->shouldReceive('load')->with('manifest.json', true)->once()->andReturn($json);
        $this->mockDeps('Assembler', 'JHM\FileLoaderInterface', $fileLoaderMock);	
		$assembler = $this->obj->get('Assembler');
		$this->AssertEquals('Assembler', get_class($assembler));
	}

}

