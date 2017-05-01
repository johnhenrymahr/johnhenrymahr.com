<?php
namespace JHM;

interface ContactStorageInterface
{

    public function addContact($email, $name, $phone = null, $company = null);

    public function addMessage($cid, $topic, $message);

}
