<?php
namespace JHM;
class Renderer {
  protected $html;
  protected $dust;
  protected $provider;
  protected $manifset;
  public function __construct(\JHM\DataProvider $provider, \JHM\Template $template, \Dust\Dust $dust) {
    $this->provider = $provider;
    $this->template = $template;
    $this->$dust = $dust;
  }

  public function getTemplate($data) {
    if (array_key_exists('template', $data)) {
    }
  }

}
?>