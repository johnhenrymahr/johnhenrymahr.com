<?php
namespace JHM;

class ContactStorage extends DbStorage implements ContactStorageInterface
{
    public function addContact($email, $name, $phone = null, $company = null)
    {
        if ($this->isEmail($email) && $this->isStringVar($name)) {
            $data = array(
                'email' => trim($email),
                'name' => trim($name),
            );
            if ($this->isStringVar($phone)) {
                $data['phone'] = trim($phone);
            }
            if ($this->isStringVar($company)) {
                $data['company'] = trim($company);
            }
            $this->db->where('email', $email);
            $result = $this->db->getOne('contact');
            if ($result && $result['id']) {
                $this->db->where('id', $result['id']);
                $this->db->update('contact', $data);
                return $result['id'];
            } else {
                return $this->db->insert('contact', $data);
            }
        }
        return false;
    }

    public function addMessage($cid, $topic, $message)
    {
        if ($cid && $this->isStringVar($topic) && $this->isStringVar($message)) {
            return $this->db->insert('message', array(
                'cid' => $cid,
                'topic' => $topic,
                'message' => $message,
            ));
        }
        return false;
    }

    public function addDownloadRecord($cid, $email, $fileId) {
        $storagePath = $this->config->getStorage('downloads').$fileId;
        if (is_readable($storagePath)) {
            $this->db->where('cid', $cid);
            $this->db->where('active', '1');
            $result = $this->db->getOne('download');
            if ($result['token']) {
                return $result['token'];
            } else {
                $newToken = $this->generateToken($email);
                $id = $this->db->insert('download', [
                    'cid' => $cid,
                    'token' => $newToken,
                    'fileId' => $fileId
                ]);
                if ($id) {
                    return $newToken;
                } else {
                    $this->logger->log('ERROR', 'Could not insert download', ['lasterror' => $this->db->getLastError()]);
                    return false;
                }   
            }
        }
        return false;
    }

    protected function generateToken ($email) {
        $salt = uniqid(mt_rand(), true);
        return sha1($email.$salt);
    }

    protected function isStringVar($var)
    {
        return (is_string($var) && !empty($var));
    }

    protected function isEmail($var)
    {
        return filter_var($var, FILTER_VALIDATE_EMAIL);
    }
}
