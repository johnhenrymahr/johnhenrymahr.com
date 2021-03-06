<?php
namespace JHM;

use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ContactHandler extends PostValidator implements ApiHandlerInterface
{

    protected $mailer;

    protected $digest;

    protected $fileLoader;

    protected $storage;

    protected $config;

    protected $_status;

    protected $logger;

    protected $csrfToken;

    protected $requiredFields = ['email', 'name', 'topic', 'message'];

    protected $honeyPotField = 'screenName';

    public function __construct(
        MailerInterface $mailer,
        MailDigestInterface $digest,
        FileLoaderInterface $fileLoader,
        ContactStorageInterface $storage,
        ConfigInterface $config,
        LoggerInterface $logger,
        CsrfTokenInterface $csrfToken
    ) {
        $this->mailer = $mailer;
        $this->digest = $digest;
        $this->fileLoader = $fileLoader;
        $this->storage = $storage;
        $this->config = $config;
        $this->logger = $logger;
        $this->csrfToken = $csrfToken;
    }

    public function process(Request $request)
    {
        if (!$this->_validate($request->request, 'contact')) {
            $this->_status = Response::HTTP_BAD_REQUEST;
            $this->logger->log('ERROR', 'post validation failure');
            return false;
        }

        $this->addToDb($request->request);

        $mailResult = $this->_sendSystemMail($request->request);

        if ($mailResult) {
            $this->_status = Response::HTTP_OK;
            if ($this->config->get('flags.sendContactThankyou')) {
                $this->_sendThankYouMail($request->request);
            }
        } else {
            $this->logger->log('ERROR', 'send mail failure');
            $this->_status = Response::HTTP_INTERNAL_SERVER_ERROR;
        }

        return $mailResult;
    }

    protected function addToDb(ParameterBag $request)
    {
        if ($this->storage->isReady()) {
            $this->logger->log('INFO', 'attempting to add to DB');
            $name = $request->filter('name', null, FILTER_SANITIZE_STRING);
            $email = $request->filter('email', null, FILTER_SANITIZE_EMAIL);
            $phone = $request->filter('phone', null, FILTER_SANITIZE_STRING);
            $company = $request->filter('company', null, FILTER_SANITIZE_STRING);
            $topic = $request->filter('topic', null, FILTER_SANITIZE_STRING);
            if ($request->has('custom-topic')) {
                $topic = $request->filter('custom-topic', null, FILTER_SANITIZE_STRING);
            }
            $message = $request->filter('message', null, FILTER_SANITIZE_STRING);
            if ($name && $email) {
                $id = $this->storage->addContact($email, $name, $company, $phone);
                if ($id) {
                    $this->storage->addMessage($id, $topic, $message);
                }
            }
            $this->storage->close();
        }
    }

    protected function _sendSystemMail(ParameterBag $request)
    {
        try {
            $this->mailer->reset();
            $this->mailer->setupSystemMailer();
            $this->mailer->setSubject('johnhenrymahr.com: Web Form Contact: ' . $request->get('topic'));
            $this->mailer->setReplyTo($request->get('email'), $request->get('name'));
            $this->mailer->setBody("Website Contact\n");
            $this->mailer->setBody('From: ' . $request->get('name') . ' (' . $request->get('email') . ')' . "\n");
            if ($request->has('phoneNumber')) {
                $this->mailer->setBody('Phone Number: ' . $request->get('phoneNumber') . "\n");
            }
            if ($request->has('company')) {
                $this->mailer->setBody('Company: ' . $request->get('company') . "\n");
            }
            $this->mailer->setBody('Topic: ' . $request->get('topic') . "\n");
            if ($request->has('custom-topic')) {
                $this->mailer->setBody($request->get('custom-topic') . "\n");
            }
            $this->mailer->setBody($request->get('message') . "\n");

            $mailResult = $this->mailer->send(true);

            $this->digest->writeMessage($this->mailer);

            return $mailResult;

        } catch (\phpmailerException $e) {
            $this->logger->log('ERROR', 'Could not send mail ', ['exception' => $e->errorMessage()]);
        } catch (\Exception $e) {
            $this->logger->log('ERROR', 'Could not send mail ', ['exception' => $e->getMessage()]);
        }
    }

    protected function _sendThankYouMail(ParameterBag $request)
    {
        $this->mailer->reset();
        $template = $this->fileLoader->load('thankYou.html');
        if ($template) {
            try {
                $this->mailer->setupNoReply();
                $this->mailer->setHTML(true);
                $this->mailer->setSubject('Thanks for Contacting John Henry Mahr');
                $this->mailer->setBody(trim($template));
                $this->mailer->setRecipient($request->get('email'), $request->get('name'));
                $this->mailer->send();
            } catch (\phpmailerException $e) {
                $this->logger->log('ERROR', 'Could not send mail ', ['exception' => $e->errorMessage()]);
            } catch (\Exception $e) {
                $this->logger->log('ERROR', 'Could not send mail ', ['exception' => $e->getMessage()]);
            }
        }
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
