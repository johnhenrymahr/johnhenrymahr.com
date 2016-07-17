<?php
namespace JHM;
class Manifest {

  protected $json = [];

  public function __construct($filedata='') {
      if (is_string($filedata) && !empty($filedata)) {
        $this->setData($filedata)
      }
  }

  public function setData($data) {
      $this->json = json_decode($filedata, true);
  }

  public function __get($key) {
    if (array_key_exists($key, $this->json)) {
      return $this->json[$key];
    }
    return false;
  }
  public function getTopLevelData () {
    return  array_diff_key($this->json, ["sections" => [], "children" => []]);
  }

  public function getSections() {
    if (array_key_exists('sections', $this->json) && is_array($this->json['sections'])) {
      return $this->json['sections'];
    }
    return [];
  }

  public function getChildren(array $section) {
    if (array_key_exists('children', $section) && is_array($section['children'])) {
      return $section['children'];
    }
    return [];
  }

}
?>