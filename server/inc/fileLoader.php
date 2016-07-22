<?php
namespace JHM;
class FileLoader implements FileLoaderInterface {

	protected $config;

	public function __construct(JHM\EnvConfigInterface $config) {
		$this->config = $config;
	}

	public function getManifest() {

	}

	public function getTemplate($id) {

	}

	public function getConfig($id) {

	}	
}
?>