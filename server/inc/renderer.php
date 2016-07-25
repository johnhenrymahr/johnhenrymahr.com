<?php
namespace JHM;
class Renderer implements RendererInterface{

  protected $dustEngine;

  public function __construct(\Dust\Dust $dust) {
    $this->dustEngine = $dust;
  }

  public function compileFile($path) {
    try {
  	return $this->dustEngine->compileFile($path);
    } catch (Exception $e) {
      return false;
    }
  }

  public function renderTemplate($template, $data) {
    try {
  	return $this->dustEngine->renderTemplate($template, $data);
    } catch(Exception $e) {
        return false;
    }
  }

}
?>