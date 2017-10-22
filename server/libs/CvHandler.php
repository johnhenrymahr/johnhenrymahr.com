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

    protected $logger;

    protected $csrfToken;

    public function __construct(
        MailerInterface $mailer,
        ContactStorageInterface $storage,
        ConfigInterface $config,
        MailDigestInterface $digest,
        LoggerInterface $logger,
        CsrfTokenInterface $csrfToken
    ) {
        $this->mailer = $mailer;
        $this->storage = $storage;
        $this->config = $config;
        $this->digest = $digest;
        $this->logger = $logger;
        $this->csrfToken = $csrfToken;
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
        if (!$this->_validate($request->request, 'cv')) {
            $this->_status = Response::HTTP_BAD_REQUEST;
            $this->logger->log('ERROR', 'validation error', ['request' => $_REQUEST]);
            return false;
        }

        $token = $this->_generateToken($request->request);

        if (!$token) {
            $this->logger->log('ERROR', 'generate token failed', ['request' => $_REQUEST, 'token' => $token]);
            $this->_status = Response::HTTP_SERVICE_UNAVAILABLE;
            return false;
        }

        $mailResult = $this->_sendSystemMail($request->request, $token);

        if ($mailResult) {
            $this->_status = Response::HTTP_OK;
        } else {
            $this->_removeToken($token);
            $this->logger->log('ERROR', 'Send mail failure', ['result' => $mailResult]);
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
            $this->logger->log('INFO', 'Generating token');
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
                } else {
                    $this->logger->log('ERROR', 'Could not add a contact', array('cid' => $cid, 'fileId' => $fileId));
                }
            }
            $this->storage->close();
            if (isset($token) && !empty($token)) {
                return $token;
            } else {
                $this->logger->log('ERROR', 'Could not generate a token', array('token' => $token));
            }
        }
        return false;
    }

    protected function _getActivateUrl($token)
    {
        return 'http://' . $this->config->get('webhost') . '/api?component=activate&t=' . $token;
    }

    protected function _sendSystemMail(ParameterBag $request, $token)
    {

        try {
            $this->mailer->reset();
            $this->mailer->setupSystemMailer();
            $this->mailer->setSubject('johnhenrymahr.com: Resume Request');
            $this->mailer->setFrom($request->get('email'), $request->get('name'));
            $this->mailer->setReplyTo($request->get('email'), $request->get('name'));
            $this->mailer->setBody("Website Resume Request\n");
            $this->mailer->setBody('From: ' . $request->get('name') . ' (' . $request->get('email') . ')' . "\n");
            if ($request->has('phoneNumber')) {
                $this->mailer->setBody('Phone Number: ' . $request->get('phoneNumber') . "\n");
            }
            if ($request->has('company')) {
                $this->mailer->setBody('Company: ' . $request->get('company') . "\n");
            }

            $this->mailer->setBody('Click the link to approve this request.' . "\n");
            $this->mailer->setBody($this->_getActivateUrl($token));

            $mailResult = $this->mailer->send(true);

            $this->digest->writeMessage($this->mailer);

            return $mailResult;
        } catch (\phpmailerException $e) {
            $this->logger->log('ERROR', 'Could not send mail ', ['exception' => $e->errorMessage()]);
        } catch (\Exception $e) {
            $this->logger->log('ERROR', 'Could not send mail ', ['exception' => $e->getMessage()]);
        }
    }
}
