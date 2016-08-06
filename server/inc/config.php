<?php
namespace JHM;

class Config implements ConfigInterface
{

    protected $active_config = [
        "webroot" => "",
        "basepath" => "",
        "storage_dir" => "storage",
        "storage" => [
            "filecache" => "{basepath}{storage_dir}cache/",
            "logs" => "{basepath}{storage_dir}logs/",
        ],
        "flags" => [
            "loggingEnabled" => true,
        ],
        "files" => [
            "dust" => "{basepath}dust/",
            "json" => "{basepath}data/",
            "ini" => "{basepath}cfg/",
            "yaml" => "{basepath}cfg/",
            "yml" => "{basepath}cfg/",
            "list" => "{basepath}cgf/",
        ],
    ];

    protected $host_configs = [
        "test" => [
            "testbase" => __DIR__ . "/../../",
            "testroot" => 'tester/',
            "files" => [
                "foo" => "bar",
            ],
        ],
    ];

    public function __construct($hostname = '', $host_config = '', $expand = true)
    {
        if (empty($hostname)) {
            $hostname = gethostname();
        }
        if (is_array($host_config)) {
            $this->host_configs = array_merge($this->host_configs, $host_config);
        }
        if (array_key_exists($hostname, $this->host_configs)) {
            $this->active_config = array_replace_recursive($this->active_config, $this->host_configs[$hostname]);
        }

        if ($expand && array_key_exists('basepath', $this->active_config)) {
            $this->active_config['basepath'] = realpath($this->active_config['basepath']);
        }
    }

    protected function _deTokenize($string)
    {
        return str_replace(
            ['{webroot}', '{basepath}', '{storage_dir}'],
            [$this->prepUrl($this->active_config['webroot']),
                $this->prepUrl($this->active_config['basepath']),
                $this->prepUrl($this->active_config['storage_dir']),
            ],
            $string
        );
    }

    protected function prepUrl($url)
    {
        if (empty($url)) {
            return $url;
        }
        return rtrim($url, '/') . '/';
    }

    protected function _getNestedVar(&$context, $name)
    {
        $pieces = explode('.', $name);
        foreach ($pieces as $piece) {
            if (!is_array($context) || !array_key_exists($piece, $context)) {
                return null;
            }
            $context = &$context[$piece];
        }
        return $context;
    }

    public function set($key, $value)
    {
        $this->active_config[$key] = $value;
    }

    public function __get($key)
    {
        return $this->get($key);
    }

    public function get($key)
    {
        if (strpos($key, '.') !== false) {
            return $this->_getNestedVar($this->active_config, $key);
        } elseif (array_key_exists($key, $this->active_config)) {
            return $this->active_config[$key];
        } else {
            return null;
        }
    }

    public function getStorage($key)
    {
        if (array_key_exists($key, $this->active_config['storage'])) {
            return $this->_deTokenize($this->active_config['storage'][$key]);
        }
    }

    public function resolvePath($file)
    {
        $ext = pathinfo($file, PATHINFO_EXTENSION);
        if (array_key_exists($ext, $this->active_config['files'])) {
            return $this->_deTokenize($this->active_config['files'][$ext]) . $file;
        }
        return $file;
    }
}
