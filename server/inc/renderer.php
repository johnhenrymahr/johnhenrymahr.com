<?php
namespace JHM;
class Renderer implements RendererInterface{
  
  protected $dustEngine;

  public function __construct(\Dust\Dust $dust) {
    $this->dustEngine = $dust;
  }

  public function compile($templateString) {
  	return $this->dustEngine->compile($templateString);
  }
  
  public function renderTemplate($template, $data) {
  	return $this->dustEngine->renderTemplate($template, $data);	
  }

}
?>