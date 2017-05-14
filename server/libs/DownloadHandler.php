<?php
namespace JHM;

use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DownloadHandler implements ApiHandlerInterface
{

    protected $config;

    protected $storage;

    protected $ga;

    protected $hash;

    protected $_status;

    public function __construct(ContactStorageInterface $storage, ConfigInterface $config, GA $ga, Hash $hash)
    {
        $this->hash = $hash;
        $this->config = $config;
        $this->storage = $storage;
        $this->ga = $ga;
    }

    public function process(Request $request)
    {
        $token = $request->query->filter('t', '', FILTER_SANITIZE_STRING);
        if (empty($token)) {
            $this->_status = Response::HTTP_BAD_REQUEST;
            return false;
        }
        $data = $this->storage->validateDownloadToken($token);
        if (!is_array($data) || !isset($data['fileId']) || !isset($data['md5_hash']) || !isset($data['fileMimeType'])) {
            $this->_status = Response::HTTP_PRECONDITION_FAILED;
            return false;
        }
        $storagePath = $this->config->getStorage('downloads') . $data['fileId'];

        if (!is_readable($storagePath)) {
            $this->_status = Response::HTTP_INTERNAL_SERVER_ERROR;
            return false;
        }

        if ($this->hash->md5File($storagePath) !== $data['md5_hash']) {
            $this->_status = Response::HTTP_UNAUTHORIZED;
            return false;
        }

        $response = new BinaryFileResponse($storagePath);
        $response->headers->set('Content-Type', $data['fileMimeType']);
        $response->setContentDisposition(
            \Symfony\Component\HttpFoundation\ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $data['fileId']
        );
        $ga = $this->ga;
        $ga->init($request);
        $this->callbacks[] = function () use ($token, &$ga) {
            $ga->trackPageHit($token, 'JohnHenryMahr: Download a File');
        };
        $this->response = $response;
        $this->_status = Response::HTTP_OK;
        return true;
    }

    public function status()
    {
        return $this->_status;
    }

    public function body()
    {
        return array(
            'statusCode' => $this->_status,
        );
    }
}
