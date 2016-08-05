<?php
//https://github.com/Level-2/Dice
namespace JHM;

class Graph {

	private $Container;

	protected $interfaceMap = [
		'JHM\\CacheInterface' 				=> ['instance' => 'JHM\\FileCache'],	
		'JHM\\ConfigInterface' 				=> ['instance' => 'JHM\\Config'],
		'JHM\\DataProviderInterface' 		=> ['instance' => 'JHM\\DataProvider'],
		'JHM\\FileLoaderInterface' 			=> ['instance' => 'JHM\\FileLoader'],
		'JHM\\LoggerInterface' 				=> ['instance' => 'JHM\\Logger'],
		'JHM\\ManifestInterface' 			=> ['instance' => 'JHM\\Manifest'],
		'JHM\\RendererInterface' 			=> ['instance' => 'JHM\\Renderer'],
		'JHM\\TemplateFactoryInterface' 	=> ['instance' => 'JHM\\TemplateFactory']
	];

	protected $rules = [];

	protected function _getFQN($className, $ns = 'JHM') {
		$ns = $ns.'\\';
		$fqn = $ns.str_replace($ns, '', $className);

		return $fqn;
	}

	public function __construct() {

		$this->Container = new \Dice\Dice();
		$this->rules['*'] = [
			"shared" => true,
			"substitutions" => $this->interfaceMap
		];
		$this->Container->addRule('*', $this->rules['*']);
	}

	public function addRule($className, array $rule=[]) {
		$fqn = $this->_getFQN($className);
		if (class_exists($fqn)) {
			if (!empty($rule)) {
				$this->rules[$fqn] = $rule;
				$this->Container->addRule($fqn, $this->rules[$fqn]);
			}
		}
	}

	public function mergeSubstitution($className, array $map=[]) {
		$fqn = $this->_getFQN($className);
		if (class_exists($fqn) && 
			!empty($map))
		{
			$this->rules[$fqn] = [
				"shared" => true,
				"substitutions" => array_merge($this->interfaceMap, $map)
			];
			$this->Container->addRule($fqn, $this->rules[$fqn]);
		}		
	}

	public function get($className) {
		$fqn = $this->_getFQN($className);
		if (class_exists($fqn)) {
			return $this->Container->create($fqn);
		}
		return false;
	}

}