<?php
namespace JHM;
interface FileLoaderInterface {

	public function getManifest();

	public function getConfig($id);
}
?>