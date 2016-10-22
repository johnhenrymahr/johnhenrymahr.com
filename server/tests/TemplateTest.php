<?php
class TemplateTest extends \PHPUnit\Framework\TestCase
{

    use \JHM\TemplateTraits;

    protected $obj;

    protected $atts;

    protected $q;

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
        $this->q = \QueryPath::with('<div>This be the rendered content<div class="container"></div></div>');
        $this->obj = new \JHM\Template($this->atts, $this->q);
    }
    protected function tearDown()
    {
        \Mockery::close();
        $this->obj = null;
        $this->atts = null;
        $this->q = null;
    }

    public function testConstruct()
    {
        $this->assertTrue(is_object($this->obj));
    }

    public function testAppendNoChildSelector()
    {
        $q = \QueryPath::with('<p>child content</p>');
        $atts = json_decode('{
          "id": "childItem",
          "tagName": "div",
          "attributes": {
            "className": "childitem"
          }
        }', true);
        $expected = '<div>This be the rendered content<div class="container"></div><div class="childitem"><p>child content</p></div></div>';
        $child = new \JHM\Template($atts, $q);
        $this->obj->appendChild($child);
        $this->assertEquals($expected, $this->obj->body());
    }

    public function testAppendWithChildSelector()
    {

        $q = \QueryPath::with('<p>child content</p>');
        $this->atts['childViewContainer'] = ".container";
        $this->obj = new \JHM\Template($this->atts, $this->q);
        $atts = json_decode('{
          "id": "childItem",
          "tagName": "div",
          "attributes": {
            "className": "childitem"
          }
        }', true);
        $expected = '<div>This be the rendered content<div class="container"><div class="childitem"><p>child content</p></div></div></div>';
        $child = new \JHM\Template($atts, $q);
        $this->obj->appendChild($child);
        $this->assertEquals($expected, $this->obj->body());
    }

    public function testOpenMethod()
    {
        $this->assertEquals('<section class="splash" data-foo="bar">', $this->obj->open());
    }

    public function testBareElement()
    {
        $e = $this->BARE_ELEMENT_WRAPPER_ELEMENT;
        $c = $this->BARE_ELEMENT_WRAPPER_CLASS;
        $markup = "<$e class=\"$c\">child content</$e>";
        $q = \QueryPath::with($markup);
        $obj = new \JHM\Template($this->atts, $q, true);
        $this->assertEquals('child content', $obj->body());
    }

    public function testBodyMethod()
    {
        $this->assertEquals('<div>This be the rendered content<div class="container"></div></div>', $this->obj->body());
    }

    public function tesCloseMethod()
    {
        $this->assertEquals('</section>', $this->obj->close());
    }

}
