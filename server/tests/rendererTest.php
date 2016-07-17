<?php
use PHPUnit\Framework\TestCase;
use JHM\Renderer;
use JHM\DataProvider;
use JHM\Manifest;
class RendererTest extends TestCase {
  protected $obj;

  protected $dataStub;

  protected $manifestStub;

  protected function setUp () {
    $this->dataStub = $this->createMock(DataProvider::class);
    $this->manifestStub = $this->createMock(Manifest::class);
    $this->obj = new Renderer($this->manifestStub, $this->dataStub);
  }
  public function testInstance () {
    $this->assertTrue(is_object($this->obj));
  }
  public function testBuildTag () {
    $result = $this->obj->buildTag(["className" => "tester", "name" => "joe", "data-test" => "foobar"], 'main');
    $expected = '<main class="tester" name="joe" data-test="foobar"></main>';
    $this->assertEquals($expected, $result);

  }
  public function testConstruct() {

  }
}
?>