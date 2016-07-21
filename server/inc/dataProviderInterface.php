<?php 
namespace JHM;
interface DataProviderInterface {
	public function getTemplateModel ($templateId);
	public function getBootstrapData();
}
?>