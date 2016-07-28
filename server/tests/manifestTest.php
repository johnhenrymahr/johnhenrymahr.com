<?php
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

class ManifestTest extends \PHPUnit\Framework\TestCase {

  protected $obj;

  protected function setUp() {
    global $json;
    $fileLoaderMock = \Mockery::mock('\JHM\FileLoaderInterface');
    $fileLoaderMock->shouldReceive('load')->with('manifest.json', true)->once()->andReturn(json_decode($json, true));

    $this->obj = new JHM\Manifest ($fileLoaderMock);
  }
  protected function tearDown() {
      \Mockery::close();
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