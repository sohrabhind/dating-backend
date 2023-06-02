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

    private function getMaxIdLikes()
    {
        $stmt = $this->db->prepare("SELECT MAX(id) FROM images_likes");
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

    public function add($mode, $comment, $imgUrl = "", $itemType = 0)
    {
        $result = array(
            "error" => true,
            "error_code" => ERROR_CODE_INITIATE
        );

        if (strlen($imgUrl) == 0) {

            return $result;
        }

        if (strlen($comment) != 0) {

            $comment = $comment." ";
        }

        $currentTime = time();
        $ip_addr = helper::ip_addr();

        $settings = new settings($this->db);
        $app_settings = $settings->get();
        unset($settings);

        $stmt = $this->db->prepare("INSERT INTO images (fromUserId, accessMode, itemType, comment, imgUrl, createAt, ip_addr) value (:fromUserId, :accessMode, :itemType, :comment, :imgUrl, :createAt, :ip_addr)");
        $stmt->bindParam(":fromUserId", $this->requestFrom, PDO::PARAM_INT);
        $stmt->bindParam(":accessMode", $mode, PDO::PARAM_INT);
        $stmt->bindParam(":itemType", $itemType, PDO::PARAM_INT);
        $stmt->bindParam(":comment", $comment, PDO::PARAM_STR);
        $stmt->bindParam(":imgUrl", $imgUrl, PDO::PARAM_STR);
        $stmt->bindParam(":createAt", $currentTime, PDO::PARAM_INT);
        $stmt->bindParam(":ip_addr", $ip_addr, PDO::PARAM_STR);

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

        if ($imageInfo['fromUserId'] != $this->getRequestFrom()) {

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

            //remove all comments to post

            $stmt3 = $this->db->prepare("UPDATE images_comments SET removeAt = (:removeAt) WHERE imageId = (:imageId)");
            $stmt3->bindParam(":removeAt", $currentTime, PDO::PARAM_INT);
            $stmt3->bindParam(":imageId", $imageId, PDO::PARAM_INT);
            $stmt3->execute();

            //remove all likes to post

            $stmt4 = $this->db->prepare("UPDATE images_likes SET removeAt = (:removeAt) WHERE imageId = (:imageId) AND removeAt = 0");
            $stmt4->bindParam(":imageId", $imageId, PDO::PARAM_INT);
            $stmt4->bindParam(":removeAt", $currentTime, PDO::PARAM_INT);
            $stmt4->execute();

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
        $stmt->bindParam(":imageId", $imageId, PDO::PARAM_INT);

        if ($stmt->execute()) {

            $result = array("error" => false);
        }

        return $result;
    }

    private function getLikesCount($imageId)
    {
        $stmt = $this->db->prepare("SELECT count(*) FROM images_likes WHERE imageId = (:imageId) AND removeAt = 0");
        $stmt->bindParam(":imageId", $imageId, PDO::PARAM_INT);
        $stmt->execute();

        return $number_of_rows = $stmt->fetchColumn();
    }

    public function recalculate($imageId) {

        $comments_count = 0;
        $likes_count = 0;
        $rating = 0;

        $likes_count = $this->getLikesCount($imageId);

        $images = new images($this->db);
        $comments_count = $images->commentsCount($imageId);
        unset($comments);

        $rating = $likes_count + $comments_count;

        $stmt = $this->db->prepare("UPDATE images SET likesCount = (:likesCount), commentsCount = (:commentsCount), rating = (:rating) WHERE id = (:imageId)");
        $stmt->bindParam(":likesCount", $likes_count, PDO::PARAM_INT);
        $stmt->bindParam(":commentsCount", $comments_count, PDO::PARAM_INT);
        $stmt->bindParam(":rating", $rating, PDO::PARAM_INT);
        $stmt->bindParam(":imageId", $imageId, PDO::PARAM_INT);
        $stmt->execute();

        $account = new account($this->db, $this->requestFrom);
        $account->updateCounters();
        unset($account);
    }

    public function like($imageId, $fromUserId)
    {
        $account = new account($this->db, $fromUserId);
        $account->setLastActive();
        unset($account);

        $result = array(
            "error" => true,
            "error_code" => ERROR_CODE_INITIATE
        );

        $spam = new spam($this->db);
        $spam->setRequestFrom($this->getRequestFrom());

        if ($spam->getGalleryLikesCount() > 30) {

            return $result;
        }

        unset($spam);

        $imageInfo = $this->info($imageId);

        if ($imageInfo['error']) {

            return $result;
        }

        if ($imageInfo['removeAt'] != 0) {

            return $result;
        }

        if ($this->is_like_exists($imageId, $fromUserId)) {

            $removeAt = time();

            $stmt = $this->db->prepare("UPDATE images_likes SET removeAt = (:removeAt) WHERE imageId = (:imageId) AND fromUserId = (:fromUserId) AND removeAt = 0");
            $stmt->bindParam(":fromUserId", $fromUserId, PDO::PARAM_INT);
            $stmt->bindParam(":imageId", $imageId, PDO::PARAM_INT);
            $stmt->bindParam(":removeAt", $removeAt, PDO::PARAM_INT);
            $stmt->execute();

            $notify = new notify($this->db);
            $notify->removeNotify($imageInfo['fromUserId'], $fromUserId, NOTIFY_TYPE_IMAGE_LIKE, $imageId);
            unset($notify);

        } else {

            $createAt = time();
            $ip_addr = helper::ip_addr();

            $stmt = $this->db->prepare("INSERT INTO images_likes (toUserId, fromUserId, imageId, createAt, ip_addr) value (:toUserId, :fromUserId, :imageId, :createAt, :ip_addr)");
            $stmt->bindParam(":toUserId", $imageInfo['fromUserId'], PDO::PARAM_INT);
            $stmt->bindParam(":fromUserId", $fromUserId, PDO::PARAM_INT);
            $stmt->bindParam(":imageId", $imageId, PDO::PARAM_INT);
            $stmt->bindParam(":createAt", $createAt, PDO::PARAM_INT);
            $stmt->bindParam(":ip_addr", $ip_addr, PDO::PARAM_STR);
            $stmt->execute();

            if ($imageInfo['fromUserId'] != $fromUserId) {

                $blacklist = new blacklist($this->db);
                $blacklist->setRequestFrom($imageInfo['fromUserId']);

                if (!$blacklist->isExists($fromUserId)) {

                    $account = new account($this->db, $imageInfo['fromUserId']);

                    $fcm = new fcm($this->db);
                    $fcm->setRequestFrom($this->getRequestFrom());
                    $fcm->setRequestTo($imageInfo['fromUserId']);
                    $fcm->setType(GCM_NOTIFY_IMAGE_LIKE);
                    $fcm->setTitle("You have new like");
                    $fcm->prepare();
                    $fcm->send();
                    unset($fcm);

                    unset($account);

                    $notify = new notify($this->db);
                    $notify->createNotify($imageInfo['fromUserId'], $fromUserId, NOTIFY_TYPE_IMAGE_LIKE, $imageId);
                    unset($notify);
                }

                unset($blacklist);
            }
        }

        $this->recalculate($imageId);

        $img_info = $this->info($imageId);

        if ($img_info['fromUserId'] != $this->requestFrom) {

            $account = new account($this->db, $img_info['fromUserId']);
            $account->updateCounters();
            unset($account);
        }

        $result = array("error" => false,
                        "error_code" => ERROR_SUCCESS,
                        "likesCount" => $img_info['likesCount'],
                        "iLiked" => $img_info['iLiked']);

        return $result;
    }

    private function is_like_exists($imageId, $fromUserId)
    {
        $stmt = $this->db->prepare("SELECT id FROM images_likes WHERE fromUserId = (:fromUserId) AND imageId = (:imageId) AND removeAt = 0 LIMIT 1");
        $stmt->bindParam(":fromUserId", $fromUserId, PDO::PARAM_INT);
        $stmt->bindParam(":imageId", $imageId, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {

            return true;
        }

        return false;
    }

    public function getLikers($imageId, $likeId = 0)
    {

        if ($likeId == 0) {

            $likeId = $this->getMaxIdLikes();
            $likeId++;
        }

        $likers = array("error" => false,
                        "error_code" => ERROR_SUCCESS,
                        "likeId" => $likeId,
                        "likers" => array());

        $stmt = $this->db->prepare("SELECT * FROM images_likes WHERE imageId = (:imageId) AND id < (:likeId) AND removeAt = 0 ORDER BY id DESC LIMIT 20");
        $stmt->bindParam(':imageId', $imageId, PDO::PARAM_INT);
        $stmt->bindParam(':likeId', $likeId, PDO::PARAM_INT);

        if ($stmt->execute()) {

            if ($stmt->rowCount() > 0) {

                while ($row = $stmt->fetch()) {

                    $profile = new profile($this->db, $row['fromUserId']);
                    $profile->setRequestFrom($this->requestFrom);
                    $profileInfo = $profile->getVeryShort();
                    unset($profile);

                    array_push($likers['likers'], $profileInfo);

                    $likers['likeId'] = $row['id'];
                }
            }
        }

        return $likers;
    }

    public function info($imageId)
    {
        $result = array("error" => true,
                        "error_code" => ERROR_CODE_INITIATE);

        $stmt = $this->db->prepare("SELECT * FROM images WHERE id = (:imageId) LIMIT 1");
        $stmt->bindParam(":imageId", $imageId, PDO::PARAM_INT);

        if ($stmt->execute()) {

            if ($stmt->rowCount() > 0) {

                $row = $stmt->fetch();

                $time = new language($this->db, $this->language);

                $iLiked = false;

                if ($this->requestFrom != 0) {

                    if ($this->is_like_exists($imageId, $this->requestFrom)) {

                        $iLiked = true;
                    }
                }

                $profile = new profile($this->db, $row['fromUserId']);
                $profileInfo = $profile->getVeryShort();
                unset($profile);

                $result = array("error" => false,
                                "error_code" => ERROR_SUCCESS,
                                "id" => $row['id'],
                                "accessMode" => $row['accessMode'],
                                "itemType" => $row['itemType'],
                                "fromUserId" => $row['fromUserId'],
                                
                                "fromUserUsername" => $profileInfo['username'],
                                "fromUserFullname" => $profileInfo['fullname'],
                                "fromUserPhoto" => $profileInfo['bigPhotoUrl'],
                                "fromUserPhotoUrl" => $profileInfo['bigPhotoUrl'],
                                "fromUserOnline" => $profileInfo['online'],
                                "fromUserAllowPhotosComments" => $profileInfo['allowPhotosComments'],
                                "comment" => $row['comment'],
                                "country" => $row['country'],
                                "lat" => $row['lat'],
                                "lng" => $row['lng'],
                                "imgUrl" => $row['imgUrl'],
                                "rating" => $row['rating'],
                                "commentsCount" => $row['commentsCount'],
                                "likesCount" => $row['likesCount'],
                                "iLiked" => $iLiked,
                                "createAt" => $row['createAt'],
                                "date" => date("Y-m-d H:i:s", $row['createAt']),
                                "timeAgo" => $time->timeAgo($row['createAt']),
                                "removeAt" => $row['removeAt']);
            }
        }

        return $result;
    }

    public function get($profileId, $imageId = 0, $accessMode = 0, $itemType = -1, $limit = 20)
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

        if ($accessMode == 0) {

            if ($itemType != -1) {

                $stmt = $this->db->prepare("SELECT id FROM images WHERE accessMode = 0 AND fromUserId = (:fromUserId) AND itemType = (:itemType) AND removeAt = 0 AND id < (:imageId) ORDER BY id DESC LIMIT :limit");
                $stmt->bindParam(':fromUserId', $profileId, PDO::PARAM_INT);
                $stmt->bindParam(':itemType', $itemType, PDO::PARAM_INT);
                $stmt->bindParam(':imageId', $imageId, PDO::PARAM_INT);
                $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);

            } else {

                $stmt = $this->db->prepare("SELECT id FROM images WHERE accessMode = 0 AND fromUserId = (:fromUserId) AND removeAt = 0 AND id < (:imageId) ORDER BY id DESC LIMIT :limit");
                $stmt->bindParam(':fromUserId', $profileId, PDO::PARAM_INT);
                $stmt->bindParam(':imageId', $imageId, PDO::PARAM_INT);
                $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            }

        } else {

            if ($this->getRequestFrom() == $profileId) {

                if ($itemType != -1) {

                    $stmt = $this->db->prepare("SELECT id FROM images WHERE fromUserId = (:fromUserId) AND itemType = (:itemType) AND removeAt = 0 AND id < (:imageId) ORDER BY id DESC LIMIT :limit");
                    $stmt->bindParam(':fromUserId', $profileId, PDO::PARAM_INT);
                    $stmt->bindParam(':itemType', $itemType, PDO::PARAM_INT);
                    $stmt->bindParam(':imageId', $imageId, PDO::PARAM_INT);
                    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);

                } else {

                    $stmt = $this->db->prepare("SELECT id FROM images WHERE fromUserId = (:fromUserId) AND removeAt = 0 AND id < (:imageId) ORDER BY id DESC LIMIT :limit");
                    $stmt->bindParam(':fromUserId', $profileId, PDO::PARAM_INT);
                    $stmt->bindParam(':imageId', $imageId, PDO::PARAM_INT);
                    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
                }


            } else {

                if ($itemType != -1) {

                    $stmt = $this->db->prepare("SELECT id FROM images WHERE fromUserId = (:fromUserId) AND itemType = (:itemType) AND removeAt = 0 AND id < (:imageId) ORDER BY id DESC LIMIT :limit");
                    $stmt->bindParam(':fromUserId', $profileId, PDO::PARAM_INT);
                    $stmt->bindParam(':itemType', $itemType, PDO::PARAM_INT);
                    $stmt->bindParam(':imageId', $imageId, PDO::PARAM_INT);
                    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);

                } else {

                    $stmt = $this->db->prepare("SELECT id FROM images WHERE fromUserId = (:fromUserId) AND removeAt = 0 AND id < (:imageId) ORDER BY id DESC LIMIT :limit");
                    $stmt->bindParam(':fromUserId', $profileId, PDO::PARAM_INT);
                    $stmt->bindParam(':imageId', $imageId, PDO::PARAM_INT);
                    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
                }
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
