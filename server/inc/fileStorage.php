<?php
namespace JHM;

abstract class fileStorage {

	protected $mode = 0770;

	public function setupStorage($path, $default = false) {
		if (is_file($path) && is_writeable($path)) {
			return $path;
		} elseif (is_dir($path)) {
			if (is_writeable($path)) {
				return $path
			} else {
				$parent = dirname($path);
				if (is_dir($parent) && is_writeable($parent)) {
					if (mkdir($path), $this->mode) {
						return $path;
					}
				}
			}
		}

		return $default;
	}
}