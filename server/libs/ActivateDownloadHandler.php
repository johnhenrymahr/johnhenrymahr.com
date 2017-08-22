<?php
namespace JHM;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ActivateDownloadHandler implements ApiHandlerInterface
{

    protected $mailer;

    protected $fileLoader;

    protected $storage;

    protected $config;

    protected $logger;

    protected $_statusMessage;

    protected $_status;

    public function __construct(
        MailerInterface $mailer,
        FileLoaderInterface $fileLoader,
        ContactStorageInterface $storage,
        ConfigInterface $config,
        LoggerInterface $logger
    ) {
        $this->mailer = $mailer;
        $this->fileLoader = $fileLoader;
        $this->storage = $storage;
        $this->config = $config;
        $this->logger = $logger;
    }

    public function process(Request $request)
    {
        if ($request->query->has('t')) {
            $token = $request->query->filter('t', '', FILTER_SANITIZE_STRING);
            $record = $this->storage->getToken($token);
            if ($record) {
                if ($record['active']) {
                    $this->_status = Response::HTTP_NO_CONTENT;
                    $this->_statusMessage = 'Token already activated';
                    return true;
                }
                if ($this->storage->activateDownloadToken($record['id'])) {
                    if ($this->_sendMail($token, $record['name'], $record['email'])) {
                        $this->_status = Response::HTTP_OK;
                        $this->_statusMessage = 'OK';
                        return true;
                    } else {
                        $this->logger->log('ERROR', 'activate sendmail failed');
                        $this->_status = Response::HTTP_INTERNAL_SERVER_ERROR;
                        $this->_statusMessage = 'send error';
                    }
                } else {
                    $this->logger->log('ERROR', 'could not active token', array('record' => $record));
                    $this->_status = Response::HTTP_INTERNAL_SERVER_ERROR;
                    $this->_statusMessage = 'activation error';
                }
            } else {
                $this->logger->log('ERROR', 'query parameter not found', array('_GET' => $_GET));
                $this->_status = Response::HTTP_NOT_FOUND;
                $this->_statusMessage = 'record not found';
            }
        } else {
            $this->_status = Response::HTTP_BAD_REQUEST;
            $this->_statusMessage = 'malformed request';
        }
        return false;
    }

    public function status()
    {
        return $this->_status;
    }

    public function body()
    {
        return array(
            'statusCode' => $this->_status,
            'statusMessage' => $this->_statusMessage,
        );
    }

    protected function _detokenize($template, $token)
    {
        $webhost = $this->config->get('webhost');
        return str_replace(array('{{webhost}}', '{{token}}'), array($webhost, $token), $template);
    }

    protected function _sendMail($token = '', $name = '', $emailAddress = '')
    {
        if (empty($token) || empty($name) || empty($emailAddress)) {
            return false;
        }
        try {
            $this->mailer->reset();
            $this->mailer->setRecipient($emailAddress, $name);
            $this->mailer->setSubject('JHM System Mailer: Download Link');
            $this->mailer->setupNoReply();
            $this->mailer->setHTML(true);
            $template = $this->fileLoader->load('cv.html');
            if ($template && !empty($token)) {
                $template = $this->_detokenize($template, $token);
                $this->mailer->setBody(trim($template));
                $mailResult = $this->mailer->send();
            } else {
                $this->logger->log('ERROR', 'Could not load download mail template');
                return false;
            }
            return $mailResult;
        } catch (\phpmailerException $e) {
            $this->logger->log('ERROR', 'Could not send download mail ', ['exception' => $e->errorMessage()]);
            return false;
        } catch (\Exception $e) {
            $this->logger->log('ERROR', 'Could not send download mail ', ['exception' => $e->getMessage()]);
            return false;
        }
    }
}
