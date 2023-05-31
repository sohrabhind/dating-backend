<?php

/*!
 * https://hindbyte.com
 * hindbyte@gmail.com
 *
 * Copyright 2012-2022 Demyanchuk Dmitry (hindbyte@gmail.com)
 */

class admin extends db_connect
{

	private $requestFrom = 0;
    private $id = 0;

	public function __construct($dbo = NULL)
    {
		parent::__construct($dbo);
	}

    public function getCount()
    {
        $stmt = $this->db->prepare("SELECT count(*) FROM admins");
        $stmt->execute();

        return $number_of_rows = $stmt->fetchColumn();
    }

    public function signup($username, $password, $fullname, $access_level = 0)
    {

        $result = array(
            "error" => true,
            "error_code" => ERROR_CODE_INITIATE
        );

        if (!helper::isCorrectLogin($username)) {

            $result = array(
                "error" => true,
                "error_code" => ERROR_INCORRECT_USERNAME,
                "error_type" => 0,
                "error_description" => "Incorrect login"
            );

            return $result;
        }

        if (!helper::isCorrectPassword($password)) {

            $result = array(
                "error" => true,
                "error_code" => ERROR_INCORRECT_PASSWORD,
                "error_type" => 1,
                "error_description" => "Incorrect password"
            );

            return $result;
        }

        $passw_hash = hash('sha256', $password);
        $currentTime = time();

        $stmt = $this->db->prepare("INSERT INTO admins (access_level, username, password, fullname, createAt) value (:access_level, :username, :password, :fullname, :createAt)");
        $stmt->bindParam(":access_level", $access_level, PDO::PARAM_INT);
        $stmt->bindParam(":username", $username, PDO::PARAM_STR);
        $stmt->bindParam(":password", $passw_hash, PDO::PARAM_STR);
        $stmt->bindParam(":fullname", $fullname, PDO::PARAM_STR);
        $stmt->bindParam(":createAt", $currentTime, PDO::PARAM_INT);

        if ($stmt->execute()) {

            $this->setId($this->db->lastInsertId());

            $result = array(
                "error" => false,
                'accountId' => $this->id,
                'access_level' => $access_level,
                'username' => $username,
                'password' => $password,
                'error_code' => ERROR_SUCCESS,
                'error_description' => 'SignUp Success!'
            );

            return $result;
        }

        return $result;
    }

    public function signin($username, $password)
    {
        $result = array(
            'error' => true,
            "error_code" => ERROR_CODE_INITIATE
        );

        $username = helper::clearText($username);
        $password = helper::clearText($password);

        $passw_hash = hash('sha256', $password);

            $stmt2 = $this->db->prepare("SELECT * FROM admins WHERE username = (:username) AND password = (:password) LIMIT 1");
            $stmt2->bindParam(":username", $username, PDO::PARAM_STR);
            $stmt2->bindParam(":password", $passw_hash, PDO::PARAM_STR);
            $stmt2->execute();

            if ($stmt2->rowCount() > 0) {

                $row2 = $stmt2->fetch();

                $result = array(
                    "error" => false,
                    "error_code" => ERROR_SUCCESS,
                    "accountId" => $row2['id'],
                    "access_level" => $row2['access_level'],
                    "username" => $row2['username'],
                    "fullname" => $row2['fullname']
                );

            }

        return $result;
    }

    public function setPassword($password, $newPassword)
    {
        $result = array(
            'error' => true,
            'error_code' => ERROR_CODE_INITIATE
        );

        if (!helper::isCorrectPassword($password)) {

            return $result;
        }

        if (!helper::isCorrectPassword($newPassword)) {

            return $result;
        }

        $passw_hash = hash('sha256', $password);

            $stmt2 = $this->db->prepare("SELECT id FROM admins WHERE id = (:adminId) AND password = (:password) LIMIT 1");
            $stmt2->bindParam(":adminId", $this->id, PDO::PARAM_INT);
            $stmt2->bindParam(":password", $passw_hash, PDO::PARAM_STR);
            $stmt2->execute();

            if ($stmt2->rowCount() > 0) {

                $this->newPassword($newPassword);

                $result = array(
                    "error" => false,
                    "error_code" => ERROR_SUCCESS
                );
            }

        return $result;
    }

    public function newPassword($password)
    {
        $newHash = hash('sha256', $password);

        $stmt = $this->db->prepare("UPDATE admins SET password = (:newHash) WHERE id = (:adminId)");
        $stmt->bindParam(":adminId", $this->id, PDO::PARAM_INT);
        $stmt->bindParam(":newHash", $newHash, PDO::PARAM_STR);
        $stmt->execute();
    }

    public function get()
    {
        $result = array(
            "error" => true,
            "error_code" => ERROR_ACCOUNT_ID
        );

        $stmt = $this->db->prepare("SELECT * FROM admins WHERE id = (:id) LIMIT 1");
        $stmt->bindParam(":id", $this->id, PDO::PARAM_INT);

        if ($stmt->execute()) {

            if ($stmt->rowCount() > 0) {

                $row = $stmt->fetch();

                $result = array(
                    "error" => false,
                    "error_code" => ERROR_SUCCESS,
                    "id" => $row['id'],
                    "access_level" => $row['access_level'],
                    "username" => $row['username'],
                    "fullname" => stripcslashes($row['fullname']),
                    "ip_addr" => $row['ip_addr'],
                    "createAt" => $row['createAt'],
                    "createDate" => date("Y-m-d H:i:s", $row['createAt']),
                );

            }
        }

        return $result;
    }

    public function remove()
    {
        $currentTime = time();

        $stmt = $this->db->prepare("UPDATE admins SET removeAt = (:removeAt) WHERE id = (:adminId)");
        $stmt->bindParam(":removeAt", $currentTime, PDO::PARAM_INT);
        $stmt->bindParam(":adminId", $this->id, PDO::PARAM_INT);
        $stmt->execute();
    }

    public function getAdminsList()
    {
        $result = array(
            "error" => false,
            "error_code" => ERROR_SUCCESS,
            "items" => array()
        );

        $stmt = $this->db->prepare("SELECT * FROM admins");

        if ($stmt->execute()) {

            while ($row = $stmt->fetch()) {

                array_push($result['items'], array(

                        "error" => false,
                        "error_code" => ERROR_SUCCESS,
                        "id" => $row['id'],
                        "access_level" => $row['access_level'],
                        "username" => $row['username'],
                        "fullname" => stripcslashes($row['fullname']),
                        "ip_addr" => $row['ip_addr'],
                        "createAt" => $row['createAt'],
                        "createDate" => date("Y-m-d H:i:s", $row['createAt']),
                        "removeAt" => $row['removeAt'],
                        "removeDate" => date("Y-m-d H:i:s", $row['removeAt']),
                    )
                );
            }
        }

        return $result;
    }


    public function authorize($accountId, $accessToken)
    {
        $accountId = helper::clearInt($accountId);

        $accessToken = helper::clearText($accessToken);
        $accessToken = helper::escapeText($accessToken);

        $stmt = $this->db->prepare("SELECT id FROM admins_data WHERE accountId = (:accountId) AND accessToken = (:accessToken) AND removeAt = 0 LIMIT 1");
        $stmt->bindParam(":accountId", $accountId, PDO::PARAM_INT);
        $stmt->bindParam(":accessToken", $accessToken, PDO::PARAM_STR);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {

            return true;
        }

        return false;
    }

    public function removeAuthorization($accountId, $accessToken)
    {
        $accountId = helper::clearInt($accountId);

        $accessToken = helper::clearText($accessToken);
        $accessToken = helper::escapeText($accessToken);

        $currentTime = time(); //current time

        $stmt = $this->db->prepare("UPDATE admins_data SET removeAt = (:removeAt) WHERE accountId = (:accountId) AND accessToken = (:accessToken)");
        $stmt->bindParam(":accountId", $accountId, PDO::PARAM_INT);
        $stmt->bindParam(":accessToken", $accessToken, PDO::PARAM_STR);
        $stmt->bindParam(":removeAt", $currentTime, PDO::PARAM_INT);

        if ($stmt->execute()) {

            return true;
        }

        return false;
    }

    public function removeAllAuthorizations($accountId)
    {
        $accountId = helper::clearInt($accountId);

        $currentTime = time(); //current time

        $stmt = $this->db->prepare("UPDATE admins_data SET removeAt = (:removeAt) WHERE accountId = (:accountId)");
        $stmt->bindParam(":accountId", $accountId, PDO::PARAM_INT);
        $stmt->bindParam(":removeAt", $currentTime, PDO::PARAM_INT);

        if ($stmt->execute()) {

            return true;
        }

        return false;
    }

    public function getAuthorizationId($accountId, $accessToken)
    {
        $accountId = helper::clearInt($accountId);

        $accessToken = helper::clearText($accessToken);
        $accessToken = helper::escapeText($accessToken);

        $stmt = $this->db->prepare("SELECT id FROM admins_data WHERE accountId = (:accountId) AND accessToken = (:accessToken) AND removeAt = 0 LIMIT 1");
        $stmt->bindParam(":accountId", $accountId, PDO::PARAM_INT);
        $stmt->bindParam(":accessToken", $accessToken, PDO::PARAM_STR);
        $stmt->execute();

        if ($stmt->execute()) {

            if ($stmt->rowCount() > 0) {

                $row = $stmt->fetch();

                return $row['id'];
            }
        }

        return 0;
    }

    public function updateAuthorizationId($auth_id, $fcm_regId = "")
    {
        $stmt = $this->db->prepare("UPDATE admins_data SET fcm_regId = (:fcm_regId) WHERE id = (:id)");
        $stmt->bindParam(":id", $auth_id, PDO::PARAM_INT);
        $stmt->bindParam(":fcm_regId", $fcm_regId, PDO::PARAM_STR);
        $stmt->execute();
    }

    public function createAuthorization($accountId, $app_type = 0, $fcm_regId = "")
    {
        $result = array(
            "error" => true,
            "error_code" => ERROR_CODE_INITIATE
        );

        $currentTime = time();	// Current time

        $ip_addr = helper::ip_addr();

        $accessToken = md5(uniqid(rand(), true));

        $stmt = $this->db->prepare("INSERT INTO admins_data (accountId, accessToken, fcm_regId, appType, createAt, ip_addr) value (:accountId, :accessToken, :fcm_regId, :appType, :createAt, :ip_addr)");
        $stmt->bindParam(":accountId", $accountId, PDO::PARAM_INT);
        $stmt->bindParam(":accessToken", $accessToken, PDO::PARAM_STR);
        $stmt->bindParam(":fcm_regId", $fcm_regId, PDO::PARAM_STR);
        $stmt->bindParam(":appType", $app_type, PDO::PARAM_INT);
        $stmt->bindParam(":createAt", $currentTime, PDO::PARAM_INT);
        $stmt->bindParam(":ip_addr", $ip_addr, PDO::PARAM_STR);

        if ($stmt->execute()) {

            $result = array(
                'error'=> false,
                'error_code' => ERROR_SUCCESS,
                'accessToken' => $accessToken,
                'accountId' => $accountId
            );

        }

        return $result;
    }

    public function setId($accountId)
    {
        $this->id = $accountId;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setRequestFrom($requestFrom)
    {
        $this->requestFrom = $requestFrom;
    }

    public function getRequestFrom()
    {
        return $this->requestFrom;
    }

    static function isSession()
    {
        if (isset($_SESSION) && isset($_SESSION['admin_id'])) {

            return true;

        } else {

            return false;
        }
    }

    static function getCurrentAdminId()
    {
        if (isset($_SESSION) && isset($_SESSION['admin_id'])) {

            return $_SESSION['admin_id'];

        } else {

            return 0;
        }
    }

    static function setSession($admin_id, $access_token, $username = "", $fullname = "", $access_level = 0)
    {
        $_SESSION['admin_id'] = $admin_id;
        $_SESSION['admin_access_token'] = $access_token;
        $_SESSION['admin_username'] = $username;
        $_SESSION['admin_fullname'] = $fullname;
        $_SESSION['admin_access_level'] = $access_level;
    }

    static function unsetSession()
    {
        unset($_SESSION['admin_id']);
        unset($_SESSION['admin_access_token']);
        unset($_SESSION['admin_username']);
        unset($_SESSION['admin_fullname']);
        unset($_SESSION['admin_access_level']);
    }

    static function getFullname()
    {
        if (isset($_SESSION) && isset($_SESSION['admin_fullname'])) {

            return $_SESSION['admin_fullname'];

        } else {

            return "undefined";
        }
    }

    static function getUsername()
    {
        if (isset($_SESSION) && isset($_SESSION['admin_username'])) {

            return $_SESSION['admin_username'];

        } else {

            return "undefined";
        }
    }

    static function getAccessToken()
    {
        if (isset($_SESSION) && isset($_SESSION['admin_access_token'])) {

            return $_SESSION['admin_access_token'];

        } else {

            return "undefined";
        }
    }

    static function getAccessLevel()
    {
        if (isset($_SESSION) && isset($_SESSION['admin_access_level'])) {

            return $_SESSION['admin_access_level'];

        } else {

            return ADMIN_ACCESS_LEVEL_READ_ONLY_RIGHTS;
        }
    }

    static function createAccessToken()
    {
        $access_token = md5(uniqid(rand(), true));

        if (isset($_SESSION)) {

            $_SESSION['admin_access_token'] = $access_token;
        }
    }
}

