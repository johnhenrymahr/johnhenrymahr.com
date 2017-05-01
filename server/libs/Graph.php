<?php
//https://github.com/Level-2/Dice
namespace JHM;

class Graph
{

    private $Container;

    protected $interfaceMap = [
        'JHM\\CacheInterface' => ['instance' => 'JHM\\FileCache'],
        'JHM\\ConfigInterface' => ['instance' => 'JHM\\Config'],
        'JHM\\DataProviderInterface' => ['instance' => 'JHM\\DataProvider'],
        'JHM\\FileLoaderInterface' => ['instance' => 'JHM\\FileLoader'],
        'JHM\\LoggerInterface' => ['instance' => 'JHM\\Logger'],
        'JHM\\ManifestInterface' => ['instance' => 'JHM\\Manifest'],
        'JHM\\RendererInterface' => ['instance' => 'JHM\\Renderer'],
        'JHM\\TemplateFactoryInterface' => ['instance' => 'JHM\\TemplateFactory'],
        'JHM\\ContactStorageInterface' => ['instance' => 'JHM\\ContactStorageInterface'],
        'JHM\\dbFactoryInterface' => ['instance' => 'JHM\\dbFactory'],
    ];

    protected $rules = [];

    protected function _getFQN($className, $ns = 'JHM')
    {
        if ($className === '*') {
            return $className;
        }
        $ns = $ns . '\\';
        $fqn = $ns . str_replace($ns, '', $className);

        return $fqn;
    }

    public function __construct()
    {

        $this->Container = new \Dice\Dice();
        $this->rules['*'] = [
            "shared" => true,
            "substitutions" => $this->interfaceMap,
        ];
        $this->Container->addRule('*', $this->rules['*']);
    }

    public function addRule($className, array $rule = [], array $objects = [])
    {
        $fqn = $this->_getFQN($className);
        if (class_exists($fqn) || $fqn === '*') {
            if (empty($rule)) {
                $rule = $this->rules['*'];
            }
            if (!empty($objects)) {
                $keys = array_keys($objects);
                foreach ($keys as $key) {
                    if (!array_key_exists($key, $rule)) {
                        $rule[$key] = [];
                    }
                    $rule[$key] = array_merge($rule[$key], $objects[$key]);
                }
            }
            $this->rules[$fqn] = $rule;
            $this->Container->addRule($fqn, $this->rules[$fqn]);
        }
    }

    public function get($className, $default = false)
    {
        $fqn = $this->_getFQN($className);
        if (class_exists($fqn)) {
            return $this->Container->create($fqn);
        }
        return $default;
    }

}
