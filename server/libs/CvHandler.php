<?php
namespace JHM;

use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CvHandler extends PostValidator implements ApiHandlerInterface
{
    protected $requiredFields = ['email'];

    protected $honeyPotField = 'screenName';

    protected $mailer;

    protected $fileLoader;

    protected $storage;

    protected $config;

    public function __construct(
        MailerInterface $mailer,
        FileLoaderInterface $fileLoader,
        ContactStorageInterface $storage,
        ConfigInterface $config
    ) {
        $this->mailer = $mailer;
        $this->fileLoader = $fileLoader;
        $this->storage = $storage;
        $this->config = $config;
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

    public function process(Request $request)
    {
        if (!$this->_validate($request->request)) {
            $this->_status = Response::HTTP_BAD_REQUEST;
            return false;
        }

        $token = $this->_generateToken($request->request);

        if (!$token) {
            $this->_status = Response::HTTP_SERVICE_UNAVAILABLE;
            return false;
        }

        $mailResult = $this->_sendMail($request->request, $token);

        if ($mailResult) {
            $this->_status = Response::HTTP_OK;
        } else {
            $this->_removeToken($token);
            $this->_status = Response::HTTP_INTERNAL_SERVER_ERROR;
        }

        return $mailResult;
    }

    protected function _detokenize($template, $token)
    {
        return str_replace('{{token}}', $token, $template);
    }

    protected function _removeToken($token = '')
    {
        if (!empty($token)) {
            $this->storage->removeDownloadToken($token);
        }
    }

    protected function _generateToken(ParameterBag $request)
    {
        if ($this->storage->isReady()) {
            $email = $request->filter('email', null, FILTER_SANITIZE_EMAIL);
            $name = $request->filter('name', '', FILTER_SANITIZE_STRING);
            $company = $request->get('company', '', FILTER_SANITIZE_STRING);
            if ($email) {
                $fileId = $this->config->get('downloads.cvFileName');
                $cid = $this->storage->addContact($email, $name, $company);
                if ($cid && $fileId) {
                    $token = $this->storage->addDownloadRecord($cid, $email, $fileId);
                }
            }
            $this->storage->close();
            if (isset($token) && !empty($token)) {
                return $token;
            }
        }
        return false;
    }

    protected function _sendMail(ParameterBag $request, $token = '')
    {
        $this->mailer->reset();
        $this->mailer->setSubject('JHM System Mailer Download Link');
        $this->mailer->setupNoReply();
        $this->mailer->setHTML(true);
        $template = $this->fileLoader->load('cv.html');
        if ($template && !empty($token)) {
            $template = $this->_detokenize($template, $token);
            $this->mailer->setBody(trim($template));
            $mailResult = $this->mailer->send();
        }
        return $mailResult;
    }

}
