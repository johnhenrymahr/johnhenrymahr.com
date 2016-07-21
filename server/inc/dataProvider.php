<?php
namespace JHM;
class DataProvider implements DataProviderInterface {
	
  protected $fileLoader;

  public function __construct(JHM\FileLoaderInterface $fileLoader) {
  	$this->fileLoader;
  }		
  public function getTemplateModel ($templateId) {
    return [];
  }

  public function getBootstrapData() {
  	return {};
  }
}
?>