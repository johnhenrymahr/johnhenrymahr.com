<?php

namespace JHM;

class Hash
{
    public function md5File($path)
    {
        return \md5_file($path);
    }
}
