<?php

namespace JHM;

use Symfony\Component\HttpFoundation\ParameterBag;

abstract class PostValidator
{

    protected $requiredFields = [];

    protected $honeyPotField = '';

    protected $csrfField = 'pr_id';

    protected function _validatorLogger($message, $context = array(), $level = 'WARNING')
    {
        if (property_exists($this, 'logger') && method_exists($this->logger, 'log')) {
            $this->logger->log($level, $message, $context);
        }
    }

    protected function _validate(ParameterBag $request, $formId = '')
    {

        $csrfField = $this->csrfToken->getField();

        if (!$this->csrfToken->checkToken($request->get($csrfField), $formId)) {
            return false;
        }

        $keys = $request->keys();
        if (empty($keys)) {
            $this->_validatorLogger('Field Validation: no fields found');
            return false;
        }

        $diff = array_diff($this->requiredFields, $keys);

        if (!empty($diff)) {
            $this->_validatorLogger('Field Validation: required fields missing.', $diff);
            return false;
        }

        $honeyPot = (empty($this->honeyPotField)) ? false : $this->honeyPotField;

        foreach ($keys as $key) {
            if (strpos(strtolower($key), 'email') !== false && $this->_validateEmail($request->get($key)) === false) {
                $this->_validatorLogger('Email Validation: no email address');
                return false;
            }
            if (in_array($key, $this->requiredFields) && empty($request->get($key))) {
                $this->_validatorLogger('Field Validation: required field empty: ' . $key);
                return false;
            }
            if ($honeyPot && $key === $honeyPot && !empty($request->get($honeyPot))) {
                $this->_validatorLogger('Field Validation: honey pot field present: ' . $key);
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
            $this->_validatorLogger('Email Validation: invalid address', array('address' => $email));
            return false;
        }
        $email_domain = preg_replace('/^.+?@/', '', $email) . '.';
        if (function_exists('checkdnsrr') && !checkdnsrr($email_domain, 'MX') && !checkdnsrr($email_domain, 'A')) {
            $this->_validatorLogger('Email Validation: No DNS record found for address', array('address' => $email, 'domain' => $email_domain));
            return false;
        }
        return true;
    }
}
