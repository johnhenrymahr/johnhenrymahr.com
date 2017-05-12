<?php

class ContactStorageTest extends \PHPUnit\Framework\TestCase {

	protected $config;

	protected $logger;

	protected $curl;

	protected $obj;

	protected $request;

	protected function setUp() {

		$this->logger = \Mockery::mock('\JHM\LoggerInterface');
    $this->logger->shouldReceive('log');
    $this->config = \Mockery::mock('\JHM\ConfigInterface');
    $this->config->shouldReceive('get')->with('ga_property_id')
    $this->curl = \Mockery::mock('\Curl\Curl');
    $this->obj = new \JHM\Ga($this->config, $this->logger, $this->curl);

    $this->request = Request::create('/download', 'GET', array('t' => 'test'));
	}
	

}