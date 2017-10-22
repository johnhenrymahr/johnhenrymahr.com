<?php
namespace JHM;

interface SessionInterface
{

    public function start();

    public function id();

    public function set($key, $val);

    public function get($key);

}
