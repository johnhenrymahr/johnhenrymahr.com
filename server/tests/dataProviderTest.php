<?php
use PHPUnit\Framework\TestCase;
use JHM\DataProvider;

class DataProviderTest extends TestCase {
  protected $obj;

  protected function setUp() {
    $this->obj = new DataProvider();
  }
  public function testGetTemplateModel () {
    $this->assertTrue(is_array($this->obj->getTemplateModel('templateid')));
  }
}

