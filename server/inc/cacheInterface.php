<?php
namespace JHM;
interface CacheInterface {

  public function get($key);

  public function set($key, $value);

  public function save();

  public function clear();

  public function cacheReady();

}
?>