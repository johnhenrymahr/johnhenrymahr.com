<?php
namespace JHM;

class Session implements SessionInterface
{

    private $started = false;

    private $lifespan = 1440;

    private $id;

    protected $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->started = session_status() === PHP_SESSION_ACTIVE;
        if ($this->started) {
            $this->id = session_id();
        }
        $system_timeout = @ini_get('session.gc_maxlifetime');
        if ($system_timeout && is_int($system_timeout)) {
            $this->lifespan = $system_timeout;
        }
        $this->_validate();
    }

    public function start()
    {
        if (!$this->started) {
            $this->started = session_start();
            if ($this->started) {
                $this->id = session_id();
                $this->_logSession();
            }
        }
        return $this->started;
    }

    public function id()
    {
        if ($this->started) {
            return $this->id;
        }
    }

    public function get($key)
    {
        if ($this->started && isset($_SESSION[$key])) {
            return $_SESSION[$key];
        }
    }

    public function set($key, $val)
    {
        if ($this->started) {
            $_SESSION[$key] = $val;
        }
    }

    protected function _logSession()
    {
        $this->logger->log('INFO', 'New Session ' . date("F j, Y, g:i a"), array(
            'User Agent' => filter_input(INPUT_SERVER, 'HTTP_USER_AGENT', FILTER_SANITIZE_STRING),
            'Request URI' => filter_input(INPUT_SERVER, 'REQUEST_URI', FILTER_SANITIZE_STRING),
            'Remote IP' => filter_input(INPUT_SERVER, 'REMOTE_ADDR', FILTER_SANITIZE_STRING),
            'HTTP Method' => filter_input(INPUT_SERVER, 'REQUEST_METHOD', FILTER_SANITIZE_STRING),
            'Session Id' => $this->id,
            'Request Time' => time(),
        ));
    }

    protected function _validate()
    {
        if ($this->started) {
            if (isset($_SESSION['LAST_ACTIVITY']) &&
                (time() - $_SESSION['LAST_ACTIVITY'] > $this->lifespan)) {
                $_SESSION = array();
                session_destroy(); // destroy session data in storage
                $this->started = false;
                unset($this->id);
            } else {
                $_SESSION['LAST_ACTIVITY'] = time();
            }
        }
    }
}
