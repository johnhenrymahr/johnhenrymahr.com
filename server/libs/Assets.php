<?php
namespace JHM;
class Assets {
	
	protected $dataFile = 'webpack-assets.json';

	protected $appId = 'app';

	protected $fileLoader;

	protected $config;

	protected $data;

	public function __construct(FileLoaderInterface $fileLoader, ConfigInterface $config) {
		$this->fileLoader = $fileLoader;
		$this->config = $config;
		$this->data = $this->fileLoader->load($this->dataFile, true);
		if (!$this->_validateData()) {
			throw new JhmException('Asset data not ready.');
		}
	}

	protected function _validateData() {

		if (array_key_exists($this->appId, $this->data) &&
		 is_array($this->data[$this->appId]) && 
		 array_key_exists('js', $this->data[$this->appId]) &&
		 array_key_exists('css', $this->data[$this->appId])) {
			return true;
		}
		return false;
	}

	public function get($type) {
		$type = strtolower(trim($type));
		if (array_key_exists($type, $this->data[$this->appId])) {
			return $this->config->get('assetroot').$this->data[$this->appId][$type];
		}
		return '';
	}

}