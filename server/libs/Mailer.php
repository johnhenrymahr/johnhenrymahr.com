<?php

namespace JHM;

class Mailer implements MailerInterface
{

    protected $config;

    protected $from;

    private $to;

    protected $subject;

    protected $body = array();

    protected $bodyGlue = "\n";

    public function __construct(ConfigInterface $config)
    {
        $this->config = $config;
        $to = filter_var($this->config->get('mailTo'), FILTER_SANITIZE_EMAIL);
        if (filter_var($to, FILTER_VALIDATE_EMAIL)) {
            $this->to = $to;
        } else {
            throw new JhmException('Email to address not valid: ' . $to);
        }
    }

    public function setSubject($subject)
    {
        $this->subject = filter_var($subject, FILTER_SANITIZE_STRING);
    }

    public function setBody($body, $reset = false)
    {
        if ($reset) {
            $this->body = array();
        }
        $body = filter_var($body, FILTER_SANITIZE_STRING);
        if ($body) {
            $this->body[] = $body;
        }
    }

    public function setFromAddress($email)
    {
        $from = filter_var($email, FILTER_SANITIZE_EMAIL);
        if ($from) {
            $this->from = "-f{$from}";
        }
    }

    public function send()
    {
        if ($this->config->get('sendMail')) {
            return $this->_send($this->to, $this->subject, implode($this->bodyGlue, $this->body), $this->from);
        }
        return true;
    }

    protected function _send($to, $subject, $body, $headers)
    {
        return mail($to, $subject, $body, $headers);
    }
}
