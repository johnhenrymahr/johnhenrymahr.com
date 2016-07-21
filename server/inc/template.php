<?php
namespace JHM;

class Template implements TemplateInterface {

  protected $attributes = [];

  protected $id;

  protected $tagName = 'div';

  protected $content = '';

  public function __construct(Array $data, $content='') {

    if (is_string($content)) {
      $this->content = $content;
    }  

    if (array_key_exists('tagName', $data)) {
      $this->tagName = $data['tagName'];
    }

    if(array_key_exists('attributes', $data) && is_Array($data['attributes']))
      $this->attributes = $data['attributes'];
    }

    if(array_key_exists('id', $data)) {
      $this->id = $data['id'];
    }
  }

  public function open () {
    $this->_open = true;
    return $this->_buildTag();
  }

  public function body () {
    return $this->content;
  }

  public function close () {
    $this->_open = false;
    return '</'.$this->tagName.'>';
  }

  protected function _buildTag() {
    $tag = '<'.$this->tagName;
    foreach($this->attributes as $key=>$val) {
      if ($key === "className") {
        $key = "class";
      }
      $tag .= ' '.$key.'="'.$val.'"';
    }
    $tag .= '>';
    return $tag;
  }

}
?>