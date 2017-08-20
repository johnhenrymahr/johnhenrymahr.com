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
            $updateResult = $this->db->update('contact', $data);
            if (!$updateResult) {
                $this->logError('Could not update contact record');
            }
            return $result['id'];
        } else {
            $insertId = $this->db->insert('contact', $data);
            if (!$insertId) {
                $this->logError('Could not insert new contact record.');
                return false;
            }
            return $insertId;
        }
    }

    public function addMessage($cid, $topic, $message)
    {
        if ($cid && $this->isStringVar($topic) && $this->isStringVar($message)) {
            $result = $this->db->insert('message', array(
                'cid' => $cid,
                'topic' => $topic,
                'message' => $message,
            ));
            if (!$result) {
                $this->logError('Could not insert message');
                return false;
            }
            return $result;
        }
        return false;
    }

    protected function _deactivateOldRecords()
    {
        $this->db->where('created < (NOW() - INTERVAL 10 DAY)');
        $result = $this->db->update('download', array('active' => '0'));
        return $result;
    }

    public function activateDownloadToken($id)
    {
        $this->db->where('id', $id);
        $result = $this->db->update('download', array("active" => 1));
        if ($result && $this->db->count) {
            return true;
        } else {
            $this->logError('Could not update token');
            return false;
        }
    }
    /*
     * getInactiveToken
     * @string $token
     * return @object || false
     * get download record joined to contact id
     */
    public function getInactiveToken($token)
    {
        $this->db->where('token', $token);
        $this->db->where('active', '0');
        $this->db->join("contact c", "d.cid=c.id", "LEFT");
        $record = $this->db->get('download d', null, 'c.name, c.email, d.*');
        if ($record && is_array($record) && !empty($record)) {
            return $record[0];
        } else {
            $this->logger->logError('Could not get inactive download record');
            return false;
        }
    }

    public function validateDownloadToken($token)
    {
        $this->_deactivateOldRecords();
        $this->db->where('token', $token);
        $this->db->where('active', '1');
        $record = $this->db->get('download');

        if ($record && is_array($record) && !empty($record)) {
            $max = $this->config->get('downloads.cvMax');
            $record = $record[0];
            if (isset($record['access']) && $record['access'] > $max) {
                $this->db->where('token', $token);
                $record['active'] = '0';
                $result = $this->db->update('download', $record);
                if (!$result || !$this->db->count) {
                    $this->logError('Could not update download record.');
                }
                return false;
            } else {
                $this->db->where('token', $token);
                if (!isset($record['access'])) {
                    $record['access'] = 0;
                }
                $data = array('access' => $record['access']++);
                $result = $this->db->update('download', $record);
                if (!$result || !$this->db->count) {
                    $this->logError('Could not update download record.');
                }
                return $record;
            }
        } else {
            return false;
        }
    }

    public function removeDownloadToken($token)
    {
        $this->db->where('token', $token);
        $result = $this->db->delete('download');
        if (!$result) {
            $this->logError('Could not delete download record.');
        }
        return $result;
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
                    $this->logError('Could not insert download');
                    return false;
                }
            }
        }
        return false;
    }

    protected function logError($message)
    {
        $this->logger->log('ERROR', $message, ['lastError' => $this->db->getLastError(), 'lastQuery' => $this->db->getLastQuery()]);
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
