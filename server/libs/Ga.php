<?php
namespace JHM;

class Ga {
	protected $config;

	public function __construct(ConfigInterface $config) {
		$this->config = $config;
	}

	
}