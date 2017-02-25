<?php

namespace JHM;

class Mailer implements MailerInterface
{

    protected $config;

    protected $logger;

    protected $from;

    private $to;

    protected $subject;

    protected $timestamp = null;

    public $sent = false;

    protected $body = array();

    protected $bodyGlue = "\n";

    public function __construct(ConfigInterface $config, LoggerInterface $logger)
    {
        $this->config = $config;
        $this->logger = $logger;
        $to = filter_var($this->config->get('mailTo'), FILTER_SANITIZE_EMAIL);
        if (filter_var($to, FILTER_VALIDATE_EMAIL)) {
            $this->to = $to;
        } else {
            throw new JhmException('Email to address not valid: ' . $to);
        }
    }

    public function setSubject($subject)
    {
        $this->subject = trim(filter_var($subject, FILTER_SANITIZE_STRING));
    }

    public function setBody($body, $reset = false)
    {
        if ($reset) {
            $this->body = array();
        }
        $body = filter_var($body, FILTER_SANITIZE_STRING);
        if ($body) {
            $this->body[] = trim($body);
        }
    }

    public function setFromAddress($email)
    {
        $from = trim(filter_var($email, FILTER_SANITIZE_EMAIL));
        if ($from) {
            $this->from = $from;
        }
    }

    protected function getFormattedFrom()
    {
        return "-f{$this->from}";
    }

    protected function getBody()
    {
        $body = array_merge(['Date: ' . $this->_getTimeStamp()], $this->body);
        return implode($this->bodyGlue, $body);
    }

    protected function _getTimeStamp()
    {
        return $this->$this->timestamp;
    }

    public function __get($key)
    {
        switch ($key) {
            case 'subject':
            case 'from':
            case 'to':
                return $this->{$key};
            case 'timestamp':
                return $this->_getTimeStamp();
            case 'body':
                return $this->getBody();
        }
    }

    public function send()
    {

        $this->timestamp = date(DATE_RFC2822);

        if ($this->config->get('sendMail')) {
            $this->sent = $this->_send($this->to, $this->subject, $this->getBody(), $this->getFormattedFrom());
        } else {
            $this->sent = true;
        }

        if (!$this->sent) {
            $this->logger->log('WARNING', 'Could not send message');
        }

        return $this->sent;
    }

    protected function _send($to, $subject, $body, $headers)
    {
        return @mail($to, $subject, $body, $headers);
    }
}
