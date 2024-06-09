<?php

class helper extends db_connect
{
    public function __construct($dbo = null)
    {
        parent::__construct($dbo);
    }

    public static function isValidURL($url)
    {

        return preg_match('|^(http(s)?://)?[a-z0-9-]+\.(.[a-z0-9-]+)+(:[0-9]+)?(/.*)?$|i', $url);
    }

    public static function truncate($str, $len)
    {
        $tail = max(0, $len-10);
        $trunk = substr($str, 0, $tail);
        $trunk .= strrev(preg_replace('~^..+?[\s,:]\b|^...~', '...', strrev(substr($str, $tail, $len-$tail))));

        return $trunk;
    }

    public static function createMsgClickableLinks($matches)
    {
        $title = $face = $matches[0];

        $face = helper::truncate($face, 50);

        $matches[0] = str_replace("www.", "http://www.", $matches[0]);
        $matches[0] = str_replace("http://http://www.", "http://www.", $matches[0]);
        $matches[0] = str_replace("https://http://www.", "https://www.", $matches[0]);

        return "<a title=\"$title\" class=\"posted_link\" target=\"_blank\" href=$matches[0]>$face</a>";
    }

    public static function processMsgText($text)
    {
        $text = preg_replace_callback('@(?<=^|(?<=[^a-zA-Z0-9-_\.//]))((https?://)?([-\w]+\.[-\w\.]+)+\w(:\d+)?(/([-\w/_\.\,]*(\?\S+)?)?)*)@', "helper::createMsgClickableLinks", $text);

        return $text;
    }

    public function getUserLogin($accountId)
    {
        $stmt = $this->db->prepare("SELECT username FROM users WHERE id = (:id) LIMIT 1");
        $stmt->bindParam(":id", $accountId);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {

            $row = $stmt->fetch();

            return $row['username'];
        }

        return 0;
    }

    public function getUserPhoto($accountId)
    {
        $stmt = $this->db->prepare("SELECT bigPhotoUrl FROM users WHERE id = (:id) LIMIT 1");
        $stmt->bindParam(":id", $accountId);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {

            $row = $stmt->fetch();

            if (strlen($row['bigPhotoUrl']) == 0) {

                return "/assets/icons/profile_default_photo.png";

            } else {

                return $row['bigPhotoUrl'];
            }
        }

        return "/assets/icons/profile_default_photo.png";
    }

    public function getUserId($username)
    {
        $username = helper::clearText($username);
        $username = helper::escapeText($username);

        $stmt = $this->db->prepare("SELECT id FROM users WHERE username = (:username) LIMIT 1");
        $stmt->bindParam(":username", $username);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {

            $row = $stmt->fetch();

            return $row['id'];
        }

        return 0;
    }

    public function getUserIdByGoogle($google_id)
    {
        $google_id = helper::clearText($google_id);
        $google_id = helper::escapeText($google_id);

        $stmt = $this->db->prepare("SELECT id FROM users WHERE gl_id = (:gl_id) LIMIT 1");
        $stmt->bindParam(":gl_id", $google_id);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {

            $row = $stmt->fetch();

            return $row['id'];
        }

        return 0;
    }



    public function getUserIdByEmail($email)
    {
        $stmt = $this->db->prepare("SELECT id FROM users WHERE email = (:email) LIMIT 1");
        $stmt->bindParam(":email", $email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {

            $row = $stmt->fetch();

            return $row['id'];
        }

        return 0;
    }

    public function getRestorePoint($hash)
    {
        $hash = helper::clearText($hash);
        $hash = helper::escapeText($hash);

        $result = array("error" => true,
                        "error_code" => ERROR_CODE_INITIATE);

        $stmt = $this->db->prepare("SELECT * FROM restore_data WHERE hash = (:hash) AND removeAt = 0 LIMIT 1");
        $stmt->bindParam(":hash", $hash);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {

            $row = $stmt->fetch();

            $result = array('error'=> false,
                            'error_code' => ERROR_SUCCESS,
                            'accountId' => $row['accountId'],
                            'hash' => $row['hash'],
                            'email' => $row['email']);
        }

        return $result;
    }

    public function isEmailExists($user_email)
    {
        $stmt = $this->db->prepare("SELECT id FROM users WHERE email = (:email) LIMIT 1");
        $stmt->bindParam(':email', $user_email);
        if ($stmt->execute()) {
            if ($stmt->rowCount() > 0) {
                return true;
            }
        }
        return false;
    }

    public function isUserExists($username)
    {
        $username = helper::clearText($username);
        $username = helper::escapeText($username);

        $stmt = $this->db->prepare("SELECT id FROM users WHERE username = (:username) LIMIT 1");
        $stmt->bindParam(":username", $username);

        if ($stmt->execute()) {
            if ($stmt->rowCount() > 0) {
                return true;
            }
        }
        return false;
    }

    public function isPhoneNumberExists($phoneNumber)
    {
        $phoneNumber = helper::clearText($phoneNumber);
        $phoneNumber = helper::escapeText($phoneNumber);

        $stmt = $this->db->prepare("SELECT id FROM users WHERE otpPhone = (:otpPhone) LIMIT 1");
        $stmt->bindParam(":otpPhone", $phoneNumber);

        if ($stmt->execute()) {

            if ($stmt->rowCount() > 0) {

                return true;
            }
        }

        return false;
    }

    public static function getContent($url_address)
    {

        if (function_exists('curl_init') && function_exists('curl_setopt') && function_exists('curl_exec') && function_exists('curl_exec')) {

            $curl = curl_init($url_address);

            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_HEADER, 0);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);
            curl_setopt($curl, CURLOPT_TIMEOUT, 5);

            $result = @curl_exec($curl);

            curl_close($curl);

        } else {

            ini_set('default_socket_timeout', 5);

            $result = @file_get_contents($url_address);
        }

        return $result;
    }


    public static function isCorrectFullname($fullname)
    {
        if (strlen($fullname) > 0) {
            return true;
        }
        return false;
    }

    public static function isCorrectLogin($username)
    {
        if (preg_match("/^.{8,64}$/i", $username)) {

            return true;
        }

        return true;
    }

    public static function isCorrectPassword($password)
    {
        if (preg_match('/^.{6,64}$/i', $password)) {
            return true;
        }
        return false;
    }

    public static function isCorrectEmail($email)
    {
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return true;
        }
        return false;
    }

    public static function getLang($language)
    {
        $languages = array("en",
                           "ru",
                           "id");

        $result = "en";

        foreach($languages as $value) {

            if ($value === $language) {

                $result = $language;

                break;
            }
        }

        return $result;
    }


    public static function escapeText($text)
    {
        if (APP_MYSQLI_EXTENSION) {

            $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

            $text = $mysqli->real_escape_string($text);
        }

        return $text;
    }


    public static function clearText($text)
    {
        $text = trim($text);
        $text = strip_tags($text);
        return $text;
    }

public static function clearInt($value)
{

    $value = intval($value);

    return $value;
}


    public static function ip_addr()
    {
        (string) $ip_addr = 'undefined';

        if (isset($_SERVER['REMOTE_ADDR'])) {
            $ip_addr = $_SERVER['REMOTE_ADDR'];
        }

        return $ip_addr;
    }

    public static function u_agent()
    {
        (string) $u_agent = 'undefined';

        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            $u_agent = $_SERVER['HTTP_USER_AGENT'];
        }

        return $u_agent;
    }

    public static function generateId($n = 2)
    {
        $key = '';
        $pattern = '123456789';
        $counter = strlen($pattern) - 1;

        for ($i = 0; $i < $n; $i++) {

            $key .= $pattern[rand(0, $counter)];
        }

        return $key;
    }

    public static function generateHash($n = 32)
    {
        $key = '';
        $pattern = '123456789abcdef';
        $counter = strlen($pattern) - 1;

        for ($i = 0; $i < $n; $i++) {
            $key .= $pattern[rand(0, $counter)];
        }

        return $key;
    }

    public static function generateSalt($n = 3)
    {
        $key = '';
        $pattern = '1234567890abcdef';
        $counter = strlen($pattern)-1;
        for ($i=0; $i<$n; $i++) {
            $key .= $pattern[rand(0, $counter)];
        }
        return $key;
    }

    public static function declOfNum($number, $titles)
    {
        $cases = array(2, 0, 1, 1, 1, 2);
        return $number.' '.$titles[ ($number%100>4 && $number%100<20) ? 2 : $cases[($number%10<5) ? $number%10 : 5] ];
    }

    public static function newAuthenticityToken()
    {

        $authenticity_token = md5(uniqid(rand(), true));

        if (isset($_SESSION)) {

            $_SESSION['authenticity_token'] = $authenticity_token;
        }
    }

    public static function getAuthenticityToken()
    {
        if (isset($_SESSION) && isset($_SESSION['authenticity_token'])) {

            return $_SESSION['authenticity_token'];

        } else {

            return null;
        }
    }
}
