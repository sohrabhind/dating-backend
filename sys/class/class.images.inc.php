<?php

class images extends db_connect
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


    public function count($itemType = -1, $moderated = false)
    {
        $sql = "SELECT count(*) FROM images WHERE fromUserId = {$this->requestFrom} AND removeAt = 0";

        if ($itemType != -1) {

            $sql = $sql." AND itemType = {$itemType}";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return $number_of_rows = $stmt->fetchColumn();
    }

    public function add($imageUrl = "", $itemType = 0)
    {
        $result = array(
            "error" => true,
            "error_code" => ERROR_CODE_INITIATE
        );

        if (strlen($imageUrl) == 0) {

            return $result;
        }

        $currentTime = time();
        $ip_addr = helper::ip_addr();

        $settings = new settings($this->db);
        $app_settings = $settings->get();
        unset($settings);

        $stmt = $this->db->prepare("INSERT INTO images (fromUserId, itemType, imageUrl, createAt, ip_addr) value (:fromUserId, :itemType, :imageUrl, :createAt, :ip_addr)");
        $stmt->bindParam(":fromUserId", $this->requestFrom);
        $stmt->bindParam(":itemType", $itemType);
        $stmt->bindParam(":imageUrl", $imageUrl);
        $stmt->bindParam(":createAt", $currentTime);
        $stmt->bindParam(":ip_addr", $ip_addr);

        if ($stmt->execute()) {

            $result = array("error" => false,
                            "error_code" => ERROR_SUCCESS,
                            "imageId" => $this->db->lastInsertId(),
                            "itemId" => $this->db->lastInsertId(),
                            "image" => $this->info($this->db->lastInsertId()));
        }

        return $result;
    }

    public function removeAll() {
        $stmt = $this->db->prepare("SELECT imageUrl FROM images WHERE fromUserId = (:fromUserId) AND removeAt = 0");
        $stmt->bindParam(":fromUserId", $this->requestFrom);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($rows as $row) {
                @unlink(MY_IMAGES_PATH . "/" . basename($row['imageUrl']));
            }
        }

        $result = array(
            "error" => true,
            "error_code" => ERROR_CODE_INITIATE
        );

        $currentTime = time();

        $stmt = $this->db->prepare("UPDATE images SET removeAt = (:removeAt) WHERE fromUserId = (:fromUserId) AND removeAt = 0");
        $stmt->bindParam(":fromUserId", $this->requestFrom);
        $stmt->bindParam(":removeAt", $currentTime);

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

        if ($imageInfo['fromUserId'] != $this->getRequestFrom()) {

            return $result;
        }

        $currentTime = time();

        $stmt = $this->db->prepare("UPDATE images SET removeAt = (:removeAt) WHERE id = (:imageId)");
        $stmt->bindParam(":imageId", $imageId);
        $stmt->bindParam(":removeAt", $currentTime);

        if ($stmt->execute()) {
            @unlink(MY_IMAGES_PATH."/".basename($imageInfo['imageUrl']));

            $stmt2 = $this->db->prepare("DELETE FROM notifications WHERE itemId = (:itemId) AND notifyType > 6");
            $stmt2->bindParam(":itemId", $imageId);
            $stmt2->execute();

            $result = array("error" => false);

            $account = new account($this->db, $imageInfo['fromUserId']);
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
        $stmt->bindParam(":imageId", $imageId);

        if ($stmt->execute()) {

            $result = array("error" => false);
        }

        return $result;
    }

    public function info($imageId)
    {
        $result = array("error" => true,
                        "error_code" => ERROR_CODE_INITIATE);

        $stmt = $this->db->prepare("SELECT * FROM images WHERE id = (:imageId) LIMIT 1");
        $stmt->bindParam(":imageId", $imageId);

        if ($stmt->execute()) {

            if ($stmt->rowCount() > 0) {

                $row = $stmt->fetch();

                $time = new language($this->db, $this->language);

                $profile = new profile($this->db, $row['fromUserId']);
                $profileInfo = $profile->getVeryShort();
                unset($profile);

                $imageUrl = "";
                if ($row['imageUrl'] != '') {
                    $imageUrl = APP_URL . "/" . MY_IMAGES_PATH . $row['imageUrl'];
                }
                
                $bigPhotoUrl = "";
                if ($profileInfo['bigPhotoUrl'] != '') {
                    $bigPhotoUrl = APP_URL . "/" . PROFILE_PHOTO_PATH . basename($profileInfo['bigPhotoUrl']);
                }

                $result = array("error" => false,
                                "error_code" => ERROR_SUCCESS,
                                "id" => $row['id'],
                                "itemType" => $row['itemType'],
                                "fromUserId" => $row['fromUserId'],
                                "fromUserUsername" => $profileInfo['username'],
                                "fromUserFullname" => $profileInfo['fullname'],
                                "fromUserPhoto" => $bigPhotoUrl,
                                "fromUserPhotoUrl" => $bigPhotoUrl,
                                "fromUserOnline" => $profileInfo['online'],
                                "lat" => $row['lat'],
                                "lng" => $row['lng'],
                                "imageUrl" => $imageUrl,
                                "createAt" => $row['createAt'],
                                "date" => date("Y-m-d H:i:s", $row['createAt']),
                                "timeAgo" => $time->timeAgo($row['createAt']),
                                "removeAt" => $row['removeAt']);
            }
        }

        return $result;
    }

    public function get($profileId, $imageId = 0, $itemType = -1, $limit = 20)
    {
        if ($imageId == 0) {

            $imageId = $this->getMaxId();
            $imageId++;
        }

        $images = array(
            "error" => false,
            "error_code" => ERROR_SUCCESS,
            "imageId" => $imageId,
            "itemId" => $imageId,
            "images" => array()
        );

        if ($this->getRequestFrom() == $profileId) {

            if ($itemType != -1) {

                $stmt = $this->db->prepare("SELECT id FROM images WHERE fromUserId = (:fromUserId) AND itemType = (:itemType) AND removeAt = 0 AND id < (:imageId) ORDER BY id DESC LIMIT :limit");
                $stmt->bindParam(':fromUserId', $profileId);
                $stmt->bindParam(':itemType', $itemType);
                $stmt->bindParam(':imageId', $imageId);
                $stmt->bindParam(':limit', $limit);
            } else {

                $stmt = $this->db->prepare("SELECT id FROM images WHERE fromUserId = (:fromUserId) AND removeAt = 0 AND id < (:imageId) ORDER BY id DESC LIMIT :limit");
                $stmt->bindParam(':fromUserId', $profileId);
                $stmt->bindParam(':imageId', $imageId);
                $stmt->bindParam(':limit', $limit);
            }
        } else {

            if ($itemType != -1) {

                $stmt = $this->db->prepare("SELECT id FROM images WHERE fromUserId = (:fromUserId) AND itemType = (:itemType) AND removeAt = 0 AND id < (:imageId) ORDER BY id DESC LIMIT :limit");
                $stmt->bindParam(':fromUserId', $profileId);
                $stmt->bindParam(':itemType', $itemType);
                $stmt->bindParam(':imageId', $imageId);
                $stmt->bindParam(':limit', $limit);
            } else {

                $stmt = $this->db->prepare("SELECT id FROM images WHERE fromUserId = (:fromUserId) AND removeAt = 0 AND id < (:imageId) ORDER BY id DESC LIMIT :limit");
                $stmt->bindParam(':fromUserId', $profileId);
                $stmt->bindParam(':imageId', $imageId);
                $stmt->bindParam(':limit', $limit);
            }
        }

        if ($stmt->execute()) {

            while ($row = $stmt->fetch()) {

                $imageInfo = $this->info($row['id']);

                array_push($images['images'], $imageInfo);

                $images['imageId'] = $imageInfo['id'];
                $images['itemId'] = $imageInfo['id'];

                unset($imageInfo);
            }
        }

        return $images;
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
