<?php
namespace JHM;

use \Symfony\Component\Yaml\Exception\ParseException;
use \Symfony\Component\Yaml\Yaml;

class FileLoader implements FileLoaderInterface
{

    protected $config;

    public function __construct(ConfigInterface $config)
    {
        $this->config = $config;
    }

    protected function _loadIni($path)
    {
        $return = file_get_contents($path);
        if ($return) {
            $return = @parse_ini_string($return, true, INI_SCANNER_TYPED);
        }
        return $return;
    }

    protected function _loadYaml($path)
    {
        $return = file_get_contents($path);
        if ($return) {
            try {
                $return = Yaml::parse($return);
            } catch (ParseException $e) {
                $return = false;
            }
        }
        return $return;
    }

    protected function _loadList($path)
    {
        return @file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    }

    protected function _loadJSON($path)
    {
        $return = file_get_contents($path);
        if ($return) {
            $return = json_decode($return, true);
            if (is_null($return)) {
                $return = false;
            }
        }
        return $return;
    }

    public function load($file, $strict = false, $default = false)
    {
        $path = $this->config->resolvePath($file);
        $ext = pathinfo($file, PATHINFO_EXTENSION);
        $return = $default;
        if (is_file($path) && is_readable($path)) {
            switch ($ext) {
                case 'ini':
                    $return = $this->_loadIni($path);
                    break;
                case 'yaml':
                case 'yml':
                    $return = $this->_loadYaml($path);
                    break;
                case 'json':
                    $return = $this->_loadJSON($path);
                    break;
                case 'list':
                    $return = $this->_loadList($path);
                    break;
                default:
                    $return = file_get_contents($path);
            }
        } elseif ($strict) {
            throw new JhmException('File not found at ' . $path);
        }

        if ($return === false) {
            if ($strict) {
                throw new JhmException('Could not parse file at ' . $path);
            }
            $return = $default;
        }

        return $return;
    }
}
