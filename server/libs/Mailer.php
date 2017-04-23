<?php

namespace JHM;

class Mailer implements MailerInterface
{

    public $body = ''; //text body only, for logging

    public $subject = '';

    public $timestamp;

    public $fromAddress;

    public $fromName = '';

    public $replyAddress;

    public $replyName = '';

    public $toAddress;

    public $toName = '';

    public $noReplyAddress = 'noreply@johnhenrymahr.com';

    public $noReplyName = 'JHM Auto Mailer';

    protected $mailerEngine;

    protected $config;

    protected $logger;

    protected $html = false;

    public function __construct(\PhpMailer $mailer, ConfigInterface $config, LoggerInterface $logger)
    {
        $this->config = $config;
        $this->logger = $logger;
        $this->mailerEngine = $mailer;
        $this->mailerEngine->Body = '';
        $this->mailerEngine->AltBody = '';
    }

    public function setHTML($html = false)
    {
        $this->html = (bool) $html;
        $this->mailerEngine->isHTML($this->html);
    }

    public function setupSystemMailer()
    {
        $this->toAddress = $toAddress = filter_var($this->config->get('systemMailTo'), FILTER_SANITIZE_EMAIL);
        $this->toName = $toName = $this->config->get('systemMailToName');
        if (filter_var($toAddress, FILTER_VALIDATE_EMAIL)) {
            $this->mailerEngine->addAddress($toAddress, $toName);
        } else {
            throw new JhmException('Email to address not valid: ' . $toAddress);
        }
    }

    public function setupNoReply()
    {
        $this->setFrom($this->noReplyAddress, $this->noReplyName);
    }

    public function setRecipient($address, $name)
    {
        if (filter_var($address, FILTER_VALIDATE_EMAIL)) {
            $this->mailerEngine->addAddress($address, $name);
        } else {
            throw new JhmException('Email to address not valid: ' . $address);
        }
    }

    public function setupSMTP()
    {
        if ($this->config->get('smtp.enabled')) {
            $this->mailerEngine->isSMTP();
            $this->mailerEngine->Host = $this->config->get('smtp.hostname');
            $this->mailerEngine->Username = $this->config->get('smtp.username');
            $this->mailerEngine->Password = $this->config->get('smtp.password');
            $this->mailerEngine->SMPTSecure = 'tls';
            $this->mailerEngine->Port = 587;
        }
    }

    public function setSubject($subject)
    {
        $this->mailerEngine->Subject = $this->subject = trim(filter_var($subject, FILTER_SANITIZE_STRING));
    }

    protected function setTextBody($body)
    {
        $body = filter_var($body, FILTER_SANITIZE_STRING);
        if ($body) {
            $this->body = $body;
            if ($this->html) {
                $this->mailerEngine->AltBody .= $body;
            } else {
                $this->mailerEngine->Body .= $body;
            }
        }
    }

    protected function setHtmlBody($body)
    {
        $this->mailerEngine->Body .= $body;
    }

    public function setBody($body)
    {
        if ($this->html) {
            $this->setHtmlBody($body);
        } else {
            $this->setTextBody($body);
        }
    }

    public function addAttachment($filename)
    {
        $docs = $this->config->getStorage('docs');
        $path = $docs . $filename;
        if (is_readable($path)) {
            $this->mailerEngine->addAttachment($path);
        }
    }

    public function setFrom($email, $name = '')
    {
        $from = trim(filter_var($email, FILTER_SANITIZE_EMAIL));
        $name = trim(filter_var($name, FILTER_SANITIZE_STRING));
        if (filter_var($from, FILTER_VALIDATE_EMAIL)) {
            $this->fromAddress = $from;
            $this->fromName = $name;
            $this->mailerEngine->setFrom($from, $name);
        }
    }

    public function setRelpyTo($email, $name)
    {
        $from = trim(filter_var($email, FILTER_SANITIZE_EMAIL));
        $name = trim(filter_var($name, FILTER_SANITIZE_STRING));
        if (filter_var($from, FILTER_VALIDATE_EMAIL)) {
            $this->replyAddress = $from;
            $this->replyName = $name;
            $this->mailerEngine->addReplyTo($from, $name);
        }
    }

    protected function injectTimeStamp()
    {

        $timestamp = $this->timestamp = date(DATE_RFC2822);
        if ($this->html) {
            $this->mailerEngine->Body .= '<p style="color: gray; font-style: italic">JHM Mailsystem sent: ' . $timestamp . '</p>';
        } else {
            $this->mailerEngine->Body .= "\nJHM Mailsystem sent: {$timestamp}\n";
        }
    }

    protected function setAltBody()
    {
        $html = new \Html2Text\Html2Text($this->mailerEngine->Body);
        $this->setTextBody($html->getText());
    }

    public function send($timestamp = false)
    {
        if ($timestamp) {
            $this->injectTimeStamp();
        }
        if ($this->html) {
            $this->setAltBody();
        }
        return $this->mailerEngine->send();
    }
}
