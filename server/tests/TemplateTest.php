<?php
class TemplateTest extends \PHPUnit\Framework\TestCase
{

    protected $obj;

    protected $atts;

    protected $q;

    protected function _getQueryObj($content)
    {
        $qp = \QueryPath::withHTML5(\QueryPath::HTML5_STUB);
        $qp->find('body')->append($content);
        return $qp;
    }

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
        $this->q = $this->_getQueryObj('<div>This be the rendered content<div class="container"></div></div>');
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

    public function testAppendNoContainer()
    {
        $q = $this->_getQueryObj('');
        $obj = new \JHM\Template($this->atts, $q);
        $qc = \QueryPath::with('<p>child content</p>');
        $atts = json_decode('{
          "id": "childItem",
          "tagName": "div",
          "attributes": {
            "className": "childitem"
          }
        }', true);
        $expected = '<div class="childitem"><p>child content</p></div>';
        $child = new \JHM\Template($atts, $qc);
        $obj->appendChild($child);
        $this->assertEquals($expected, $obj->body());
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

    public function testBodyMethod()
    {
        $this->assertEquals('<div>This be the rendered content<div class="container"></div></div>', $this->obj->body());
    }

    public function tesCloseMethod()
    {
        $this->assertEquals('</section>', $this->obj->close());
    }

    public function testMarkupMethod()
    {
        $this->assertEquals('<section class="splash" data-foo="bar"><div>This be the rendered content<div class="container"></div></div></section>', $this->obj->markup());
    }

}
