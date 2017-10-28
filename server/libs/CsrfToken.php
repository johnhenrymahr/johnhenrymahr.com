<?php
namespace JHM;

class CsrfToken implements CsrfTokenInterface {

	private $key;

	private $fieldId = 'pr_id';

	protected $ready = false;

	protected $logger;

	protected $session;

	public function __construct(SessionInterface $session, LoggerInterface $logger, $key = '') {
		$this->logger = $logger;
		$this->session = $session;
		$this->ready = $this->session->start();
		if ($this->ready) {
			$sKey = $this->session->get('csrfKey');
			if ($sKey) {
				$this->key = $sKey;
			} else {
				$this->key = (empty($key)) ? bin2hex(openssl_random_pseudo_bytes(32)) : $key;
				$this->session->set('csrfKey', $this->key);
			}
		}
	}

	public function getField() {
		return $this->fieldId;
	}

	public function generateToken($formId) {
		if ($this->ready === false) {
			throw new JhmException('CSRF Not Ready.');
		}
		$id = $this->session->id();
		return hash_hmac('sha256', $id . trim($formId), $this->key);
	}

	public function checkToken($token, $formId) {
		if (!is_string($token)) {
			$this->logger->log('WARNING', 'CSRF: invalid token format');
			return false;
		}

		$isValid = $token === $this->generateToken($formId);
		if (!$isValid) {
			$this->logger->log('WARNING', 'CSRF: invalid token.', array('token' => $token, 'formId' => $formId));
		}
		return $isValid;
	}

}
