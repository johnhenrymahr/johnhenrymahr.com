<?php
namespace JHM;

interface FileLoaderInterface
{

    public function load($file, $strict = false, $default = false);
}
