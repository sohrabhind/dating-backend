<?php

/*!
 * ifsoft.co.uk
 *
 * http://ifsoft.com.ua, https://ifsoft.co.uk, https://hindbyte.com
 * hindbyte@gmail.com
 *
 * Copyright 2012-2020 Demyanchuk Dmitry (hindbyte@gmail.com)
 */

if (!defined("APP_SIGNATURE")) {

    header("Location: /");
    exit;
}

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
        $stmt = $this->db->prepare("SELECT count(*) FROM photos");
        $stmt->execute();

        return $number_of_rows = $stmt->fetchColumn();
    }

    private function getMaxId()
    {
        $stmt = $this->db->prepare("SELECT MAX(id) FROM photos");
        $stmt->execute();

        return $number_of_rows = $stmt->fetchColumn();
    }

    private function getMaxIdLikes()
    {
        $stmt = $this->db->prepare("SELECT MAX(id) FROM images_likes");
        $stmt->execute();

        return $number_of_rows = $stmt->fetchColumn();
    }

    public function count()
    {
        $sql = "SELECT count(*) FROM photos WHERE removeAt = 0";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return $number_of_rows = $stmt->fetchColumn();
    }

    public function add($mode, $comment, $imgUrl = "", $itemType = 0, $itemShowInStream = 1, $photoArea = "", $photoCountry = "", $photoCity = "", $photoLat = "0.000000", $photoLng = "0.000000")
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
        $u_agent = helper::u_agent();

        $settings = new settings($this->db);
        $app_settings = $settings->get();
        unset($settings);

        $stmt = $this->db->prepare("INSERT INTO photos (fromUserId, accessMode, itemType, itemShowInStream, comment, imgUrl, area, country, city, lat, lng, createAt, ip_addr, u_agent) value (:fromUserId, :accessMode, :itemType, :itemShowInStream, :comment, :imgUrl, :area, :country, :city, :lat, :lng, :createAt, :ip_addr, :u_agent)");
        $stmt->bindParam(":fromUserId", $this->requestFrom, PDO::PARAM_INT);
        $stmt->bindParam(":accessMode", $mode, PDO::PARAM_INT);
        $stmt->bindParam(":itemType", $itemType, PDO::PARAM_INT);
        $stmt->bindParam(":itemShowInStream", $itemShowInStream, PDO::PARAM_INT);
        $stmt->bindParam(":comment", $comment, PDO::PARAM_STR);
        $stmt->bindParam(":imgUrl", $imgUrl, PDO::PARAM_STR);
        $stmt->bindParam(":area", $photoArea, PDO::PARAM_STR);
        $stmt->bindParam(":country", $photoCountry, PDO::PARAM_STR);
        $stmt->bindParam(":city", $photoCity, PDO::PARAM_STR);
        $stmt->bindParam(":lat", $photoLat, PDO::PARAM_STR);
        $stmt->bindParam(":lng", $photoLng, PDO::PARAM_STR);
        $stmt->bindParam(":createAt", $currentTime, PDO::PARAM_INT);
        $stmt->bindParam(":ip_addr", $ip_addr, PDO::PARAM_STR);
        $stmt->bindParam(":u_agent", $u_agent, PDO::PARAM_STR);

        if ($stmt->execute()) {

            $result = array(
                "error" => false,
                "error_code" => ERROR_SUCCESS,
                "photoId" => $this->db->lastInsertId(),
                "itemId" => $this->db->lastInsertId(),
                "photo" => $this->info($this->db->lastInsertId())
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

        $stmt = $this->db->prepare("UPDATE photos SET removeAt = (:removeAt) WHERE fromUserId = (:fromUserId) AND removeAt = 0");
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

    public function remove($photoId)
    {
        $result = array("error" => true);

        $photoInfo = $this->info($photoId);

        if ($photoInfo['error']) {

            return $result;
        }

        if ($photoInfo['owner']['id'] != $this->getRequestFrom()) {

            return $result;
        }

        $currentTime = time();

        $stmt = $this->db->prepare("UPDATE photos SET removeAt = (:removeAt) WHERE id = (:photoId)");
        $stmt->bindParam(":photoId", $photoId, PDO::PARAM_INT);
        $stmt->bindParam(":removeAt", $currentTime, PDO::PARAM_INT);

        if ($stmt->execute()) {

            $stmt2 = $this->db->prepare("DELETE FROM notifications WHERE itemId = (:itemId) AND notifyType > 6");
            $stmt2->bindParam(":itemId", $photoId, PDO::PARAM_INT);
            $stmt2->execute();

            //remove all comments to post

            $stmt3 = $this->db->prepare("UPDATE images_comments SET removeAt = (:removeAt) WHERE imageId = (:imageId)");
            $stmt3->bindParam(":removeAt", $currentTime, PDO::PARAM_INT);
            $stmt3->bindParam(":imageId", $photoId, PDO::PARAM_INT);
            $stmt3->execute();

            //remove all likes to post

            $stmt4 = $this->db->prepare("UPDATE images_likes SET removeAt = (:removeAt) WHERE imageId = (:imageId) AND removeAt = 0");
            $stmt4->bindParam(":imageId", $photoId, PDO::PARAM_INT);
            $stmt4->bindParam(":removeAt", $currentTime, PDO::PARAM_INT);
            $stmt4->execute();

            $result = array("error" => false);

            $account = new account($this->db, $photoInfo['owner']['id']);
            $account->updateCounters();
            unset($account);
        }

        return $result;
    }

    public function restore($photoId)
    {
        $result = array("error" => true);

        $photoInfo = $this->info($photoId);

        if ($photoInfo['error'] === true) {

            return $result;
        }

        $stmt = $this->db->prepare("UPDATE photos SET removeAt = 0 WHERE id = (:photoId)");
        $stmt->bindParam(":photoId", $photoId, PDO::PARAM_INT);

        if ($stmt->execute()) {

            $result = array("error" => false);
        }

        return $result;
    }

    private function getLikesCount($itemId)
    {
        $stmt = $this->db->prepare("SELECT count(*) FROM images_likes WHERE imageId = (:itemId) AND removeAt = 0");
        $stmt->bindParam(":itemId", $itemId, PDO::PARAM_INT);
        $stmt->execute();

        return $number_of_rows = $stmt->fetchColumn();
    }

    public function getCommentsCount($itemId)
    {
        $stmt = $this->db->prepare("SELECT count(*) FROM images_comments WHERE imageId = (:itemId) AND removeAt = 0");
        $stmt->bindParam(":itemId", $itemId, PDO::PARAM_INT);
        $stmt->execute();

        return $number_of_rows = $stmt->fetchColumn();
    }

    public function recalculate($itemId)
    {
        $result = array(
            "error" => true,
            "error_code" => ERROR_CODE_INITIATE
        );

        $comments_count = 0;
        $likes_count = 0;
        $rating = 0;

        $likes_count = $this->getLikesCount($itemId);
        $comments_count = $this->getCommentsCount($itemId);

        $rating = $likes_count + $comments_count;

        $stmt = $this->db->prepare("UPDATE photos SET likesCount = (:likesCount), commentsCount = (:commentsCount), rating = (:rating) WHERE id = (:itemId)");
        $stmt->bindParam(":likesCount", $likes_count, PDO::PARAM_INT);
        $stmt->bindParam(":commentsCount", $comments_count, PDO::PARAM_INT);
        $stmt->bindParam(":rating", $rating, PDO::PARAM_INT);
        $stmt->bindParam(":itemId", $itemId, PDO::PARAM_INT);

        if ($stmt->execute()) {

            $result = array(
                "error" => true,
                "error_code" => ERROR_CODE_INITIATE,
                "comments_count" => $comments_count,
                "likes_count" => $likes_count,
                "rating" => $rating
            );
        }

        return $result;
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

            $imageInfo['myLike'] = false;
            $imageInfo['likesCount'] = $imageInfo['likesCount'] - 1;

            $notify = new notify($this->db);
            $notify->removeNotify($imageInfo['owner']['id'], $fromUserId, NOTIFY_TYPE_IMAGE_LIKE, $imageId);
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

            $imageInfo['myLike'] = true;
            $imageInfo['likesCount'] = $imageInfo['likesCount'] + 1;

            if ($imageInfo['owner']['id'] != $fromUserId) {

                $blacklist = new blacklist($this->db);
                $blacklist->setRequestFrom($imageInfo['fromUserId']);

                if (!$blacklist->isExists($fromUserId)) {

                    $account = new account($this->db, $imageInfo['owner']['id']);

                    $fcm = new fcm($this->db);
                    $fcm->setRequestFrom($this->getRequestFrom());
                    $fcm->setRequestTo($imageInfo['owner']['id']);
                    $fcm->setType(GCM_NOTIFY_IMAGE_LIKE);
                    $fcm->setTitle("You have new like");
                    $fcm->prepare();
                    $fcm->send();
                    unset($fcm);

                    unset($account);

                    $notify = new notify($this->db);
                    $notify->createNotify($imageInfo['owner']['id'], $fromUserId, NOTIFY_TYPE_IMAGE_LIKE, $imageId);
                    unset($notify);
                }

                unset($blacklist);
            }
        }

        $this->recalculate($imageId);

        $result = array(
            "error" => false,
            "error_code" => ERROR_SUCCESS,
            "likesCount" => $imageInfo['likesCount'],
            "myLike" => $imageInfo['myLike']
        );

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

    public function getLikes($itemId, $itemIndex = 0)
    {

        if ($itemIndex == 0) {

            $itemIndex = 1000000;
        }

        $result = array(
            "error" => false,
            "error_code" => ERROR_SUCCESS,
            "itemIndex" => $itemIndex,
            "items" => array()
        );

        $stmt = $this->db->prepare("SELECT * FROM images_likes WHERE imageId = (:itemId) AND id < (:itemIndex) AND removeAt = 0 ORDER BY id DESC LIMIT 20");
        $stmt->bindParam(':itemId', $itemId, PDO::PARAM_INT);
        $stmt->bindParam(':itemIndex', $itemIndex, PDO::PARAM_INT);

        if ($stmt->execute()) {

            if ($stmt->rowCount() > 0) {

                while ($row = $stmt->fetch()) {

                    $profile = new profile($this->db, $row['fromUserId']);
                    $profile->setRequestFrom($this->getRequestFrom());
                    $profileInfo = $profile->getVeryShort();
                    unset($profile);

                    array_push($result['items'], $profileInfo);

                    $result['itemIndex'] = $row['id'];
                }
            }
        }

        return $result;
    }

    public function info($itemId)
    {
        $result = array(
            "error" => true,
            "error_code" => ERROR_CODE_INITIATE
        );

        $stmt = $this->db->prepare("SELECT * FROM photos WHERE id = (:itemId) LIMIT 1");
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

        $myLike = false;

        if ($this->getRequestFrom() != 0) {

            if ($this->is_like_exists($row['id'], $this->getRequestFrom())) {

                $myLike = true;
            }
        }

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
            "comment" => htmlspecialchars_decode(stripslashes($row['comment'])),
            "area" => htmlspecialchars_decode(stripslashes($row['area'])),
            "country" => htmlspecialchars_decode(stripslashes($row['country'])),
            "city" => htmlspecialchars_decode(stripslashes($row['city'])),
            "lat" => $row['lat'],
            "lng" => $row['lng'],
            "imgUrl" => $row['imgUrl'],
            "rating" => $row['rating'],
            "commentsCount" => $row['commentsCount'],
            "likesCount" => $row['likesCount'],
            "myLike" => $myLike,
            "showInStream" => $row['itemShowInStream'],
            "createAt" => $row['createAt'],
            "date" => date("Y-m-d H:i:s", $row['createAt']),
            "timeAgo" => $time->timeAgo($row['createAt']),
            "removeAt" => $row['removeAt']
        );

        return $result;
    }

    // Get items
    public function get($itemId = 0, $profileId = 0, $stream = false, $access = false, $limit = 20)
    {

        if ($itemId == 0) {

            $itemId = 1000000;
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

        $streamSql = "";

        if ($stream) {

            $streamSql = " AND itemShowInStream <> 0";
        }

        $endSql = " ORDER BY id DESC LIMIT {$limit}";

        $sql = "SELECT * FROM photos WHERE removeAt = 0 AND id < {$itemId}".$profileSql.$accessSql.$streamSql.$endSql;
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
