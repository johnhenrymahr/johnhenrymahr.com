<?php
namespace JHM;

class Config implements ConfigInterface
{

    public $usingLiveConfig = false;

    protected $active_config = [
        "webroot" => "{{webroot}}", // token replaced by deploy script
        "basepath" => "{{serverApp}}", // token replaced by deploy script
        "systemMailTo" => "{{mailToAddress}}", // send contact emails here, token replaced by deploy script
        "systemMailToName" => "{{mailToName}}", // token replaced by deploy script
        "assetroot" => "rsc/",
        "storage_dir" => "storage",
        "ga_property_id" => "{{property-id}}", // google analytics property id
        "liveconfig" => "liveconfig",
        "pagestate" => [
            "homepage" => "up",
        ],
        "storage" => [
            "filecache" => "{basepath}{storage_dir}cache/",
            "logs" => "{basepath}{storage_dir}logs/",
            "digest" => "{basepath}{storage_dir}digest/",
            "downloads" => "{basepath}{storage_dir}downloads/",
        ],
        "downloads" => [
            "cvMax" => 8,
            "cvFileName" => "jhm_resume.pdf",
            "cvMimeType" => "application/pdf",
        ],
        "mysql" => [
            "enabled" => "{{mysql__enabled}}",
            "host" => "{{mysql__host}}",
            "port" => "{mysql__port}}",
            "db" => "{{mysql__db}}",
            "user" => "{{mysql__user}}",
            "password" => "{{mysql__password}}",
            "prefix" => "{{mysql__prefix}}",
        ],
        "smtp" => [
            "enabled" => "{{smtp__enabled}}", // token replaced by deploy script
            "hostname" => "{{smtp__hostname}}", // token replaced by deploy script
            "username" => "{{smtp__username}}", // token replaced by deploy script
            "password" => "{{smtp__password}}", // token replaced by deploy script
        ],
        "flags" => [
            "loggingEnabled" => false,
            "sendMail" => false,
            "sendContactThankyou" => false,
        ],
        "files" => [
            "dust" => "{basepath}dust/",
            "json" => "{basepath}data/",
            "ini" => "{basepath}cfg/",
            "yaml" => "{basepath}cfg/",
            "yml" => "{basepath}cfg/",
            "list" => "{basepath}cgf/",
            "html" => "{basepath}mailTpl/",
        ],
    ];

    protected $hostMap = [
        'johnhenrymahr.com' => 'production',
        'jhmdev.spherical-photo.net' => 'staging',
        'beta.johnhenrymahr.com' => 'beta',
        'faketestdomain.net' => 'test',
    ];

    protected $host_configs = [
        "test" => [
            "basepath" => "",
            "webroot" => "",
            "testbase" => __DIR__ . "/../../",
            "liveconfig" => "testLiveConfig",
            "testroot" => 'tester/',
            "cachekey" => [
                "testkey2" => 290,
            ],
            "smtp" => [
                "enabled" => false,
            ],
            "files" => [
                "foo" => "bar",
            ],
        ],
        "production" => [
            "flags" => [
                "loggingEnabled" => true,
                "sendMail" => true,
            ],
        ],
        "staging" => [
            "flags" => [
                "loggingEnabled" => true,
                "sendMail" => false,
            ],
        ],
        "beta" => [
            "flags" => [
                "loggingEnabled" => true,
                "sendMail" => true,
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
        $key = $this->_getMappedKey($hostname);
        if ($key && (array_key_exists($key, $this->host_configs))) {
            $this->active_config = array_replace_recursive($this->active_config, $this->host_configs[$key]);
        } elseif (array_key_exists($hostname, $this->host_configs)) {
            $this->active_config = array_replace_recursive($this->active_config, $this->host_configs[$hostname]);
        }
        $liveconfig = $this->_getLiveConfig();
        if ($liveconfig && is_array($liveconfig) && !empty($liveconfig)) {
            $this->active_config = array_replace_recursive($this->active_config, $liveconfig);
            $this->usingLiveConfig = true;
        }

        if ($expand && array_key_exists('basepath', $this->active_config)) {
            $this->active_config['basepath'] = realpath($this->active_config['basepath']);
        }

        $this->active_config['webhost'] = $hostname;
    }

    protected function _getMappedKey($hostname)
    {
        if (array_key_exists($hostname, $this->hostMap)) {
            return $this->hostMap[$hostname];
        }
        return false;
    }

    protected function _getLiveConfig()
    {
        $liveconfig = $this->get('liveconfig');
        if ($liveconfig && is_scalar($liveconfig) && defined('SERVER_ROOT') && is_readable(SERVER_ROOT . $liveconfig . '.ini')) {
            return parse_ini_file(SERVER_ROOT . $liveconfig . '.ini', true, INI_SCANNER_TYPED);
        }
        return false;
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
