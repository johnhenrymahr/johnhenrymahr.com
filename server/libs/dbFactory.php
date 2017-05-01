<?php

namespace JHM;

class dbFactory implements dbFactoryInterface
{

    protected $config;

    public function __construct(ConfigInterface $config)
    {
        $this->config = $config;

    }

    public function getDB()
    {
        if ($this->config->get('mysql.enabled')) {
            return $this->setupDB();
        } else {
            return null;
        }
    }

    protected function setupDB()
    {
        return new \MysqliDb(array(
            'host' => $this->config->get('mysql.host'),
            'username' => $this->config->get('mysql.user'),
            'password' => $this->config->get('mysql.password'),
            'db' => $this->config->get('mysql.db'),
            'port' => $this->config->get('mysql.port'),
            'prefix' => $this->config->get('mysql.prefix'),
            'charset' => 'utf8',
        ));
    }

}
