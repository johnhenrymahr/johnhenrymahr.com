<?php
class AssemblerTest extends \PHPUnit\Framework\TestCase
{
    protected $obj;

    protected $manifest;

    protected $templateFactory;

    protected $markup;

    protected function setUp()
    {
        $this->manifest = \Mockery::mock('\JHM\ManifestInterface');
        $this->templateFactory = \Mockery::mock('\JHM\TemplateFactoryInterface');
        $this->obj = new \JHM\Assembler($this->manifest, $this->templateFactory);
        $this->markup = file_get_contents(realpath(__DIR__) . '/data/expectedMarkupOutput.html');
    }

    protected function tearDown()
    {
        \Mockery::close();
    }

    public function testInstance()
    {
        $this->assertTrue(is_Object($this->obj));
    }

    public function testAssembleMethod()
    {
        $templateMock = \Mockery::mock('\JHM\TemplateInterface');

        $templateMock->shouldReceive('markup')->once()->andReturn($this->markup);
        $templateMock->shouldReceive('appendChild')->atLeast()->times(4);
        $templateMock->shouldReceive('purgeImgTags')->andReturn($templateMock);
        $this->manifest->shouldReceive('getTopLevelData')->once()->andReturn([]);
        $this->manifest->shouldReceive('getSections')->once()->andReturn([["children" => array()], [], ["renderOnServer" => false]]);
        $this->manifest->shouldReceive('getChildren')->once()->andReturn([['child1'], ['child2']]);
        $this->templateFactory->shouldReceive('getTemplate')->andReturn($templateMock);
        $result = $this->obj->assemble();

        $this->assertEquals($this->markup, $result);
    }

}
