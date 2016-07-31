<?php
class RendererTest extends \PHPUnit\Framework\TestCase
{
    protected $obj;

    protected $dustMock;

    protected $loggerMock;

    protected function setUp()
    {
        $this->dustMock = \Mockery::mock('\Dust\Dust');
        $this->loggerMock = \Mockery::mock('\JHM\LoggerInterface');
        $this->obj = new \JHM\Renderer($this->dustMock, $this->loggerMock);
    }
    protected function tearDown()
    {
        $this->dustMock = null;
        \Mockery::close();
    }
    public function testCompileMethod()
    {
        $path = '/path/to/file.dust';
        $templateMock = 'compiled ast';
        $this->dustMock->shouldReceive('compileFile')->once()->with($path)->andReturn($templateMock);
        $result = $this->obj->compileFile($path);
        $this->assertEquals($templateMock, $result);
    }

    public function testCompileMethodCatch()
    {
        $path = '/path/to/file.dust';
        $this->dustMock->shouldReceive('compileFile')->once()->with($path)->andThrow('\Dust\DustException');
        $this->loggerMock->shouldReceive('log')->once();
        $this->assertFalse($this->obj->compileFile($path));
    }

    public function testRenderMethod()
    {
        $templateMock = \Mockery::mock('\Dust\Ast\Body');
        $data = [];
        $renderedTemplate = '<div>a template</div>';
        $this->dustMock->shouldReceive('renderTemplate')->once()->with($templateMock, $data)->andReturn($renderedTemplate);
        $result = $this->obj->renderTemplate($templateMock, $data);
        $this->assertEquals($renderedTemplate, $result);
    }

    public function testRenderMethodCatch()
    {
        $templateMock = \Mockery::mock('\Dust\Ast\Body');
        $data = [];
        $renderedTemplate = '<div>a template</div>';
        $this->dustMock->shouldReceive('renderTemplate')->once()->with($templateMock, $data)->andThrow('\Dust\DustException');
        $this->loggerMock->shouldReceive('log')->once();
        $result = $this->obj->renderTemplate($templateMock, $data);
        $this->assertEquals('', $result);
    }

}
