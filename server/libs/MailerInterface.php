<?php
namespace JHM;

interface MailerInterface
{
    public function setSubject($subject);

    public function setupSystemMailer();

    public function setRecipient($address, $name);

    public function setBody($body);

    public function setFrom($email, $name);

    public function setRelpyTo($email, $name);

    public function setupSMTP();

    public function setHTML($html = false);

    public function addAttachment($path);

    public function send();
}
