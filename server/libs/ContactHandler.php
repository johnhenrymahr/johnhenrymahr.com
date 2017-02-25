<?php
namespace JHM;

use Symfony\Component\HttpFoundation\Request;

class ContactHandler implements ApiHandlerInterface
{

    protected $mailer;

    protected $digest;

    protected $_status;

    public function __construct(MailerInterface $mailer, MailDigestInterface $digest)
    {
        $this->mailer = $mailer;
        $this->digest = $digest;
    }

    public function process(Request $request)
    {
        if (!$this->_validate($request)) {
            $this->_status = 400;
            return false;
        }

        $r = $request->request;
        $this->mailer->setSubject('johnhenrymahr.com: Web Form Contact: ' . $r->get('topic'));
        $this->mailer->setFromAddress($r->get('email'));
        $this->mailer->setBody('Website Contact');
        $this->mailer->setBody('From: ' . $r->get('name') . ' (' . $r->get('email') . ')');
        if ($r->has('phoneNumber')) {
            $this->mailer->setBody('Phone Number: ' . $r->get('phoneNumber'));
        }
        if ($r->has('company')) {
            $this->mailer->setBody('Company: ' . $r->get('company'));
        }
        $this->mailer->setBody('Topic: ' . $r->get('topic'));
        if ($r->has('custom-topic')) {
            $this->mailer->setBody($r->get('custom-topic'));
        }
        $this->mailer->setBody($r->get('message'));
        $mailResult = $this->mailer->send();
        $this->digest->writeMessage($this->mailer);

        if ($mailResult) {
            $this->_status = 200;
        } else {
            $this->_status = 500;
        }

        return $mailResult;
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
