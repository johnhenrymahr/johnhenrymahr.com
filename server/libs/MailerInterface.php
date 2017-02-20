<?php
namespace JHM;

interface MailerInterface
{
    public function setSubject($subject);

    public function setBody($body, $reset = false);

    public function setFromAddress($email);

    public function send();
}
