<?php
namespace JHM;
interface ConfigInterface {
      public function get($key);
	public function resolvePath($file);
}
?>