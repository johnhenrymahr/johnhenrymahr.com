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

    protected $storage;

    protected $config;

    public function __construct(
        MailerInterface $mailer,
        ContactStorageInterface $storage,
        ConfigInterface $config,
        MailDigestInterface $digest
    ) {
        $this->mailer = $mailer;
        $this->storage = $storage;
        $this->config = $config;
        $this->digest = $digest;
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

        $mailResult = $this->_sendSystemMail($request->request, $token);

        if ($mailResult) {
            $this->_status = Response::HTTP_OK;
        } else {
            $this->_removeToken($token);
            $this->_status = Response::HTTP_INTERNAL_SERVER_ERROR;
        }

        return $mailResult;
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
            $phone = $request->filter('phone', null, FILTER_SANITIZE_STRING);
            $company = $request->filter('company', null, FILTER_SANITIZE_STRING);
            if ($email) {
                $fileId = $this->config->get('downloads.cvFileName');
                $fileMimeType = $this->config->get('downloads.cvMimeType');
                $cid = $this->storage->addContact($email, $name, $company, $phone);
                if ($cid && $fileId) {
                    $token = $this->storage->addDownloadRecord($cid, $email, $fileId, $fileMimeType);
                }
            }
            $this->storage->close();
            if (isset($token) && !empty($token)) {
                return $token;
            }
        }
        return false;
    }

    protected function _getActivateUrl($token) {
        return 'http://'.$this->config->get('webhost').'/api?component=activateDownload&t='.$token;
    }

    protected function _sendSystemMail(ParameterBag $request, $token)
    {
        $this->mailer->reset();
        $this->mailer->setupSystemMailer();
        $this->mailer->setSubject('johnhenrymahr.com: Resume Request');
        $this->mailer->setFrom($request->get('email'), $request->get('name'));
        $this->mailer->setRelpyTo($request->get('email'), $request->get('name'));
        $this->mailer->setBody("Website Resume Request\n");
        $this->mailer->setBody('From: ' . $request->get('name') . ' (' . $request->get('email') . ')' . "\n");
        if ($request->has('phoneNumber')) {
            $this->mailer->setBody('Phone Number: ' . $request->get('phoneNumber') . "\n");
        }
        if ($request->has('company')) {
            $this->mailer->setBody('Company: ' . $request->get('company') . "\n");
        }
        
        $this->mailer->setBody('Click the link to approve this request.');
        $this->mailer->setBody($this->_getActivateUrl($token));

        $mailResult = $this->mailer->send(true);

        $this->digest->writeMessage($this->mailer);

        return $mailResult;
    }
}
