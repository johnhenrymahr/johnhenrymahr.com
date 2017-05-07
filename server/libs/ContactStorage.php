<?php
namespace JHM;

class ContactStorage extends DbStorage implements ContactStorageInterface
{
    public function addContact($email, $name, $company = null, $phone = null)
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
            return $this->_createUpdateContact($email, $data);
        }
        return false;
    }

    protected function _createUpdateContact($email, array $data = [])
    {
        $this->db->where('email', $email);
        $result = $this->db->getOne('contact');
        if ($result && is_array($result) && isset($result['id']) && $result['id']) {
            $this->db->where('id', $result['id']);
            $this->db->update('contact', $data);
            return $result['id'];
        } else {
            return $this->db->insert('contact', $data);
        }
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

    protected function _deactivateOldRecords()
    {
        $this->db->where('WHERE created < (NOW() - INTERVAL 3 DAY)');
        return $this->db->update('download', array('active' => '0'));
    }

    public function validateDownloadToken($token)
    {
        $this->_deactivateOldRecords();
        $this->db->where('token', $token);
        $this->db->where('active', '1');
        $record = $this->db->getOne('download');
        if ($record && is_array($record) && isset($record['token']) && isset($record['fileId'])) {
            $max = $this->config->get('downloads.cvMax');
            if ($record['access'] > $max) {
                $this->db->where('token', $token);
                $record['active'] = '0';
                $this->db->update('download', $record);
                return false;
            } else {
                $this->db->where('token', $token);
                $data = array('access' => $record['access']++);
                $this->db->update('download', $record);
                return $record;
            }
        } else {
            return false;
        }
    }

    public function removeDownloadToken($token)
    {
        $this->db->where('token', $token);
        return $this->db->delete('download');
    }

    public function addDownloadRecord($cid, $email, $fileId, $fileMimeType = null)
    {
        $storagePath = $this->config->getStorage('downloads') . $fileId;
        if (is_readable($storagePath)) {
            $this->db->where('cid', $cid);
            $this->db->where('active', '1');
            $result = $this->db->getOne('download');
            if (isset($result['token']) && !empty($result['token'])) {
                return $result['token'];
            } else {
                $newToken = $this->generateToken($email);
                $data = [
                    'cid' => $cid,
                    'token' => $newToken,
                    'fileId' => $fileId,
                    'md5_hash' => md5_file($storagePath),
                ];
                if ($fileMimeType) {
                    $data['fileMimeType'] = $fileMimeType;
                }
                $id = $this->db->insert('download', $data);
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

    protected function generateToken($email)
    {
        $salt = uniqid(mt_rand(), true);
        return sha1($email . $salt);
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
