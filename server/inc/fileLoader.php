<?php
namespace JHM;
class FileLoader implements FileLoaderInterface {

	protected $config;

	public function __construct(ConfigInterface $config) {
		$this->config = $config;
	}

	public function getManifest() {
		$path = $this->config->resolveFIle('manifest.json');
		try {
			return file_get_contents($path);
		}catch(Exception $e) {
			throw new Exception('Could not load manifest. Path: '. $path);
		}
	}

	public function getConfig($id) {

	}
}
?>