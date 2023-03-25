<?php

/*!
 * https://hindbyte.com
 * hindbyte@gmail.com
 *
 * Copyright 2012-2021 Demyanchuk Dmitry (hindbyte@gmail.com)
 */

class comments extends db_connect
{

	private $requestFrom = 0;
    private $language = 'en';

	public function __construct($dbo = NULL)
    {
		parent::__construct($dbo);
	}

    public function getAllCommentsCount()
    {
        $stmt = $this->db->prepare("SELECT count(*) FROM images_comments WHERE removeAt = 0");
        $stmt->execute();

        return $number_of_rows = $stmt->fetchColumn();
    }

    public function add($itemId, $itemType, $itemInfo, $text, $replyToUserId = 0)
    {
        $result = array(
            "error" => true,
            "error_code" => ERROR_CODE_INITIATE
        );

        $spam = new spam($this->db);
        $spam->setRequestFrom($this->getRequestFrom());

        if ($spam->getCommentsCount() > 20) {

            return $result;
        }

        unset($spam);

        if (strlen($text) == 0) {

            return $result;
        }

        $currentTime = time();
        $ip_addr = helper::ip_addr();
        $u_agent = helper::u_agent();

        $stmt = $this->db->prepare("INSERT INTO images_comments (fromUserId, replyToUserId, imageId, comment, createAt, ip_addr, u_agent) value (:fromUserId, :replyToUserId, :imageId, :comment, :createAt, :ip_addr, :u_agent)");
        $stmt->bindParam(":fromUserId", $this->requestFrom, PDO::PARAM_INT);
        $stmt->bindParam(":replyToUserId", $replyToUserId, PDO::PARAM_INT);
        $stmt->bindParam(":imageId", $itemId, PDO::PARAM_INT);
        $stmt->bindParam(":comment", $text, PDO::PARAM_STR);
        $stmt->bindParam(":createAt", $currentTime, PDO::PARAM_INT);
        $stmt->bindParam(":ip_addr", $ip_addr, PDO::PARAM_STR);
        $stmt->bindParam(":u_agent", $u_agent, PDO::PARAM_STR);

        if ($stmt->execute()) {

            $result = array(
                "error" => false,
                "error_code" => ERROR_SUCCESS,
                "commentId" => $this->db->lastInsertId(),
                "comment" => $this->info($this->db->lastInsertId())
            );

            if (($this->getRequestFrom() != $itemInfo['owner']['id']) && ($replyToUserId != $itemInfo['owner']['id'])) {
                $fcm = new fcm($this->db);
                $fcm->setRequestFrom($this->getRequestFrom());
                $fcm->setRequestTo($itemInfo['owner']['id']);
                $fcm->setType(GCM_NOTIFY_IMAGE_COMMENT);
                $fcm->setTitle("You have a new comment.");
                $fcm->prepare();
                $fcm->send();
                unset($fcm);

                $notify = new notify($this->db);
                $notifyId = $notify->createNotify($itemInfo['owner']['id'], $this->getRequestFrom(), NOTIFY_TYPE_IMAGE_COMMENT, $itemId);
                unset($notify);

                $this->setNotifyId($result['commentId'], $notifyId);
            }

            if ($replyToUserId != $this->getRequestFrom() && $replyToUserId != 0) {

                $fcm = new fcm($this->db);
                $fcm->setRequestFrom($this->getRequestFrom());
                $fcm->setRequestTo($replyToUserId);
                $fcm->setType(GCM_NOTIFY_IMAGE_COMMENT_REPLY);
                $fcm->setTitle("You have a new reply to comment.");
                $fcm->prepare();
                $fcm->send();
                unset($fcm);

                $notify = new notify($this->db);
                $notifyId = $notify->createNotify($replyToUserId, $this->getRequestFrom(), NOTIFY_TYPE_IMAGE_COMMENT_REPLY, $itemId);
                unset($notify);

                $this->setNotifyId($result['commentId'], $notifyId);
            }

            $gallery = new gallery($this->db);
            $gallery->recalculate($itemId);
            unset($gallery);
        }

        return $result;
    }

    private function setNotifyId($commentId, $notifyId)
    {
        $stmt = $this->db->prepare("UPDATE images_comments SET notifyId = (:notifyId) WHERE id = (:commentId)");
        $stmt->bindParam(":commentId", $commentId, PDO::PARAM_INT);
        $stmt->bindParam(":notifyId", $notifyId, PDO::PARAM_INT);

        $stmt->execute();
    }

    public function remove($commentId, $commentInfo)
    {
        $result = array(
            "error" => true,
            "error_code" => ERROR_CODE_INITIATE
        );

        if (empty($commentInfo)) {
            $commentInfo = $this->info($commentId);
        }

        $currentTime = time();
        $stmt = $this->db->prepare("UPDATE images_comments SET removeAt = (:removeAt) WHERE id = (:commentId)");
        $stmt->bindParam(":commentId", $commentId, PDO::PARAM_INT);
        $stmt->bindParam(":removeAt", $currentTime, PDO::PARAM_INT);

        if ($stmt->execute()) {
            $gallery = new gallery($this->db);
            $gallery->recalculate($commentInfo['imageId']);
            unset($gallery);

            $notify = new notify($this->db);
            $notify->remove($commentInfo['notifyId']);
            unset($notify);

            $result = array(
                "error" => false,
                "error_code" => ERROR_SUCCESS
            );
        }

        return $result;
    }

    public function commentsRemoveAll($imageId) {
        $currentTime = time();

        $stmt = $this->db->prepare("UPDATE images_comments SET removeAt = (:removeAt) WHERE imageId = (:imageId)");
        $stmt->bindParam(":imageId", $imageId, PDO::PARAM_INT);
        $stmt->bindParam(":removeAt", $currentTime, PDO::PARAM_INT);
    }

    public function info($commentId)
    {
        $result = array(
            "error" => true,
            "error_code" => ERROR_CODE_INITIATE
        );

        $stmt = $this->db->prepare("SELECT * FROM images_comments WHERE id = (:commentId) LIMIT 1");
        $stmt->bindParam(":commentId", $commentId, PDO::PARAM_INT);

        if ($stmt->execute()) {

            if ($stmt->rowCount() > 0) {

                $row = $stmt->fetch();

                $result = $this->quickInfo($row);
            }
        }

        return $result;
    }

    public function quickInfo($row, $itemFromUserId = 0)
    {
        $time = new language($this->db, $this->language);

        $profile = new profile($this->db, $row['fromUserId']);
        $fromUserId = $profile->getVeryShort();
        unset($profile);

        $replyToUserId = $row['replyToUserId'];
        $replyToUserUsername = "";
        $replyToFullname = "";

        $replyToUser = array();

        if ($replyToUserId != 0) {

            $profile = new profile($this->db, $row['replyToUserId']);
            $replyToUser = $profile->getVeryShort();
            unset($profile);

            $replyToUserUsername = $replyToUser['username'];
            $replyToFullname = $replyToUser['fullname'];
        }

        $lowPhotoUrl = APP_URL."/assets/img/profile_default_photo.png";

        if (strlen($fromUserId['lowPhotoUrl']) != 0) {

            $lowPhotoUrl = $fromUserId['lowPhotoUrl'];
        }

        $result = array(
            "error" => false,
            "error_code" => ERROR_SUCCESS,
            "id" => $row['id'],
            "owner" => $fromUserId,
            "replyTo" => $replyToUser,
            "comment" => htmlspecialchars_decode(stripslashes($row['comment'])),
            "fromUserId" => $row['fromUserId'],
            "replyToUserId" => $replyToUserId,
            "replyToUserUsername" => $replyToUserUsername,
            "replyToFullname" => $replyToFullname,
            "imageId" => $row['imageId'],
            "itemId" => $row['imageId'],
            "itemFromUserId" => $itemFromUserId,
            "createAt" => $row['createAt'],
            "removeAt" => $row['removeAt'],
            "notifyId" => $row['notifyId'],
            "timeAgo" => $time->timeAgo($row['createAt']));

        if ($itemFromUserId == 0) {

            $gallery = new gallery($this->db);
            $gallery->setRequestFrom($this->getRequestFrom());

            $imageInfo = $gallery->info($row['imageId']);

            $result['imageFromUserId'] = $imageInfo['owner']['id'];
        }

        return $result;
    }

    public function get($itemId, $itemIndex = 0, $itemInfo = array())
    {
        if ($itemIndex == 0) {
            $itemIndex = 10000000;
        }

        if (empty($itemInfo)) {
            $gallery = new gallery($this->db);
            $gallery->setRequestFrom($this->getRequestFrom());

            $itemInfo = $gallery->info($itemId);
        }

        $result = array(
            "error" => false,
            "error_code" => ERROR_SUCCESS,
            "itemIndex" => $itemIndex,
            "itemId" => $itemId,
            "items" => array());

        $stmt = $this->db->prepare("SELECT * FROM images_comments WHERE imageId = (:itemId) AND id < (:itemIndex) AND removeAt = 0 ORDER BY id DESC LIMIT 70");
        $stmt->bindParam(':itemId', $itemId, PDO::PARAM_INT);
        $stmt->bindParam(':itemIndex', $itemIndex, PDO::PARAM_INT);

        if ($stmt->execute()) {

            while ($row = $stmt->fetch()) {

                array_push($result['items'], $this->quickInfo($row, $itemInfo['owner']['id']));

                $result['itemIndex'] = $row['id'];
            }
        }

        return $result;
    }

    public function stream($itemId = 0)
    {
        if ($itemId == 0) {

            $itemId = 10000000;
            $itemId++;
        }

        $result = array(
            "error" => false,
            "error_code" => ERROR_SUCCESS,
            "itemId" => $itemId,
            "items" => array()
        );

        $stmt = $this->db->prepare("SELECT * FROM images_comments WHERE removeAt = 0 AND id < (:itemId) ORDER BY id DESC LIMIT 20");
        $stmt->bindParam(':itemId', $itemId, PDO::PARAM_INT);

        if ($stmt->execute()) {

            if ($stmt->rowCount() > 0) {

                while ($row = $stmt->fetch()) {

                    array_push($result['items'], $this->quickInfo($row));

                    $result['itemId'] = $row['id'];
                }
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
}