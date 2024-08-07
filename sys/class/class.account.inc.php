<?php



class account extends db_connect
{
    private $id = 0;

    public function __construct($dbo = NULL, $accountId = 0)
    {

        parent::__construct($dbo);

        $this->setId($accountId);
    }

    public function signup($username, $fullname, $password, $email, $gender, $access_level, $u_age, $bio, $interests, $country = '')
    {

        $result = array("error" => true);

        $helper = new helper($this->db);

        if (!helper::isCorrectLogin($username)) {

            $result = array(
                "error" => true,
                "error_code" => ERROR_INCORRECT_USERNAME,
                "error_type" => 0,
                "error_description" => "Incorrect login"
            );

            return $result;
        }

        if ($helper->isUserExists($username)) {
            $result = array(
                "error" => true,
                "error_code" => ERROR_LOGIN_TAKEN,
                "error_type" => 0,
                "error_description" => "Login already taken"
            );

            return $result;
        }

        if (empty($fullname)) {

            $result = array(
                "error" => true,
                "error_code" => ERROR_EMPTY_FULL_NAME,
                "error_type" => 3,
                "error_description" => "Empty user full name"
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

        if (!helper::isCorrectEmail($email)) {

            $result = array(
                "error" => true,
                "error_code" => ERROR_INCORRECT_EMAIL,
                "error_type" => 2,
                "error_description" => "Wrong email"
            );

            return $result;
        }

        if ($helper->isEmailExists($email)) {

            $result = array(
                "error" => true,
                "error_code" => ERROR_EMAIL_TAKEN,
                "error_type" => 2,
                "error_description" => "User with this email is already registered"
            );

            return $result;
        }

        if ($gender < 0 || $gender > 1) {
            //0 = male //1= female//2 = other
            $gender = 1; // Default gender. 2 = other
        }

        if ($u_age > 110 || $u_age < 18) {
            $u_age = 18; // Default age. 18 = 18 years
        }

        $ip_addr = helper::ip_addr();

        $settings = new settings($this->db);
        $app_settings = $settings->get();
        unset($settings);

        if ($app_settings['allowMultiAccountsFunction']['intValue'] == 0) {
            if ($this->checkMultiAccountsByIp($ip_addr)) {
                $result = array(
                    "error" => true,
                    "error_code" => 500,
                    "error_type" => 4,
                    "error_description" => "User with this ip is already registered"
                );
                return $result;
            }
        }

        $passw_hash = hash('sha256', $password);
        $currentTime = time();

        $accountState = ACCOUNT_STATE_ENABLED;
        $default_user_balance = $app_settings['defaultBalance']['intValue'];
        $default_level_messages_count = $app_settings['defaultLevelMessagesCount']['intValue'];
        $default_user_language = "en";

        $stmt = $this->db->prepare("INSERT INTO users (access_level, level_messages_count, language, state, username, fullname, password, email, balance, bio, interests, gender, u_age, country, regtime, ip_addr) value (:access_level, :level_messages_count, :language, :state, :username, :fullname, :password, :email, :balance, :bio, :interests, :gender, :age, :country, :createAt, :ip_addr)");
        $stmt->bindParam(":access_level", $access_level);
        $stmt->bindParam(":level_messages_count", $default_level_messages_count);
        $stmt->bindParam(":language", $default_user_language);
        $stmt->bindParam(":state", $accountState);
        $stmt->bindParam(":username", $username);
        $stmt->bindParam(":fullname", $fullname);
        $stmt->bindParam(":password", $passw_hash);
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":balance", $default_user_balance);
        $stmt->bindParam(":gender", $gender);
        $stmt->bindParam(":bio", $bio);
        $stmt->bindParam(":interests", $interests);
        $stmt->bindParam(":country", $country);
        $stmt->bindParam(":age", $u_age);
        $stmt->bindParam(":createAt", $currentTime);
        $stmt->bindParam(":ip_addr", $ip_addr);

        if ($stmt->execute()) {

            $this->setId($this->db->lastInsertId());

            $result = array(
                "error" => false,
                'error_code' => ERROR_SUCCESS,
                'error_description' => 'SignUp Success!',
                'accountId' => $this->getId(),
                'username' => $username,
                'fullname' => $fullname,
                'password' => $password,
                'balance' => $default_user_balance,
                'level_messages_count' => $default_level_messages_count
            );

            return $result;
        }

        return $result;
    }

    public function signin($email, $password)
    {
        $access_data = array('error' => true);

        $email = helper::clearText($email);
        $password = helper::clearText($password);

        $passw_hash = hash('sha256', $password);

        $stmt2 = $this->db->prepare("SELECT id, state, fullname, bigPhotoUrl, level, level_messages_count FROM users WHERE email = (:email) AND password = (:password) LIMIT 1");
        $stmt2->bindParam(":email", $email);
        $stmt2->bindParam(":password", $passw_hash);
        $stmt2->execute();

        if ($stmt2->rowCount() > 0) {
            $row2 = $stmt2->fetch();
            $bigPhotoUrl = "";
            if ($row2['bigPhotoUrl'] != '') {
                $bigPhotoUrl = APP_URL . "/" . PROFILE_PHOTO_PATH . $row2['bigPhotoUrl'];
            }
            $access_data = array(
                "error" => false,
                "error_code" => ERROR_SUCCESS,
                "accountId" => $row2['id'],
                "fullname" => $row2['fullname'],
                "photoUrl" => $bigPhotoUrl,
                "level" => $row2['level'],
                "level_messages_count" => $row2['level_messages_count']
            );
        }

        return $access_data;
    }

    public function logout($accountId, $accessToken)
    {
        $auth = new auth($this->db);
        $auth->remove($accountId, $accessToken);
    }

    public function checkMultiAccountsByIp($ip_addr)
    {

        $createAt = time() - 12 * 3600; // 6 hours
        $stmt = $this->db->prepare("SELECT id FROM users WHERE ip_addr = (:ip_addr) AND regtime > (:regtime) LIMIT 1");
        $stmt->bindParam(":ip_addr", $ip_addr);
        $stmt->bindParam(":regtime", $createAt);

        if ($stmt->execute()) {

            if ($stmt->rowCount() > 0) {

                return true;
            }
        }

        return false;
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

        $stmt2 = $this->db->prepare("SELECT id FROM users WHERE id = (:accountId) AND password = (:password) LIMIT 1");
        $stmt2->bindParam(":accountId", $this->id);
        $stmt2->bindParam(":password", $passw_hash);
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

        $stmt = $this->db->prepare("UPDATE users SET password = (:newHash) WHERE id = (:accountId)");
        $stmt->bindParam(":accountId", $this->id);
        $stmt->bindParam(":newHash", $newHash);
        $stmt->execute();
    }


    public function setHeight($u_height)
    {
        $result = array(
            "error" => true,
            "error_code" => ERROR_CODE_INITIATE
        );

        $stmt = $this->db->prepare("UPDATE users SET u_height = (:u_height) WHERE id = (:accountId)");
        $stmt->bindParam(":accountId", $this->id);
        $stmt->bindParam(":u_height", $u_height);

        if ($stmt->execute()) {

            $result = array(
                'error' => false,
                'error_code' => ERROR_SUCCESS
            );
        }

        return $result;
    }

    public function getHeight()
    {
        $stmt = $this->db->prepare("SELECT u_height FROM users WHERE id = (:accountId) LIMIT 1");
        $stmt->bindParam(":accountId", $this->id);

        if ($stmt->execute()) {

            $row = $stmt->fetch();

            return $row['height'];
        }

        return 0;
    }

    public function setAge($u_age)
    {
        $result = array(
            "error" => true,
            "error_code" => ERROR_CODE_INITIATE
        );

        $stmt = $this->db->prepare("UPDATE users SET u_age = (:u_age) WHERE id = (:accountId)");
        $stmt->bindParam(":accountId", $this->id);
        $stmt->bindParam(":u_age", $u_age);

        if ($stmt->execute()) {

            $result = array(
                'error' => false,
                'error_code' => ERROR_SUCCESS
            );
        }

        return $result;
    }

    public function getAge()
    {
        $stmt = $this->db->prepare("SELECT u_age FROM users WHERE id = (:accountId) LIMIT 1");
        $stmt->bindParam(":accountId", $this->id);

        if ($stmt->execute()) {

            $row = $stmt->fetch();

            return $row['age'];
        }

        return 0;
    }


    public function setGender($gender)
    {
        $result = array(
            "error" => true,
            "error_code" => ERROR_CODE_INITIATE
        );

        $stmt = $this->db->prepare("UPDATE users SET gender = (:gender) WHERE id = (:accountId)");
        $stmt->bindParam(":accountId", $this->id);
        $stmt->bindParam(":gender", $gender);

        if ($stmt->execute()) {

            $result = array(
                'error' => false,
                'error_code' => ERROR_SUCCESS
            );
        }

        return $result;
    }

    public function getGender()
    {
        $stmt = $this->db->prepare("SELECT gender FROM users WHERE id = (:accountId) LIMIT 1");
        $stmt->bindParam(":accountId", $this->id);
        if ($stmt->execute()) {
            $row = $stmt->fetch();
            return $row['gender'];
        }
        return 0;
    }


    public function setLevel($level)
    {
        $result = array(
            "error" => true,
            "error_code" => ERROR_CODE_INITIATE
        );

        $level_create_at = 0;
        if ($level != 0) {
            $level_create_at = time();
        }

        $stmt = $this->db->prepare("UPDATE users SET level= (:level), level_create_at = (:level_create_at) WHERE id = (:accountId)");
        $stmt->bindParam(":accountId", $this->id);
        $stmt->bindParam(":level", $level);
        $stmt->bindParam(":level_create_at", $level_create_at);

        if ($stmt->execute()) {
            $result = array(
                'error' => false,
                'error_code' => ERROR_SUCCESS
            );
        }
        return $result;
    }



    public function setBalance($balance)
    {
        $result = array(
            "error" => true,
            "error_code" => ERROR_CODE_INITIATE
        );

        $stmt = $this->db->prepare("UPDATE users SET balance = (:balance) WHERE id = (:accountId)");
        $stmt->bindParam(":accountId", $this->id);
        $stmt->bindParam(":balance", $balance);

        if ($stmt->execute()) {

            $result = array(
                'error' => false,
                'error_code' => ERROR_SUCCESS
            );
        }

        return $result;
    }

    public function getBalance()
    {
        $stmt = $this->db->prepare("SELECT balance FROM users WHERE id = (:accountId) LIMIT 1");
        $stmt->bindParam(":accountId", $this->id);

        if ($stmt->execute()) {

            $row = $stmt->fetch();

            return $row['balance'];
        }

        return 0;
    }

    public function setLevelMessagesCount($count)
    {
        if ($count < 0) {
            $count = 0;
        }
        $result = array("error" => true, "error_code" => ERROR_CODE_INITIATE);
        $stmt = $this->db->prepare("UPDATE users SET level_messages_count = (:level_messages_count) WHERE id = (:accountId)");
        $stmt->bindParam(":accountId", $this->id);
        $stmt->bindParam(":level_messages_count", $count);

        if ($stmt->execute()) {

            $result = array(
                'error' => false,
                'error_code' => ERROR_SUCCESS
            );
        }

        return $result;
    }


    public function getLevel()
    {
        $level = 0;
        $stmt = $this->db->prepare("SELECT level, level_create_at FROM users WHERE id = (:accountId) LIMIT 1");
        $stmt->bindParam(":accountId", $this->id);
        if ($stmt->execute()) {
            $row = $stmt->fetch();
            if ($row['level'] > 0 && time() < $row['level_create_at'] + (30 * 24 * 60 * 60)) {
                $level = $row['level'];
            }
        }
        return $level;
    }

    public function getLevelMessagesCount($level = 0)
    {
        $level_messages_count = 0;
        if ($level == 0) {
            return $level_messages_count;
        }
        $stmt = $this->db->prepare("SELECT level_messages_count FROM users WHERE id = (:accountId) LIMIT 1");
        $stmt->bindParam(":accountId", $this->id);
        if ($stmt->execute()) {
            $row = $stmt->fetch();
            $level_messages_count = $row['level_messages_count'];
        }
        return $level_messages_count;
    }

    public function getFreeMessagesCount($level = 0)
    {
        $free_messages_per_day = 5;
        if ($level > 0) {
            $stmt = $this->db->prepare("SELECT chats FROM level WHERE id = :level LIMIT 1");
            $stmt->bindParam(":level", $level);
            if ($stmt->execute()) {
                $row = $stmt->fetch();
                $free_messages_per_day = $row['chats'];
            }
        }
        $pastTime = time() - (24 * 60 * 60);
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM chats WHERE fromUserId = :accountId AND createAt > :pastTime");
        $stmt->bindParam(":accountId", $this->id);
        $stmt->bindParam(":pastTime", $pastTime);

        if ($stmt->execute()) {
            $number_of_rows = $stmt->fetchColumn();
            $free_messages_count = $free_messages_per_day - $number_of_rows;
            if ($free_messages_count < 0) {
                $free_messages_count = 0;
            }
            return $free_messages_count;
        }
        return 0;
    }


    public function setImagesCount($imagesCount)
    {
        $result = array("error" => true, "error_code" => ERROR_CODE_INITIATE);

        $stmt = $this->db->prepare("UPDATE users SET images_count = (:images_count) WHERE id = (:accountId)");
        $stmt->bindParam(":accountId", $this->id);
        $stmt->bindParam(":images_count", $imagesCount);
        if ($stmt->execute()) {
            $result = array('error' => false, 'error_code' => ERROR_SUCCESS);
        }

        return $result;
    }

    public function getImagesCount()
    {
        $stmt = $this->db->prepare("SELECT count(*) FROM images WHERE fromUserId = (:fromUserId) AND removeAt = 0");
        $stmt->bindParam(":fromUserId", $this->id);
        $stmt->execute();

        return $number_of_rows = $stmt->fetchColumn();
    }

    public function updateCounters()
    {
        $imagesCount = $this->getImagesCount();
        $likesCount = $this->getLikesCount();

        $result = array("error" => true, "error_code" => ERROR_CODE_INITIATE);

        $stmt = $this->db->prepare("UPDATE users SET images_count = (:images_count), likes_count = (:likes_count) WHERE id = (:accountId)");
        $stmt->bindParam(":accountId", $this->id);
        $stmt->bindParam(":images_count", $imagesCount);
        $stmt->bindParam(":likes_count", $likesCount);

        if ($stmt->execute()) {
            $result = array('error' => false, 'error_code' => ERROR_SUCCESS);
        }
        return $result;
    }

    public function setGoogleFirebaseId($gl_id)
    {
        $result = array(
            "error" => true,
            "error_code" => ERROR_CODE_INITIATE
        );

        $stmt = $this->db->prepare("UPDATE users SET gl_id = (:gl_id) WHERE id = (:accountId)");
        $stmt->bindParam(":accountId", $this->id);
        $stmt->bindParam(":gl_id", $gl_id);

        if ($stmt->execute()) {

            $result = array(
                'error' => false,
                'error_code' => ERROR_SUCCESS
            );
        }

        return $result;
    }

    public function getGoogleFirebaseId()
    {
        $stmt = $this->db->prepare("SELECT gl_id FROM users WHERE id = (:accountId) LIMIT 1");
        $stmt->bindParam(":accountId", $this->id);
        if ($stmt->execute()) {
            $row = $stmt->fetch();
            return $row['gl_id'];
        }
        return 0;
    }



    public function setInterests($interests)
    {
        $result = array("error" => true, "error_code" => ERROR_CODE_INITIATE);
        $stmt = $this->db->prepare("UPDATE users SET interests = (:interests) WHERE id = (:accountId)");
        $stmt->bindParam(":accountId", $this->id);
        $stmt->bindParam(":interests", $interests);

        if ($stmt->execute()) {
            $result = array('error' => false, 'error_code' => ERROR_SUCCESS);
        }

        return $result;
    }

    public function getInterests()
    {
        $stmt = $this->db->prepare("SELECT interests FROM users WHERE id = (:accountId) LIMIT 1");
        $stmt->bindParam(":accountId", $this->id);

        if ($stmt->execute()) {
            $row = $stmt->fetch();
            return $row['interests'];
        }
        return '';
    }

    public function setEmail($email)
    {
        $result = array("error" => true, "error_code" => ERROR_CODE_INITIATE);

        $helper = new helper($this->db);

        if (!helper::isCorrectEmail($email)) {
            return $result;
        }

        if ($helper->isEmailExists($email)) {
            return $result;
        }

        $stmt = $this->db->prepare("UPDATE users SET email = (:email) WHERE id = (:accountId)");
        $stmt->bindParam(":accountId", $this->id);
        $stmt->bindParam(":email", $email);

        if ($stmt->execute()) {
            $result = array('error' => false, 'error_code' => ERROR_SUCCESS);
        }
        return $result;
    }

    public function getEmail()
    {
        $stmt = $this->db->prepare("SELECT email FROM users WHERE id = (:accountId) LIMIT 1");
        $stmt->bindParam(":accountId", $this->id);

        if ($stmt->execute()) {
            $row = $stmt->fetch();
            return $row['email'];
        }
        return '';
    }

    public function setUsername($username)
    {
        $result = array(
            "error" => true,
            "error_code" => ERROR_CODE_INITIATE
        );

        $helper = new helper($this->db);

        if (!helper::isCorrectLogin($username)) {
            return $result;
        }

        if ($helper->isUserExists($username)) {
            return $result;
        }

        $stmt = $this->db->prepare("UPDATE users SET username = (:username) WHERE id = (:accountId)");
        $stmt->bindParam(":accountId", $this->id);
        $stmt->bindParam(":username", $username);

        if ($stmt->execute()) {
            $result = array('error' => false, 'error_code' => ERROR_SUCCESS);
        }
        return $result;
    }

    public function getUsername()
    {
        $stmt = $this->db->prepare("SELECT username FROM users WHERE id = (:accountId) LIMIT 1");
        $stmt->bindParam(":accountId", $this->id);

        if ($stmt->execute()) {
            $row = $stmt->fetch();
            return $row['username'];
        }
        return '';
    }

    public function setLocation($location)
    {
        $result = array("error" => true, "error_code" => ERROR_CODE_INITIATE);

        $stmt = $this->db->prepare("UPDATE users SET location = (:location) WHERE id = (:accountId)");
        $stmt->bindParam(":accountId", $this->id);
        $stmt->bindParam(":location", $location);

        if ($stmt->execute()) {
            $result = array('error' => false, 'error_code' => ERROR_SUCCESS);
        }
        return $result;
    }

    public function getLocation()
    {
        $stmt = $this->db->prepare("SELECT location FROM users WHERE id = (:accountId) LIMIT 1");
        $stmt->bindParam(":accountId", $this->id);

        if ($stmt->execute()) {
            $row = $stmt->fetch();
            return $row['location'];
        }

        return '';
    }


    public function setCountry($country)
    {
        $result = array("error" => true, "error_code" => ERROR_CODE_INITIATE);
        $stmt = $this->db->prepare("UPDATE users SET country = (:country) WHERE id = (:accountId)");
        $stmt->bindParam(":accountId", $this->id);
        $stmt->bindParam(":country", $country);

        if ($stmt->execute()) {
            $result = array('error' => false, 'error_code' => ERROR_SUCCESS);
        }
        return $result;
    }


    public function setGeoLocation($lat, $lng)
    {
        $result = array("error" => true, "error_code" => ERROR_CODE_INITIATE);

        $stmt = $this->db->prepare("UPDATE users SET lat = (:lat), lng = (:lng) WHERE id = (:accountId)");
        $stmt->bindParam(":accountId", $this->id);
        $stmt->bindParam(":lat", $lat);
        $stmt->bindParam(":lng", $lng);

        if ($stmt->execute()) {
            $result = array('error' => false, 'error_code' => ERROR_SUCCESS);
        }

        return $result;
    }


    public function getGeoLocation()
    {
        $result = array("error" => true, "error_code" => ERROR_CODE_INITIATE);

        $stmt = $this->db->prepare("SELECT lat, lng FROM users WHERE id = (:accountId) LIMIT 1");
        $stmt->bindParam(":accountId", $this->id);

        if ($stmt->execute()) {

            $row = $stmt->fetch();

            $result = array(
                'error' => false,
                'error_code' => ERROR_SUCCESS,
                'lat' => $row['lat'],
                'lng' => $row['lng']
            );
        }

        return $result;
    }

    public function getLikesCount()
    {
        $stmt = $this->db->prepare("SELECT count(*) FROM profile_likes WHERE toUserId = (:toUserId)");
        $stmt->bindParam(":toUserId", $this->id);
        $stmt->execute();

        return $number_of_rows = $stmt->fetchColumn();
    }

    public function setLikesCount($likesCount)
    {
        $result = array("error" => true, "error_code" => ERROR_CODE_INITIATE);

        $stmt = $this->db->prepare("UPDATE users SET likes_count = (:likes_count) WHERE id = (:accountId)");
        $stmt->bindParam(":accountId", $this->id);
        $stmt->bindParam(":likes_count", $likesCount);

        if ($stmt->execute()) {

            $result = array('error' => false, 'error_code' => ERROR_SUCCESS);
        }

        return $result;
    }


    public function setBio($bio)
    {
        $result = array("error" => true, "error_code" => ERROR_CODE_INITIATE);

        $stmt = $this->db->prepare("UPDATE users SET bio = (:bio) WHERE id = (:accountId)");
        $stmt->bindParam(":accountId", $this->id);
        $stmt->bindParam(":bio", $bio);

        if ($stmt->execute()) {
            $result = array('error' => false, 'error_code' => ERROR_SUCCESS);
        }

        return $result;
    }

    public function getBio()
    {
        $stmt = $this->db->prepare("SELECT bio FROM users WHERE id = (:accountId) LIMIT 1");
        $stmt->bindParam(":accountId", $this->id);

        if ($stmt->execute()) {
            $row = $stmt->fetch();
            return $row['bio'];
        }
        return '';
    }

    public function restorePointCreate($email)
    {
        $result = array("error" => true, "error_code" => ERROR_CODE_INITIATE);

        $restorePointInfo = $this->restorePointInfo();

        if ($restorePointInfo['error'] === false) {
            return $restorePointInfo;
        }

        $currentTime = time();    // Current time

        $ip_addr = helper::ip_addr();

        $hash = md5(uniqid(rand(), true));

        $stmt = $this->db->prepare("INSERT INTO restore_data (accountId, hash, email, createAt, ip_addr) value (:accountId, :hash, :email, :createAt, :ip_addr)");
        $stmt->bindParam(":accountId", $this->id);
        $stmt->bindParam(":hash", $hash);
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":createAt", $currentTime);
        $stmt->bindParam(":ip_addr", $ip_addr);

        if ($stmt->execute()) {
            $result = array(
                'error' => false,
                'error_code' => ERROR_SUCCESS,
                'accountId' => $this->id,
                'hash' => $hash,
                'email' => $email
            );
        }
        return $result;
    }

    public function restorePointInfo()
    {
        $result = array("error" => true, "error_code" => ERROR_CODE_INITIATE);

        $stmt = $this->db->prepare("SELECT * FROM restore_data WHERE accountId = (:accountId) AND removeAt = 0 LIMIT 1");
        $stmt->bindParam(":accountId", $this->id);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch();
            $result = array(
                'error' => false,
                'error_code' => ERROR_SUCCESS,
                'accountId' => $row['accountId'],
                'hash' => $row['hash'],
                'email' => $row['email']
            );
        }

        return $result;
    }

    public function restorePointRemove()
    {
        $result = array(
            "error" => true,
            "error_code" => ERROR_CODE_INITIATE
        );

        $removeAt = time();

        $stmt = $this->db->prepare("UPDATE restore_data SET removeAt = (:removeAt) WHERE accountId = (:accountId)");
        $stmt->bindParam(":accountId", $this->id);
        $stmt->bindParam(":removeAt", $removeAt);

        if ($stmt->execute()) {

            $result = array(
                "error" => false,
                "error_code" => ERROR_SUCCESS
            );
        }

        return $result;
    }

    public function deactivation($password)
    {

        $result = array(
            'error' => true,
            'error_code' => ERROR_CODE_INITIATE
        );

        if (!helper::isCorrectPassword($password)) {
            return $result;
        }

        $passw_hash = hash('sha256', $password);

        $stmt2 = $this->db->prepare("SELECT id FROM users WHERE id = (:accountId) AND password = (:password) LIMIT 1");
        $stmt2->bindParam(":accountId", $this->id);
        $stmt2->bindParam(":password", $passw_hash);
        $stmt2->execute();

        if ($stmt2->rowCount() > 0) {
            $this->setState(ACCOUNT_STATE_DISABLED);
            $result = array("error" => false, "error_code" => ERROR_SUCCESS);
        }
        return $result;
    }

    public function setLanguage($language)
    {
        $result = array(
            "error" => true,
            "error_code" => ERROR_CODE_INITIATE
        );

        $stmt = $this->db->prepare("UPDATE users SET language = (:language) WHERE id = (:accountId)");
        $stmt->bindParam(":accountId", $this->id);
        $stmt->bindParam(":language", $language);

        if ($stmt->execute()) {

            $result = array(
                "error" => false,
                "error_code" => ERROR_SUCCESS
            );
        }

        return $result;
    }

    public function getLanguage()
    {
        $stmt = $this->db->prepare("SELECT language FROM users WHERE id = (:accountId) LIMIT 1");
        $stmt->bindParam(":accountId", $this->id);

        if ($stmt->execute()) {

            $row = $stmt->fetch();

            return $row['language'];
        }

        return 'en';
    }



    public function setFullname($fullname)
    {
        if (strlen($fullname) == 0) {

            return;
        }

        $stmt = $this->db->prepare("UPDATE users SET fullname = (:fullname) WHERE id = (:accountId)");
        $stmt->bindParam(":accountId", $this->id);
        $stmt->bindParam(":fullname", $fullname);

        $stmt->execute();
    }


    public function setState($accountState)
    {
        $stmt = $this->db->prepare("UPDATE users SET state = (:accountState) WHERE id = (:accountId)");
        $stmt->bindParam(":accountId", $this->id);
        $stmt->bindParam(":accountState", $accountState);
        $stmt->execute();
    }

    public function getState()
    {
        $stmt = $this->db->prepare("SELECT state FROM users WHERE id = (:id) LIMIT 1");
        $stmt->bindParam(":id", $this->id);

        if ($stmt->execute()) {
            $row = $stmt->fetch();
            return $row['state'];
        }
        return 0;
    }


    public function setLastActive()
    {
        $time = time();
        $stmt = $this->db->prepare("UPDATE users SET last_authorize = (:last_authorize) WHERE id = (:id)");
        $stmt->bindParam(":id", $this->id);
        $stmt->bindParam(":last_authorize", $time);
        $stmt->execute();
    }

    public function setLastNotify()
    {
        $time = time();

        $stmt = $this->db->prepare("UPDATE users SET last_notify = (:last_notify) WHERE id = (:id)");
        $stmt->bindParam(":id", $this->id);
        $stmt->bindParam(":last_notify", $time);
        $stmt->execute();
    }

    public function getLastNotify()
    {
        $stmt = $this->db->prepare("SELECT last_notify FROM users WHERE id = (:id) LIMIT 1");
        $stmt->bindParam(":id", $this->id);

        if ($stmt->execute()) {

            if ($stmt->rowCount() > 0) {

                $row = $stmt->fetch();

                return $row['last_notify'];
            }
        }

        $time = time();

        return $time;
    }


    public function get()
    {
        $result = array("error" => true, "error_code" => ERROR_ACCOUNT_ID);

        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = (:id) LIMIT 1");
        $stmt->bindParam(":id", $this->id);

        if ($stmt->execute()) {
            if ($stmt->rowCount() > 0) {
                $row = $stmt->fetch();
                $notifications_count = 0;
                if ($row['level'] > 0 && time() < $row['level_create_at'] + (30 * 24 * 60 * 60)) {
                    $level = $row['level'];
                } else {
                    $level = 0;
                }
                $level_messages_count = $this->getLevelMessagesCount($level);
                $free_messages_count = $this->getFreeMessagesCount($level);

                $online = false;
                $current_time = time();

                if ($row['last_authorize'] != 0 && $row['last_authorize'] > ($current_time - 15 * 60)) {
                    $online = true;
                    $online = false;
                }


                $bigPhotoUrl = "";
                if ($row['bigPhotoUrl'] != '') {
                    $bigPhotoUrl = APP_URL . "/" . PROFILE_PHOTO_PATH . $row['bigPhotoUrl'];
                }

                $time = new language($this->db);
                $result = array(
                    "error" => false,
                    "error_code" => ERROR_SUCCESS,
                    "id" => $row['id'],
                    "level" => $level,
                    "level_create_at" => $row['level_create_at'],
                    "balance" => $row['balance'],
                    "free_messages_count" => $free_messages_count,
                    "level_messages_count" => $level_messages_count,
                    "gl_id" => $row['gl_id'],
                    "state" => $row['state'],
                    "regtime" => $row['regtime'],
                    "ip_addr" => $row['ip_addr'],
                    "username" => $row['username'],
                    "fullname" => stripcslashes($row['fullname']),
                    "location" => stripcslashes($row['location']),
                    "bio" => stripcslashes($row['bio']),
                    "interests" => stripcslashes($row['interests']),
                    "email" => $row['email'],
                    "emailVerify" => $row['emailVerify'],
                    "gender" => $row['gender'],
                    "age" => $row['u_age'],
                    "height" => $row['u_height'],
                    "lat" => $row['lat'],
                    "lng" => $row['lng'],
                    "language" => $row['language'],
                    "bigPhotoUrl" => $bigPhotoUrl,
                    "iReligiousView" => $row['iReligiousView'],
                    "iSmokingViews" => $row['iSmokingViews'],
                    "iAlcoholViews" => $row['iAlcoholViews'],
                    "iLooking" => $row['iLooking'],
                    "iInterested" => $row['iInterested'],
                    "allowShowOnline" => $row['allowShowOnline'],
                    "lastAuthorize" => $row['last_authorize'],
                    "lastAuthorizeDate" => date("Y-m-d H:i:s", $row['last_authorize']),
                    "lastAuthorizeTimeAgo" => $time->timeAgo($row['last_authorize']),
                    "online" => $online,
                    "imagesCount" => $row['images_count'],
                    "likesCount" => $row['likes_count'],
                    "notificationsCount" => $notifications_count,
                    "messagesCount" => $level_messages_count,
                    "LastNotify" => $row['last_notify']
                );

                unset($time);
            }
        }

        return $result;
    }

    public function edit($fullname)
    {
        $result = array("error" => true);

        $stmt = $this->db->prepare("UPDATE users SET fullname = (:fullname) WHERE id = (:accountId)");
        $stmt->bindParam(":accountId", $this->id);
        $stmt->bindParam(":fullname", $fullname);

        if ($stmt->execute()) {

            $result = array("error" => false);
        }

        return $result;
    }

    public function setPhoto($array_data)
    {
        $stmt = $this->db->prepare("UPDATE users SET bigPhotoUrl = (:bigPhotoUrl) WHERE id = (:account_id)");
        $stmt->bindParam(":account_id", $this->id);
        $stmt->bindParam(":bigPhotoUrl", $array_data['bigPhotoUrl']);

        $stmt->execute();
    }



    public function getAccessLevel($user_id)
    {
        $stmt = $this->db->prepare("SELECT access_level FROM users WHERE id = (:id) LIMIT 1");
        $stmt->bindParam(":id", $user_id);

        if ($stmt->execute()) {

            if ($stmt->rowCount() > 0) {

                $row = $stmt->fetch();

                return $row['access_level'];
            }
        }

        return 0;
    }

    public function setAccessLevel($access_level)
    {
        $stmt = $this->db->prepare("UPDATE users SET access_level = (:access_level) WHERE id = (:accountId)");
        $stmt->bindParam(":accountId", $this->id);
        $stmt->bindParam(":access_level", $access_level);

        $stmt->execute();
    }


    public function set_iReligiousView($religiousView)
    {
        $result = array(
            "error" => true,
            "error_code" => ERROR_CODE_INITIATE
        );

        $stmt = $this->db->prepare("UPDATE users SET iReligiousView = (:iReligiousView) WHERE id = (:accountId)");
        $stmt->bindParam(":accountId", $this->id);
        $stmt->bindParam(":iReligiousView", $religiousView);

        if ($stmt->execute()) {

            $result = array(
                'error' => false,
                'error_code' => ERROR_SUCCESS
            );
        }

        return $result;
    }

    public function get_iReligiousView()
    {
        $stmt = $this->db->prepare("SELECT iReligiousView FROM users WHERE id = (:accountId) LIMIT 1");
        $stmt->bindParam(":accountId", $this->id);

        if ($stmt->execute()) {

            $row = $stmt->fetch();

            return $row['iReligiousView'];
        }

        return 0;
    }





    public function set_iSmokingViews($smokingViews)
    {
        $result = array(
            "error" => true,
            "error_code" => ERROR_CODE_INITIATE
        );

        $stmt = $this->db->prepare("UPDATE users SET iSmokingViews = (:iSmokingViews) WHERE id = (:accountId)");
        $stmt->bindParam(":accountId", $this->id);
        $stmt->bindParam(":iSmokingViews", $smokingViews);

        if ($stmt->execute()) {

            $result = array(
                'error' => false,
                'error_code' => ERROR_SUCCESS
            );
        }

        return $result;
    }

    public function get_iSmokingViews()
    {
        $stmt = $this->db->prepare("SELECT iSmokingViews FROM users WHERE id = (:accountId) LIMIT 1");
        $stmt->bindParam(":accountId", $this->id);

        if ($stmt->execute()) {

            $row = $stmt->fetch();

            return $row['iSmokingViews'];
        }

        return 0;
    }

    public function set_iAlcoholViews($alcoholViews)
    {
        $result = array(
            "error" => true,
            "error_code" => ERROR_CODE_INITIATE
        );

        $stmt = $this->db->prepare("UPDATE users SET iAlcoholViews = (:iAlcoholViews) WHERE id = (:accountId)");
        $stmt->bindParam(":accountId", $this->id);
        $stmt->bindParam(":iAlcoholViews", $alcoholViews);

        if ($stmt->execute()) {

            $result = array(
                'error' => false,
                'error_code' => ERROR_SUCCESS
            );
        }

        return $result;
    }

    public function get_iAlcoholViews()
    {
        $stmt = $this->db->prepare("SELECT iAlcoholViews FROM users WHERE id = (:accountId) LIMIT 1");
        $stmt->bindParam(":accountId", $this->id);

        if ($stmt->execute()) {

            $row = $stmt->fetch();

            return $row['iAlcoholViews'];
        }

        return 0;
    }

    public function set_iLooking($looking)
    {
        $result = array(
            "error" => true,
            "error_code" => ERROR_CODE_INITIATE
        );

        $stmt = $this->db->prepare("UPDATE users SET iLooking = (:iLooking) WHERE id = (:accountId)");
        $stmt->bindParam(":accountId", $this->id);
        $stmt->bindParam(":iLooking", $looking);

        if ($stmt->execute()) {

            $result = array(
                'error' => false,
                'error_code' => ERROR_SUCCESS
            );
        }

        return $result;
    }

    public function get_iLooking()
    {
        $stmt = $this->db->prepare("SELECT iLooking FROM users WHERE id = (:accountId) LIMIT 1");
        $stmt->bindParam(":accountId", $this->id);

        if ($stmt->execute()) {

            $row = $stmt->fetch();

            return $row['iLooking'];
        }

        return 0;
    }

    public function set_iInterested($interested)
    {
        $result = array(
            "error" => true,
            "error_code" => ERROR_CODE_INITIATE
        );

        $stmt = $this->db->prepare("UPDATE users SET iInterested = (:iInterested) WHERE id = (:accountId)");
        $stmt->bindParam(":accountId", $this->id);
        $stmt->bindParam(":iInterested", $interested);

        if ($stmt->execute()) {

            $result = array(
                'error' => false,
                'error_code' => ERROR_SUCCESS
            );
        }

        return $result;
    }

    public function get_iInterested()
    {
        $stmt = $this->db->prepare("SELECT iInterested FROM users WHERE id = (:accountId) LIMIT 1");
        $stmt->bindParam(":accountId", $this->id);

        if ($stmt->execute()) {

            $row = $stmt->fetch();

            return $row['iInterested'];
        }

        return 0;
    }

    public function setId($accountId)
    {
        $this->id = $accountId;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setAnonymousQuestions($anonymousQuestions)
    {
        $result = array("error" => true);

        $stmt = $this->db->prepare("UPDATE users SET anonymousQuestions = (:anonymousQuestions) WHERE id = (:accountId) LIMIT 1");
        $stmt->bindParam(":accountId", $this->id);
        $stmt->bindParam(":anonymousQuestions", $anonymousQuestions);
        $stmt->execute();

        if ($stmt->execute()) {

            $result = array("error" => false);
        }

        return $result;
    }
}
