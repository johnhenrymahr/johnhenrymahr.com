<?php
namespace JHM;

class Cookie
{
    public function set($name, $value = "", $expire = 0, $path = "",
        $domain = "", $secure = false, $httponly = false) {
        return setcookie($name, $value, $expire, $path, $domain, $secure, $httponly);
    }
}
