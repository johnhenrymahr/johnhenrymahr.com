<?php
namespace JHM;
class FileLoader implements FileLoaderInterface {

	protected $config;

	public function __construct(ConfigInterface $config) {
		$this->config = $config;
	}

	public function getManifest() {
		$path = $this->config->resolveFile('manifest.json');
		$string = file_get_contents($path);

		if($string) {
			return $string;
		} else {
			throw new Exception('Could not load manifest. Path: '. $path);
		}	
	}

	public function getConfig($id) {

	}
}
?>