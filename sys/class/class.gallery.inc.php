<?php





class gallery extends db_connect
{
	private $requestFrom = 0;
    private $language = 'en';
    private $profileId = 0;

	public function __construct($dbo = NULL)
    {
		parent::__construct($dbo);
	}

    public function getAllCount()
    {
        $stmt = $this->db->prepare("SELECT count(*) FROM images");
        $stmt->execute();

        return $number_of_rows = $stmt->fetchColumn();
    }

    private function getMaxId()
    {
        $stmt = $this->db->prepare("SELECT MAX(id) FROM images");
        $stmt->execute();

        return $number_of_rows = $stmt->fetchColumn();
    }


    public function count()
    {
        $sql = "SELECT count(*) FROM images WHERE removeAt = 0";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return $number_of_rows = $stmt->fetchColumn();
    }

    public function add($mode, $imgUrl = "", $itemType = 0)
    {
        $result = array(
            "error" => true,
            "error_code" => ERROR_CODE_INITIATE
        );

        if (strlen($imgUrl) == 0) {

            return $result;
        }

        $currentTime = time();
        $ip_addr = helper::ip_addr();

        $stmt = $this->db->prepare("INSERT INTO images (fromUserId, accessMode, itemType, imgUrl, createAt, ip_addr) value (:fromUserId, :accessMode, :itemType, :imgUrl, :createAt, :ip_addr)");
        $stmt->bindParam(":fromUserId", $this->requestFrom, PDO::PARAM_INT);
        $stmt->bindParam(":accessMode", $mode, PDO::PARAM_INT);
        $stmt->bindParam(":itemType", $itemType, PDO::PARAM_INT);
        $stmt->bindParam(":imgUrl", $imgUrl, PDO::PARAM_STR);
        $stmt->bindParam(":createAt", $currentTime, PDO::PARAM_INT);
        $stmt->bindParam(":ip_addr", $ip_addr, PDO::PARAM_STR);

        if ($stmt->execute()) {

            $result = array(
                "error" => false,
                "error_code" => ERROR_SUCCESS,
                "imageId" => $this->db->lastInsertId(),
                "itemId" => $this->db->lastInsertId(),
                "image" => $this->info($this->db->lastInsertId())
            );
            $fcm = new fcm($this->db);
            $fcm->setRequestFrom(0);
            $fcm->setRequestTo(0);
            $fcm->setAppType(APP_TYPE_MANAGER);
            $fcm->setType(GCM_NOTIFY_PROFILE_NEW_MEDIA_ITEM_UPLOADED);
            $fcm->setTitle("New media item created.");
            $fcm->prepare();
            $fcm->send();
            unset($fcm);
        }

        return $result;
    }

    public function removeAll() {

        $result = array(
            "error" => true,
            "error_code" => ERROR_CODE_INITIATE
        );

        $currentTime = time();

        $stmt = $this->db->prepare("UPDATE images SET removeAt = (:removeAt) WHERE fromUserId = (:fromUserId) AND removeAt = 0");
        $stmt->bindParam(":fromUserId", $this->requestFrom, PDO::PARAM_INT);
        $stmt->bindParam(":removeAt", $currentTime, PDO::PARAM_INT);

        if ($stmt->execute()) {

            $result = array(
                "error" => false,
                "error_code" => ERROR_SUCCESS
            );
        }

        return $result;
    }

    public function remove($imageId)
    {
        $result = array("error" => true);

        $imageInfo = $this->info($imageId);

        if ($imageInfo['error']) {

            return $result;
        }

        if ($imageInfo['owner']['id'] != $this->getRequestFrom()) {

            return $result;
        }

        $currentTime = time();

        $stmt = $this->db->prepare("UPDATE images SET removeAt = (:removeAt) WHERE id = (:imageId)");
        $stmt->bindParam(":imageId", $imageId, PDO::PARAM_INT);
        $stmt->bindParam(":removeAt", $currentTime, PDO::PARAM_INT);

        if ($stmt->execute()) {
            @unlink(MY_PHOTOS_PATH."/".basename($imageInfo['imgUrl']));

            $stmt2 = $this->db->prepare("DELETE FROM notifications WHERE itemId = (:itemId) AND notifyType > 6");
            $stmt2->bindParam(":itemId", $imageId, PDO::PARAM_INT);
            $stmt2->execute();


            $result = array("error" => false);

            $account = new account($this->db, $imageInfo['owner']['id']);
            $account->updateCounters();
            unset($account);
        }

        return $result;
    }

    public function restore($imageId)
    {
        $result = array("error" => true);

        $imageInfo = $this->info($imageId);

        if ($imageInfo['error'] === true) {

            return $result;
        }

        $stmt = $this->db->prepare("UPDATE images SET removeAt = 0 WHERE id = (:imageId)");
        $stmt->bindParam(":imageId", $imageId, PDO::PARAM_INT);

        if ($stmt->execute()) {

            $result = array("error" => false);
        }

        return $result;
    }


    public function info($itemId)
    {
        $result = array(
            "error" => true,
            "error_code" => ERROR_CODE_INITIATE
        );

        $stmt = $this->db->prepare("SELECT * FROM images WHERE id = (:itemId) LIMIT 1");
        $stmt->bindParam(":itemId", $itemId, PDO::PARAM_INT);

        if ($stmt->execute()) {

            if ($stmt->rowCount() > 0) {

                $row = $stmt->fetch();

                $result = $this->quick($row);
            }
        }

        return $result;
    }

    public function quick($row)
    {
        $time = new language($this->db, $this->language);

        $profile = new profile($this->db, $row['fromUserId']);
        $profileInfo = $profile->getVeryShort();
        unset($profile);

        $result = array(
            "error" => false,
            "error_code" => ERROR_SUCCESS,
            "id" => $row['id'],
            "owner" => $profileInfo,
            "accessMode" => $row['accessMode'],
            "itemType" => $row['itemType'],
            "imgUrl" => $row['imgUrl'],
            "createAt" => $row['createAt'],
            "date" => date("Y-m-d H:i:s", $row['createAt']),
            "timeAgo" => $time->timeAgo($row['createAt']),
            "removeAt" => $row['removeAt']
        );

        return $result;
    }

    // Get items
    public function get($itemId = 0, $profileId = 0, $access = false, $limit = 20)
    {

        if ($itemId == 0) {
            $itemId = 99999999999;
            $itemId++;
        }

        $result = array(
            "error" => false,
            "error_code" => ERROR_SUCCESS,
            "itemId" => $itemId,
            "items" => array()
        );

        $profileSql = "";

        if ($profileId != 0) {
            $profileSql = " AND fromUserId = {$profileId}";
        }

        $accessSql = "";

        if ($access) {
            $accessSql = " AND accessMode = 0";
        }

        $endSql = " ORDER BY id DESC LIMIT $limit";

        $sql = "SELECT * FROM images WHERE removeAt = 0 AND id < $itemId".$profileSql.$accessSql.$endSql;
        $stmt = $this->db->prepare($sql);

        if ($stmt->execute()) {
            while ($row = $stmt->fetch()) {
                array_push($result['items'], $this->quick($row));
                $result['itemId'] = $row['id'];
            }
        }
        return $result;
    }

    public function setLanguage($language)
    {
        $this->language = $language;
    }

    public function getLanguage()
    {
        return $this->language;
    }

    public function setRequestFrom($requestFrom)
    {
        $this->requestFrom = $requestFrom;
    }

    public function getRequestFrom()
    {
        return $this->requestFrom;
    }

    public function setProfileId($profileId)
    {
        $this->profileId = $profileId;
    }

    public function getProfileId()
    {
        return $this->profileId;
    }
}
