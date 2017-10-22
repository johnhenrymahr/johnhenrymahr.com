<?php
namespace JHM;

class Session implements SessionInterface
{

    private $started = false;

    public function __construct()
    {
        $this->started = session_status() === PHP_SESSION_ACTIVE;
    }

    public function start()
    {
        if (!$this->started) {
            $this->started = session_start();
        }
        return $this->started;
    }

    public function id()
    {
        if ($this->started) {
            return session_id();
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
}
