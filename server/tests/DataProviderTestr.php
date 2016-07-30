<?php
class DataProviderTest extends \PHPUnit\Framework\TestCase {
  protected $obj;

  protected function setUp() {
  	$fileLoaderMock = \Mockery::mock('\JHM\FileLoaderInterface');
      $this->obj = new \JHM\DataProvider($fileLoaderMock);
  }
  protected function tearDown() {
      \Mockery::close();
  }
  public function testGetTemplateModel () {
    $this->assertTrue(is_array($this->obj->getTemplateModel('templateid')));
  }
  public function testGetBootstrapData () {
  	$this->assertTrue(is_array($this->obj->getBootstrapData()));
  }
}

