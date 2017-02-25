<?php
/**
 * digest of contact emails sent in time period
 * based on RFC 1153
 * @url http://www.faqs.org/rfcs/rfc1153.html
 */
namespace JHM;

interface MailDigestInterface
{
    public function writeMessage(MailerInterface $mailer);
}
