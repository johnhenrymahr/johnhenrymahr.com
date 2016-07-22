<?php
use PHPUnit\Framework\TestCase;
use JHM\Renderer;
use JHM\DataProvider;
use JHM\Manifest;
class RendererTest extends TestCase {
  protected $obj;

  protected $dustMock;

  protected function setUp () {
    $this->dustMock = \Mockey::mock(\Dust\Dust)
    $this->obj = new Renderer($this->dustMock);
  }
  protected function tearDown() {
      \Mockery::close();
  }
  public function testCompileMethod() {
    $tpl = $this->obj->compile('<div>A test string</div>');
    
  }

}
?>