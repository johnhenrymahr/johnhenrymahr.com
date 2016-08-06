<?php
namespace JHM;

interface LoggerInterface
{
    public function log($level, $info, $context = []);

    public function loggingTo();

    public function isEnabled();
}
