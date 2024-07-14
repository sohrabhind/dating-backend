<?php



class profile extends db_connect
{

    private $id = 0;
    private $requestFrom = 0;

    public function __construct($dbo = NULL, $profileId = 0)
    {

        parent::__construct($dbo);

        $this->setId($profileId);
    }

    private function getMaxIdLikes()
    {
        $stmt = $this->db->prepare("SELECT MAX(id) FROM profile_likes");
        $stmt->execute();

        return $number_of_rows = $stmt->fetchColumn();
    }

    public function getILikedCount()
    {
        $stmt = $this->db->prepare("SELECT count(*) FROM profile_likes WHERE fromUserId = (:fromUserId)");
        $stmt->bindParam(":fromUserId", $this->requestFrom);
        $stmt->execute();

        return $number_of_rows = $stmt->fetchColumn();
    }

    public function get()
    {
        $result = array("error" => true, "error_code" => ERROR_ACCOUNT_ID);

        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = (:id) LIMIT 1");
        $stmt->bindParam(":id", $this->id);


        if ($stmt->execute()) {

            if ($stmt->rowCount() > 0) {

                $row = $stmt->fetch();

                // test to my like
                $iLiked = false;
                if ($this->getRequestFrom() != 0 && $this->getRequestFrom() != $this->getId()) {
                    if ($this->is_like_exists($this->requestFrom)) {
                        $iLiked = true;
                    }
                }

                $myFan = false;
                if ($this->getRequestFrom() != 0 && $this->getRequestFrom() != $this->getId()) {
                    if ($this->is_my_fan($this->requestFrom)) {
                        $myFan = true;
                    }
                }

                // test to blocked
                $blocked = false;

                if ($this->getRequestFrom() != 0 && $this->getRequestFrom() != $this->getId()) {

                    $blacklist = new blacklist($this->db);
                    $blacklist->setRequestFrom($this->requestFrom);

                    if ($blacklist->isExists($this->id)) {

                        $blocked = true;
                    }

                    unset($blacklist);
                }


                // is my profile exists in blacklist
                $inBlackList = false;

                if ($this->getRequestFrom() != 0 && $this->getRequestFrom() != $this->getId()) {

                    $blacklist = new blacklist($this->db);
                    $blacklist->setRequestFrom($this->getId());

                    if ($blacklist->isExists($this->getRequestFrom())) {

                        $inBlackList = true;
                    }

                    unset($blacklist);
                }

                $online = false;

                $current_time = time();

                if ($row['last_authorize'] != 0 && $row['last_authorize'] > ($current_time - 15 * 60)) {

                    $online = true;
                    $online = false;
                }


                if ($row['level'] > 0 && time() < $row['level_create_at'] + (30 * 24 * 60 * 60)) {
                    $level = $row['level'];
                } else {
                    $level = 0;
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
                    "access_level" => $row['access_level'],
                    "level" => $level,
                    "level_create_at" => $row['level_create_at'],
                    "state" => $row['state'],
                    "gender" => $row['gender'],
                    "age" => $row['u_age'],
                    "height" => $row['u_height'],
                    "lat" => $row['lat'],
                    "lng" => $row['lng'],
                    "username" => $row['username'],
                    "fullname" => $row['fullname'],
                    "location" => stripcslashes($row['location']),
                    "bio" => stripcslashes($row['bio']),
                    "interests" => stripcslashes($row['interests']),
                    "bigPhotoUrl" => $bigPhotoUrl,
                    "iReligiousView" => $row['iReligiousView'],
                    "iSmokingViews" => $row['iSmokingViews'],
                    "iAlcoholViews" => $row['iAlcoholViews'],
                    "iLooking" => $row['iLooking'],
                    "iInterested" => $row['iInterested'],
                    "allowShowOnline" => $row['allowShowOnline'],
                    "imagesCount" => $row['images_count'],
                    "likesCount" => $row['likes_count'],
                    "inBlackList" => $inBlackList,
                    "blocked" => $blocked,
                    "iLiked" => $iLiked,
                    "myFan" => $myFan,
                    "createAt" => $row['regtime'],
                    "createDate" => date("Y-m-d", $row['regtime']),
                    "lastAuthorize" => $row['last_authorize'],
                    "lastAuthorizeDate" => date("Y-m-d H:i:s", $row['last_authorize']),
                    "lastAuthorizeTimeAgo" => $time->timeAgo($row['last_authorize']),
                    "online" => $online
                );
            }
        }

        return $result;
    }

    public function getShort()
    {
        $result = array(
            "error" => true,
            "error_code" => ERROR_ACCOUNT_ID
        );

        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = (:id) LIMIT 1");
        $stmt->bindParam(":id", $this->id);

        if ($stmt->execute()) {

            if ($stmt->rowCount() > 0) {

                $row = $stmt->fetch();

                // test to my like

                $iLiked = false;
                if ($this->getRequestFrom() != 0 && $this->getRequestFrom() != $this->getId()) {
                    if ($this->is_like_exists($this->requestFrom)) {
                        $iLiked = true;
                    }
                }

                $myFan = false;
                if ($this->getRequestFrom() != 0 && $this->getRequestFrom() != $this->getId()) {
                    if ($this->is_my_fan($this->requestFrom)) {
                        $myFan = true;
                    }
                }


                // is my profile exists in blacklist
                $inBlackList = false;

                if ($this->requestFrom != 0) {

                    $blacklist = new blacklist($this->db);
                    $blacklist->setRequestFrom($this->getId());

                    if ($blacklist->isExists($this->getRequestFrom())) {

                        $inBlackList = true;
                    }

                    unset($blacklist);
                }

                $online = false;

                $current_time = time();

                if ($row['last_authorize'] != 0 && $row['last_authorize'] > ($current_time - 15 * 60)) {

                    $online = true;
                    $online = false;
                }



                if ($row['level'] > 0 && time() < $row['level_create_at'] + (30 * 24 * 60 * 60)) {
                    $level = $row['level'];
                } else {
                    $level = 0;
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
                    "access_level" => $row['access_level'],
                    "level" => $level,
                    "level_create_at" => $row['level_create_at'],
                    "state" => $row['state'],
                    "gender" => $row['gender'],
                    "age" => $row['u_age'],
                    "height" => $row['u_height'],
                    "lat" => $row['lat'],
                    "lng" => $row['lng'],
                    "username" => $row['username'],
                    "fullname" => $row['fullname'],
                    "location" => stripcslashes($row['location']),
                    "bio" => stripcslashes($row['bio']),
                    "interests" => stripcslashes($row['interests']),
                    "imagesCount" => $row['images_count'],
                    "likesCount" => $row['likes_count'],
                    "bigPhotoUrl" => $bigPhotoUrl,
                    "allowShowOnline" => $row['allowShowOnline'],
                    "inBlackList" => $inBlackList,
                    "iLiked" => $iLiked,
                    "myFan" => $myFan,
                    "createAt" => $row['regtime'],
                    "createDate" => date("Y-m-d", $row['regtime']),
                    "lastAuthorize" => $row['last_authorize'],
                    "lastAuthorizeDate" => date("Y-m-d H:i:s", $row['last_authorize']),
                    "lastAuthorizeTimeAgo" => $time->timeAgo($row['last_authorize']),
                    "online" => $online
                );
            }
        }

        return $result;
    }

    public function getVeryShort()
    {
        $result = array(
            "error" => true,
            "error_code" => ERROR_ACCOUNT_ID
        );

        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = (:id) LIMIT 1");
        $stmt->bindParam(":id", $this->id);

        if ($stmt->execute()) {

            if ($stmt->rowCount() > 0) {

                $row = $stmt->fetch();

                $online = false;
                $inBlackList = false;
                $blocked = false;

                $iLiked = false;
                if ($this->getRequestFrom() != 0 && $this->getRequestFrom() != $this->getId()) {
                    if ($this->is_like_exists($this->requestFrom)) {
                        $iLiked = true;
                    }
                }

                $myFan = false;
                if ($this->getRequestFrom() != 0 && $this->getRequestFrom() != $this->getId()) {
                    if ($this->is_my_fan($this->requestFrom)) {
                        $myFan = true;
                    }
                }

                $current_time = time();

                if ($row['last_authorize'] != 0 && $row['last_authorize'] > ($current_time - 15 * 60)) {
                    $online = true;
                    $online = false;
                }


                if ($row['level'] > 0 && time() < $row['level_create_at'] + (30 * 24 * 60 * 60)) {
                    $level = $row['level'];
                } else {
                    $level = 0;
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
                    "access_level" => $row['access_level'],
                    "level" => $level,
                    "level_create_at" => $row['level_create_at'],
                    "state" => $row['state'],
                    "gender" => $row['gender'],
                    "age" => $row['u_age'],
                    "height" => $row['u_height'],
                    "lat" => $row['lat'],
                    "lng" => $row['lng'],
                    "username" => $row['username'],
                    "fullname" => $row['fullname'],
                    "location" => stripcslashes($row['location']),
                    "bio" => stripcslashes($row['bio']),
                    "interests" => stripcslashes($row['interests']),
                    "bigPhotoUrl" => $bigPhotoUrl,
                    "iReligiousView" => $row['iReligiousView'],
                    "iSmokingViews" => $row['iSmokingViews'],
                    "iAlcoholViews" => $row['iAlcoholViews'],
                    "iLooking" => $row['iLooking'],
                    "iInterested" => $row['iInterested'],
                    "imagesCount" => $row['images_count'],
                    "likesCount" => $row['likes_count'],
                    "createAt" => $row['regtime'],
                    "createDate" => date("Y-m-d", $row['regtime']),
                    "lastAuthorize" => $row['last_authorize'],
                    "lastAuthorizeDate" => date("Y-m-d H:i:s", $row['last_authorize']),
                    "lastAuthorizeTimeAgo" => $time->timeAgo($row['last_authorize']),
                    "allowShowOnline" => $row['allowShowOnline'],
                    "online" => $online,
                    "inBlackList" => $inBlackList,
                    "blocked" => $blocked,
                    "iLiked" => $iLiked,
                    "myFan" => $myFan
                );
            }
        }

        return $result;
    }


    public function getLikeToProfiles()
    {
        $profiles = array();
        $timeEnd = time() + (15 * 24 * 60 * 60);
        $timeA = time() + rand(30, 120);
        $timeB = time() + (rand(18, 24) * 60 * 60);

        $stmt = $this->db->prepare("SELECT DISTINCT u.id AS userid
        FROM users u
        LEFT JOIN profile_likes pl ON u.id = pl.toUserId
        WHERE u.gender = '0' AND u.state = '0'
        AND u.last_authorize < :timeEnd AND u.regtime < :timeA
        AND (pl.toUserId IS NULL OR pl.createAt > :timeB)");
        $stmt->bindParam(':timeEnd', $timeEnd);
        $stmt->bindParam(':timeA', $timeA);
        $stmt->bindParam(':timeB', $timeB);

        if ($stmt->execute()) {
            if ($stmt->rowCount() > 0) {
                while ($row = $stmt->fetch()) {
                    $profiles[] = $row['userid'];
                }
            }
        }
        return $profiles;
    }

    public function getLikeFromProfiles()
    {
        $profiles = array();
        $stmt = $this->db->prepare("SELECT id AS userid FROM users WHERE access_level = '1' AND gender = '1' AND state = '0'");
        if ($stmt->execute()) {
            if ($stmt->rowCount() > 0) {
                while ($row = $stmt->fetch()) {
                    $profiles[] = $row['userid'];
                }
            }
        }
        return $profiles;
    }


    public function like($fromUserId, $sendNotification = true)
    {
        $result = array(
            "error" => true,
            "error_code" => ERROR_CODE_INITIATE
        );


        $account = new account($this->db, $fromUserId);
        $account->setLastActive();
        unset($account);

        $iLiked = false;

        if ($this->is_like_exists($fromUserId)) {
            $stmt = $this->db->prepare("DELETE FROM profile_likes WHERE toUserId = (:toUserId) AND fromUserId = (:fromUserId)");
            $stmt->bindParam(":fromUserId", $fromUserId);
            $stmt->bindParam(":toUserId", $this->id);
            $stmt->execute();

            $notify = new notify($this->db);
            $notify->removeNotify($this->id, $fromUserId, NOTIFY_TYPE_LIKE, 0);
            unset($notify);

            $iLiked = false;
        } else {
            $createAt = time();
            $ip_addr = helper::ip_addr();
            $stmt = $this->db->prepare("INSERT INTO profile_likes (toUserId, fromUserId, createAt, ip_addr) value (:toUserId, :fromUserId, :createAt, :ip_addr)");
            $stmt->bindParam(":toUserId", $this->id);
            $stmt->bindParam(":fromUserId", $fromUserId);
            $stmt->bindParam(":createAt", $createAt);
            $stmt->bindParam(":ip_addr", $ip_addr);
            $stmt->execute();

            $iLiked = true;


            if ($this->id != $fromUserId) {

                $blacklist = new blacklist($this->db);
                $blacklist->setRequestFrom($this->id);

                if (!$blacklist->isExists($fromUserId)) {


                    $u_profile = new profile($this->db, $fromUserId);
                    $profileInfo = $u_profile->getVeryShort();

                    $bigPhotoUrl = "";
                    if ($profileInfo['bigPhotoUrl'] != '') {
                        $bigPhotoUrl = APP_URL . "/" . PROFILE_PHOTO_PATH . basename($profileInfo['bigPhotoUrl']);
                    }

                    $currentTime = time();
                    $time = new language($this->db, "en");

                    $msgInfo = array(
                        "error" => false,
                        "error_code" => ERROR_SUCCESS,
                        "id" => $this->requestFrom,
                        "fromUserId" => $this->requestFrom,
                        "fromUserState" => $profileInfo['state'],
                        "fromUserOnline" => $profileInfo['online'],
                        "fromUserUsername" => $profileInfo['username'],
                        "fromUserFullname" => $profileInfo['fullname'],
                        "fromUserPhotoUrl" => $bigPhotoUrl,
                        "message" => "You have new like",
                        "imageUrl" => $bigPhotoUrl,
                        "createAt" => $currentTime,
                        "seenAt" => 0,
                        "date" => date("Y-m-d H:i:s", $currentTime),
                        "timeAgo" => $time->timeAgo($currentTime),
                        "removeAt" => 0
                    );

                    if ($sendNotification) {
                        $fcm = new fcm($this->db);
                        $fcm->setRequestFrom($this->getRequestFrom());
                        $fcm->setRequestTo($this->id);
                        $fcm->setType(GCM_NOTIFY_LIKE);
                        $fcm->setTitle("You have new like");
                        $fcm->setItemId($this->requestFrom);
                        $fcm->setMessage($msgInfo);
                        $fcm->prepare();
                        $fcm->send();
                        unset($fcm);
                    }
                    
                    $notify = new notify($this->db);
                    $notify->createNotify($this->id, $fromUserId, NOTIFY_TYPE_LIKE, 0);
                    unset($notify);
                }

                unset($blacklist);
            }
        }

        $account = new account($this->db, $this->id);

        $account->updateCounters();

        $likesCount = $account->getLikesCount();
        unset($account);

        $result = array(
            "error" => false,
            "error_code" => ERROR_SUCCESS,
            "likesCount" => $likesCount,
            "iLiked" => $iLiked
        );

        return $result;
    }

    public function getFans($itemId = 0, $limit = 20)
    {
        if ($itemId == 0) {

            $itemId = $this->getMaxIdLikes();
            $itemId++;
        }

        $fans = array(
            "error" => false,
            "error_code" => ERROR_SUCCESS,
            "itemId" => $itemId,
            "items" => array()
        );

        $stmt = $this->db->prepare("SELECT * FROM profile_likes WHERE toUserId = (:toUserId) AND id < (:itemId) ORDER BY id DESC LIMIT :limit");
        $stmt->bindParam(':toUserId', $this->id, PDO::PARAM_INT);
        $stmt->bindParam(':itemId', $itemId, PDO::PARAM_INT);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);

        if ($stmt->execute()) {

            if ($stmt->rowCount() > 0) {

                while ($row = $stmt->fetch()) {

                    $profile = new profile($this->db, $row['fromUserId']);
                    $profile->setRequestFrom($this->requestFrom);
                    $profileInfo = $profile->getVeryShort();
                    unset($profile);

                    array_push($fans['items'], $profileInfo);

                    $fans['itemId'] = $row['id'];

                    unset($profile);
                }
            }
        }

        return $fans;
    }

    public function getILiked($itemId = 0)
    {
        if ($itemId == 0) {

            $itemId = $this->getMaxIdLikes();
            $itemId++;
        }

        $result = array(
            "error" => false,
            "error_code" => ERROR_SUCCESS,
            "itemId" => $itemId,
            "items" => array()
        );

        $stmt = $this->db->prepare("SELECT * FROM profile_likes WHERE fromUserId = (:fromUserId) AND id < (:itemId) ORDER BY id DESC LIMIT 20");
        $stmt->bindParam(':fromUserId', $this->id);
        $stmt->bindParam(':itemId', $itemId);

        if ($stmt->execute()) {

            if ($stmt->rowCount() > 0) {

                while ($row = $stmt->fetch()) {

                    $profile = new profile($this->db, $row['toUserId']);
                    $profile->setRequestFrom($this->requestFrom);
                    $profileInfo = $profile->getVeryShort();
                    unset($profile);

                    array_push($result['items'], $profileInfo);

                    $result['itemId'] = $row['id'];

                    unset($profile);
                }
            }
        }

        return $result;
    }

    private function is_like_exists($fromUserId)
    {
        $stmt = $this->db->prepare("SELECT id FROM profile_likes WHERE fromUserId = (:fromUserId) AND toUserId = (:toUserId) LIMIT 1");
        $stmt->bindParam(":fromUserId", $fromUserId);
        $stmt->bindParam(":toUserId", $this->id);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            return true;
        }
        return false;
    }

    public function is_my_fan($myUserId)
    {
        $stmt = $this->db->prepare("SELECT id FROM profile_likes WHERE fromUserId = (:fromUserId) AND toUserId = (:toUserId) LIMIT 1");
        $stmt->bindParam(":toUserId", $myUserId);
        $stmt->bindParam(":fromUserId", $this->id);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            return true;
        }
        return false;
    }



    public function getState()
    {
        $stmt = $this->db->prepare("SELECT state FROM users WHERE id = (:profileId) LIMIT 1");
        $stmt->bindParam(":profileId", $this->id);
        $stmt->execute();

        $row = $stmt->fetch();

        return $row['state'];
    }

    public function getFullname()
    {
        $stmt = $this->db->prepare("SELECT username, fullname FROM users WHERE id = (:profileId) LIMIT 1");
        $stmt->bindParam(":profileId", $this->id);
        $stmt->execute();

        $row = $stmt->fetch();
        $fullname = $row['fullname'];
        if (strlen($fullname) < 1) {
            $fullname = $row['username'];
        }
        return $fullname;
    }

    public function getUsername()
    {
        $stmt = $this->db->prepare("SELECT username FROM users WHERE id = (:profileId) LIMIT 1");
        $stmt->bindParam(":profileId", $this->id);
        $stmt->execute();

        $row = $stmt->fetch();

        return $row['username'];
    }

    public function setId($profileId)
    {
        $this->id = $profileId;
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
}
