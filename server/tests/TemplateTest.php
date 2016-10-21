<?php
class TemplateTest extends \PHPUnit\Framework\TestCase
{
    protected $obj;

    protected function setUp()
    {
        $atts = json_decode('{
      "id": "splash",
      "template": "splashTpl.dust",
      "tagName": "section",
      "attributes": {
        "className": "splash",
        "data-foo": "bar"
      },
      "selector": ".splash"
    }', true);
        $q = \QueryPath::with('<div>This be the rendered content</div>');
        $this->obj = new \JHM\Template($atts, $q);
    }
    protected function tearDown()
    {
        \Mockery::close();
    }

    public function testOpenMethod()
    {
        $this->assertEquals('<section class="splash" data-foo="bar">', $this->obj->open());
    }

    public function testBodyMethod()
    {
        $this->assertEquals('<div>This be the rendered content</div>', $this->obj->body());
    }

    public function tesCloseMethod()
    {
        $this->assertEquals('</section>', $this->obj->close());
    }

}
