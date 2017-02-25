<?php

namespace JHM;

class MailDigest extends FileStorage implements MailDigestInterface
{

    protected $config;

    protected $logger;

    protected $filePath;

    public function __construct(ConfigInterface $config, LoggerInterface $logger)
    {
        $this->config = $config;
        $this->logger = $logger;
        $digestPath = $this->config->getStorage('digest');
        $this->filePath = $this->setupStorage($digestPath);
        if ($this->filePath === false) {
            $this->logger->log('WARNING', 'Could not write mail digest. Path: ' . $digestPath);
            throw new JhmException('Could not write to digest path');
        }
    }

    public function writeMessage(MailerInterface $mailer)
    {
        $message = "Date: {$mailer->timestamp}\n";
        $message .= "From: {$mailer->from}\n";
        $message .= "To: {$mailer->to}\n";
        $message .= "Subject: {$mailer->subject}\n\n";
        $message .= "{$mailer->body}\n";
        $message .= "------------------------------\n\n";

        if ($this->_writeToFIle($message)) {
            return true;
        } else {
            $this->logger->log('WARNING', 'Could not write mail digest.');
            return false;
        }
    }

    protected function _writeToFIle($contents)
    {
        return @file_put_contents($this->filePath . $this->_getDigestName(), $contents, FILE_APPEND);
    }

    protected function _getDigestName()
    {
        return date("Y_W") . '__digest';
    }
}
