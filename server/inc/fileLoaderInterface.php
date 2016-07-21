<?php
namespace 'JHM';
interface FileLoaderInterface {
	
	public function getManifest();

	public function getTemplate($id);

	public function getConfig($id);
}
?>