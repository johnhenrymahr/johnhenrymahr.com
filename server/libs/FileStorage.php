<?php
namespace JHM;

abstract class FileStorage
{

    protected $mode = 0770;

    public function setupStorage($path, $default = false)
    {
        
        $isFile = (Boolean) pathinfo($path, PATHINFO_EXTENSION); // get intention of path regardless of if it exists
        $dirname = dirname($path);
        if ($isFile && is_file($path) && is_writeable($path)) {
            return $path;
        } elseif (!$isFile && is_dir($path) && is_writeable($path)) {
            return $path;
        } elseif (!$isFile && is_dir($dirname) && is_writeable($dirname)) {
            if (@mkdir($path, $this->mode)) {
                return $path;
            } 
        } elseif ($isFile && is_dir($dirname) && is_writable($dirname) && touch($path)) {
            return $path;
        } elseif ($isFile && !is_dir($dirname)) {
            if (@mkdir($dirname, $this->mode) && touch($path)) {
                return $path;
            }
        }

        return $default;
    }
}
