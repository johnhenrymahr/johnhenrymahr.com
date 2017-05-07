<?php

namespace JHM;

use Symfony\Component\HttpFoundation\ParameterBag;

abstract class PostValidator
{

    protected $requiredFields = [];

    protected $honeyPotField = '';

    protected function _validate(ParameterBag $request)
    {
        $keys = $request->keys();
        if (empty($keys)) {
            return false;
        }

        $diff = array_diff($this->requiredFields, $keys);

        if (!empty($diff)) {
            return false;
        }

        $honeyPot = (empty($this->honeyPotField)) ? false : $this->honeyPotField;

        foreach ($keys as $key) {
            if (strpos(strtolower($key), 'email') !== false && $this->_validateEmail($request->get($key)) === false) {
                return false;
            }
            if (in_array($key, $this->requiredFields) && empty($request->get($key))) {
                return false;
            }
            if ($honeyPot && $key === $honeyPot && !empty($request->get($honeyPot))) {
                return false;
            }
        }

        return true;
    }

    /**
     * validate email address
     * @access private
     * @param email string
     * @return boolean valid
     */
    private function _validateEmail($email)
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }
        $email_domain = preg_replace('/^.+?@/', '', $email) . '.';
        if (function_exists('checkdnsrr') && !checkdnsrr($email_domain, 'MX') && !checkdnsrr($email_domain, 'A')) {
            return false;
        }
        return true;
    }
}
