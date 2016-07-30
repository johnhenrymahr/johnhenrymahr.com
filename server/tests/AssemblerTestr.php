<?php
class AssemblerTest extends \PHPUnit\Framework\TestCase {
  protected $obj;

  protected $manifest;

  protected $templateFactory;

  protected function setUp() {    
  	$this->manifest = \Mockery::mock('\JHM\ManifestInterface');
  	$this->templateFactory = \Mockery::mock('\JHM\TemplateFactoryInterface');
  	$this->obj = new \JHM\Assembler($this->manifest, $this->templateFactory);
  }

  protected function tearDown() {
      \Mockery::close();
  }

  public function testInstance() {
  	$this->assertTrue(is_Object($this->obj));
  }

  public function testAssembleMethod () {
    $templateMock = \Mockery::mock('\JHM\TemplateInterface');
    $templateMock->shouldReceive('open')->andReturn('<div>');
    $templateMock->shouldReceive('body')->andReturn('<div class="content">Content</div>');
    $templateMock->shouldReceive('close')->andReturn('</div>');
    $this->manifest->shouldReceive('getTopLevelData')->once()->andReturn([]);
    $this->manifest->shouldReceive('getSections')->once()->andReturn([[], []]);
    $this->manifest->shouldReceive('getChildren')->twice()->andReturn(['child1','child2']);
    $this->templateFactory->shouldReceive('getTemplate')->andReturn($templateMock);
    $result = $this->obj->assemble();
    $expected = '<div><div class="content">Content</div><div><div class="content">Content</div><div><div class="content">Content</div></div><div><div class="content">Content</div></div></div><div><div class="content">Content</div><div><div class="content">Content</div></div><div><div class="content">Content</div></div></div></div>';
   
   $this->assertEquals($expected, $result);
  }

}
?>