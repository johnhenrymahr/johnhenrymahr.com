<?php
namespace JHM;
class FileLoader implements FileLoaderInterface {

	protected $config;

	public function __construct(ConfigInterface $config) {
		$this->config = $config;
	}

	protected function _loadIni($path) {
		$return = file_get_contents($path);
		if ($return) {
			$return = @parse_ini_string($return, true, INI_SCANNER_TYPED);						
		}
		return $return;
	}

	protected function _loadJSON($path) {
		$return = file_get_contents($path);
		if ($return) {
			$return = json_decode($return, true);
			if (is_null($return)) {
				$return = false;
			}
		}
		return $return;
	}

	public function load($file, $strict=false) {
		$path = $this->config->resolvePath($file);
		$ext = pathinfo($file, PATHINFO_EXTENSION);
		$return = false;
		if (file_exists($path)) {
			switch($ext) {
				case 'ini':
					$return = $this->_loadIni($path);
				break;
				case 'json':
					$return = $this->_loadJSON($path);
				break;
			}
		} elseif ($strict) {
			throw new JhmException ('File not found at '.$path);
		}

		if ($return===false && $strict) {
			throw new JhmException ('Could not parse file at '.$path);
		}

		return $return;
	}
}	
?>