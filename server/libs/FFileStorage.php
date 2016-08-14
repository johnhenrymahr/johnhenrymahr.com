<?php
namespace JHM;

abstract class FileStorage {

	protected $mode = 0770;

	public function setupStorage($path, $default = false) {
		if (is_file($path) && is_writeable($path)) {
			return $path;
		} elseif (is_dir($path) && is_writeable($path)) {		
			return $path;			
		} elseif (is_dir(dirname($path))) {
			if (mkdir($path, $this->mode)) {
				return $path;
			}
		}			

		return $default;
	}
}