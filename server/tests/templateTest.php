<?php
class TemplateTest extends \PHPUnit\Framework\TestCase {
  protected $obj;

  protected function setUp() {
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
    $this->obj = new \JHM\Template($atts, '<div>This be the rendered content</div>');
  }
  protected function tearDown() {
      \Mockery::close();
  }

  public function testOpenMethod() {
    $this->assertEquals('<section class="splash" data-foo="bar">', $this->obj->open());
  }

   public function tesContentMethod() {
    $this->assertEquals('<div>This be the rendered content</div>', $this->obj->open());
  }

    public function tesCloseMethod() {
    $this->assertEquals('</section>', $this->obj->open());
  }
}
?>