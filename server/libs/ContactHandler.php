<?php
namespace JHM;

use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;

class ContactHandler implements ApiHandlerInterface
{

    protected $mailer;

    protected $digest;

    protected $fileLoader;

    protected $storage;

    protected $_status;

    public function __construct(
        MailerInterface $mailer,
        MailDigestInterface $digest,
        FileLoaderInterface $fileLoader,
        ContactStorageInterface $storage
    ) {
        $this->mailer = $mailer;
        $this->digest = $digest;
        $this->fileLoader = $fileLoader;
        $this->storage = $storage;
    }

    public function process(Request $request)
    {
        if (!$this->_validate($request)) {
            $this->_status = 400;
            return false;
        }

        $this->addToDb($request->request);

        $mailResult = $this->_sendSystemMail($request->request);

        if ($mailResult) {
            $this->_status = 200;
            $this->_sendThankYouMail($request->request);
        } else {
            $this->_status = 500;
        }

        return $mailResult;
    }

    protected function addToDb(ParameterBag $request)
    {
        if ($this->storage->isReady()) {
            $name = $request->get('name');
            $email = $request->get('email');
            $phone = $request->get('phoneNumber');
            $company = $request->get('company');
            $topic = $request->get('topic');
            if ($request->has('custom-topic')) {
                $topic = $request->get('custom-topic');
            }
            $message = $request->get('message');
            if ($name && $email) {
                $id = $this->storage->addContact($email, $name, $phone, $company);
                if ($id) {
                    $this->addMessage($id, $topic, $message);
                }
            }
        }
    }

    protected function _sendSystemMail(ParameterBag $request)
    {
        $this->mailer->reset();
        $this->mailer->setupSystemMailer();
        $this->mailer->setSubject('johnhenrymahr.com: Web Form Contact: ' . $request->get('topic'));
        $this->mailer->setFrom($request->get('email'), $request->get('name'));
        $this->mailer->setRelpyTo($request->get('email'), $request->get('name'));
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
    }

    protected function _sendThankYouMail(ParameterBag $request)
    {
        $this->mailer->reset();
        $tpl = $this->fileLoader->load('thankYou.html');
        if ($tpl) {
            $this->mailer->setupNoReply();
            $this->mailer->setHTML(true);
            $this->mailer->setSubject('Thanks for Contacting John Henry Mahr');
            $this->mailer->setBody(trim($tpl));
            $this->mailer->setRecipient($request->get('email'), $request->get('name'));
            $this->mailer->send();
        }
    }

    protected function _validate(Request $request)
    {
        $keys = $request->request->keys();
        if (empty($keys)) {
            return false;
        }
        $required = ['email', 'name', 'topic', 'message'];
        $diff = array_diff($required, $keys);

        if (!empty($diff)) {
            return false;
        }

        if (!filter_var($request->request->get('email'), FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        if (empty($request->request->get('name') || $request->request->get('top') || $request->request->get('message'))) {
            return false;
        }

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
