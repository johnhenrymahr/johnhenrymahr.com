<?php
namespace JHM;
class Assembler {

  protected  $manifest;
  protected $renderer;
  protected $html;

  public function __construct(JHM\Manifset $manifest, JHM\Renderer $renderer) {
      $this->$manifset = $manifset;
      $this->renderer = $renderer;
  }

  public function assemble() {
      $markup = "";
      $mainTemplate = new JHM\Template($this->manifset->getTopLevelData());
      $markup  .= $mainTemplate->open() . $mainTemplate->body();
      foreach($this -> manifset->getSections() as $section) {
        $sectionTemplate = new JHM\Template($section);
        $markup .= $sectionTemplate->open() . $sectionTemplate->body();
        foreach($this->manifset->getChildren($section) as $child) {
          $childTemplate = new JHM\Template($child)
          $markup .= $childTemplate->open() . $childTemplate->body() . $childTemplate->close();
        }
        $markup .= $sectionTemplate->close();
      }
      $mainTemplate->close();
      $markup  .= $mainTemplate->close();

      return $markup
  }

}
?>