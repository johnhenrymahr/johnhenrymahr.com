<?php 
namespace JHM;
class Config {

	protected $active_confg = [
		"paths" => [
			"dust" => "dust/",
			"json" => "data/"
		]
	]

	protected $host_configs = []

	public function __construct() {
		$hostname = gethostname();
		if (array_key_exists($hostname, $this->host_configs)) {
			$this->active_config = array_merge($this->active_config, $this->host_configs[$hostname]);
		}
	}
	
	public function resolvePath($file) {
		
	}
}
?>