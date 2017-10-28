<?php
namespace JHM;

class Session implements SessionInterface {

	private $started = false;

	private $lifespan = 1440;

	public function __construct() {
		$this->started = session_status() === PHP_SESSION_ACTIVE;
		$system_timeout = @ini_get('session.gc_maxlifetime');
		if ($system_timeout && is_int($system_timeout)) {
			$this->lifespan = $system_timeout;
		}
		$this->_valdiate();
	}

	public function start() {
		if (!$this->started) {
			$this->started = session_start();
		}
		return $this->started;
	}

	public function id() {
		if ($this->started) {
			return session_id();
		}
	}

	public function get($key) {
		if ($this->started && isset($_SESSION[$key])) {
			return $_SESSION[$key];
		}
	}

	public function set($key, $val) {
		if ($this->started) {
			$_SESSION[$key] = $val;
		}
	}

	protected function _validate() {
		if ($this->started) {
			if (isset($_SESSION['LAST_ACTIVITY']) &&
				(time() - $_SESSION['LAST_ACTIVITY'] > $this->lifespan)) {
				$_SESSION = array();
				session_destroy(); // destroy session data in storage
				$this->started = false;
			} else {
				$_SESSION['LAST_ACTIVITY'] = time();
			}
		}
	}
}
