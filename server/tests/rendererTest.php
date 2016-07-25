<?php
class RendererTest extends \PHPUnit\Framework\TestCase {
  protected $obj;

  protected $dustMock;

  protected function setUp () {
    $this->dustMock = \Mockery::mock('\Dust\Dust');
    $this->obj = new \JHM\Renderer($this->dustMock);
  }
  protected function tearDown() {
      \Mockery::close();
  }
  public function testCompileMethod() {
    $path = '/path/to/file.dust';
    $templateMock = 'compiled ast';
    $this->dustMock->shouldReceive('compileFile')->once()->with($path)->andReturn($templateMock);
    $result = $this->obj->compileFile($path);
    $this->assertEquals($templateMock, $result);
  }

  public function testRenderMethod () {
    $templateMock = \Mockery::mock('\Dust\Ast\Body');
    $data = [];
    $renderedTemplate = '<div>a template</div>';
    $this->dustMock->shouldReceive('renderTemplate')->once()->with($templateMock, $data)->andReturn($renderedTemplate);
    $result = $this->obj->renderTemplate($templateMock, $data);
    $this->assertEquals($renderedTemplate, $result);
  }

}
?>