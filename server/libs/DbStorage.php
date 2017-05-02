<?php
//@url https://github.com/joshcam/PHP-MySQLi-Database-Class
namespace JHM;

abstract class DbStorage
{

    protected $db = null;

    protected $config;

    protected $logger;

    protected $ready = false;

    public function __construct(dbFactoryInterface $db, ConfigInterface $config, LoggerInterface $logger)
    {
        $this->config = $config;
        $this->db = $db->getDB();
        if (is_object($this->db)) {
            try {
                $this->db->connect();
                $this->ready = $this->db->ping();
            } catch (Exception $e) {
                $this->logger->log('ERROR', 'Could not connect to DB ', ['exception' => $e->getMessage()]);
            }
        }
    }

    public function close() {
        $this->db->disconnect();
    }

    public function isReady()
    {
        return $this->ready;
    }
}
