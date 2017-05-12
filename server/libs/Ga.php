<?php
namespace JHM;

use Symfony\Component\HttpFoundation\Request;

//@uses https://github.com/php-curl-class/php-curl-class

class Ga {
	protected $config;

  protected $logger;

	protected $request;

  protected $url = 'https://www.google-analytics.com/collect';

  protected $cookieKey = '_jhm';

  protected $curl;

  protected $ready = false;

	public function __construct(ConfigInterface $config, LoggerInterface $logger, \Curl\Curl $curl) {
		$this->config = $config;
    $this->logger = $logger;
    $this->curl = $curl;
	}
	public function init($request)
  {
      if (is_object($request) && $request instanceof Request) {
          $this->_init($request);
      } else {
          $this->_init(Request::createFromGlobals());
      }

      if ($this->request instanceof Request && 
        !empty($this->request->server->count())) {
        $this->ready = true;
      }
  }
  public function trackPageHit ($uid = '', $pageTitle = '') {
    if (!$this->ready) {
     throw new JhmException('GA object not ready. Be sure to run init.');
    }
    $params = $this->_getParams($uid, $pageTitle);
    if ($params) {
      return $this->_makeCurlRequest($params);
    }
  }
	protected function _init($request)
  {
      $this->request = $request;
  }
  protected function _makeCurlRequest($params) {
    $this->curl->setUserAgent($this->request->server->get('HTTP_USER_AGENT'));
    $this->curl->post($this->url, $params);
    if($this->curl->error) {
      $this->logger->log('ERROR', 'Could not make analytics request', array(
        'errorCode' => $this->curl->errorCode,
        'errorMessage' => $this->curl->errorMessage
      ));
      return false;
    } else {
      return $curl->response;
    }
  }
  protected function _getParams($uid = '', $documentTitle = '') {
    $params =  array(
      'v' => '1',
      'ds' => 'web', //data source
      'tid' => $this->config->get('ga_property_id'),
      'cid' => $this->_getClientId(),
      't' => 'pageView',
      'dl' => $this->_getLocation(),
      'ua' => $this->request->server->get('HTTP_USER_AGENT'), //user-agent
      'uip' => $this->request->server->get('REMOTE_ADDR'), //ip-address from client
      'dr' => $this->request->server->get('HTTP_REFERER'), //referrer
      'dh' => $this->request->server->get('HTTP_HOST'), // hostname
      'dp' => $this->request->server->get('REQUEST_URI')
    );
    if (!empty($uid)) {
      $params['uid'] = $uid;
    }
    if(!empty($documentTitle)) {
      $params['dt'] = $documentTitle;
    }
    return $params;
  }
  protected function _getProtocol() {
    if ($this->request->server->has('HTTPS') && 
      $this->request->server->get('HTTPS') !=='off' &&
      $this->request->server->has('SERVER_PORT') &&
      $this->request->server->get('SERVER_PORT') == 443
      ) {
      return 'https://';
    }
    return 'http://';
  }
  protected function _getLocation() {

    $url = $this->_getProtocol();
    $url .= $this->request->server->get('HTTP_HOST');
    if ($this->request->server->has('QUERY_STRING')) {
      $url .='?'.$this->request->server->get('QUERY_STRING');
    }
    return $url;  
  }
  protected function _getClientId() {
  	if ($this->request && $this->request->cookies->has($this->cookieKey)) {
  		return $this->request->cookies->filter('_jhm', FILTER_SANITIZE_STRING);
  	} else {
  		$pseudoId = $this->_buildClientId();
      $this->_writeCookie($pseudoId);
      return $pseudoId;
  	}
  }
  protected function _writeCookie($val) {
    setcookie($this->cookieKey, $val, time() + (86400 * 3)); //three day expiration
  }	
	protected function _buildClientId() {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < 32; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
	}
}