<?php
namespace JHM;

interface CsrfTokenInterface
{

    public function generateToken($formId);

    public function checkToken($token, $formId);

    public function getField();

}
