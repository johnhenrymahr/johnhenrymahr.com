<?php
use PHPUnit\Framework\TestCase;
use JHM\Manifest;

$json = <<<JSN
{
  "template": "mainTpl.dust",
  "selector": "main.mainView",
  "attributes": {
    "className": "mainView container-fluid",
    "tagName": "main"
  },
  "sections": [
    {
      "id": "splash",
      "template": "splashTpl.dust",
      "attributes": {
        "className": "splash"
      },
      "selector": ".splash"
    },
    {
    "container": {
      "attributes": {
        "className": "content"
     }
    },
    "children": [
        {
            "id": "title",
            "selector": "section.title",
            "template": "titleTpl.dust",
            "attributes": {
              "className": "titleView",
              "tagName": "section"
            }
        },
        {
            "id": "intro",
            "selector": "section.intro",
            "template": "introTpl.dust",
            "attributes": {
              "className": "introView",
              "tagName": "section"
            }
        }
      ]
    }]
}
JSN;

class ManifestTest extends TestCase {

  protected $obj;

  protected function setUp() {
    global $json;
    $this->obj = new Manifest ($json);
  }
  public function testGetter () {
      $this->assertEquals('mainTpl.dust', $this->obj->template);
  }
  public function testGetSections() {
    $this->assertTrue(is_array($this->obj->getSections()));
  }
  public function testGetChildren() {
    $section = [];
    $this->assertTrue(is_array($this->obj->getChildren($section)));
  }
}