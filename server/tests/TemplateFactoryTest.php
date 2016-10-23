<?php
class TemplateFactoryTest extends \PHPUnit\Framework\TestCase
{

    protected $atts;

    protected $obj;

    protected $mocks = [];

    protected function setUp()
    {
        $this->atts = json_decode('{
      "id": "splash",
      "template": "splashTpl.dust",
      "tagName": "section",
      "attributes": {
        "className": "splash",
        "data-foo": "bar"
      },
      "selector": ".splash"
    }', true);
        $this->mocks['DP'] = \Mockery::mock('\JHM\DataProviderInterface');
        $this->mocks['config'] = \Mockery::mock('\JHM\ConfigInterface');
        $this->mocks['renderer'] = \Mockery::mock('\JHM\RendererInterface');
        $this->obj = new \JHM\TemplateFactory($this->mocks['renderer'], $this->mocks['config'], $this->mocks['DP']);
    }

    protected function tearDown()
    {
        \Mockery::close();
    }

    public function testInstance()
    {
        $this->assertTrue(is_object($this->obj));
    }

    public function testGetTemplateNegative()
    {
        $this->assertFalse($this->obj->getTemplate());
    }

    public function testGetTemplate()
    {
        $mockTpl = 'template AST code';
        $mockData = ["foo" => "bar"];
        $mockRendered = '<div>compiled</div>';
        $this->mocks['config']->shouldReceive('resolvePath')->with('splashTpl.dust')->andReturn('/path/to/splashTpl.dust');
        $this->mocks['DP']->shouldReceive('getTemplateModel')->with('splash')->andReturn($mockData);
        $this->mocks['renderer']->shouldReceive('compileFile')->with('/path/to/splashTpl.dust')->andReturn($mockTpl);
        $this->mocks['renderer']->shouldReceive('renderTemplate')->with($mockTpl, $mockData)->andReturn($mockRendered);
        $result = $this->obj->getTemplate($this->atts);
        $this->assertInstanceOf(\JHM\Template::class, $result);
        $this->assertEquals($mockRendered, $result->body());
        $this->assertEquals('</section>', $result->close());
    }

    public function testGetTemplateCompileFail()
    {
        $mockTpl = 'template AST code';
        $mockData = ["foo" => "bar"];
        $mockRendered = '';
        $this->mocks['config']->shouldReceive('resolvePath')->with('splashTpl.dust')->andReturn('/path/to/splashTpl.dust');
        $this->mocks['DP']->shouldReceive('getTemplateModel')->with('splash')->andReturn($mockData);
        $this->mocks['renderer']->shouldReceive('compileFile')->with('/path/to/splashTpl.dust')->andReturn(false);
        $result = $this->obj->getTemplate($this->atts);
        $this->assertInstanceOf(\JHM\Template::class, $result);
        $this->assertEquals($mockRendered, $result->body());
        $this->assertEquals('</section>', $result->close());
    }

    public function testGetTemplateRenderFail()
    {
        $mockTpl = 'template AST code';
        $mockData = ["foo" => "bar"];
        $mockRendered = '';
        $this->mocks['config']->shouldReceive('resolvePath')->with('splashTpl.dust')->andReturn('/path/to/splashTpl.dust');
        $this->mocks['DP']->shouldReceive('getTemplateModel')->with('splash')->andReturn($mockData);
        $this->mocks['renderer']->shouldReceive('compileFile')->with('/path/to/splashTpl.dust')->andReturn($mockTpl);
        $this->mocks['renderer']->shouldReceive('renderTemplate')->with($mockTpl, $mockData)->andReturn(false);
        $result = $this->obj->getTemplate($this->atts);
        $this->assertInstanceOf(\JHM\Template::class, $result);
        $this->assertEquals($mockRendered, $result->body());
        $this->assertEquals('</section>', $result->close());
    }

}
