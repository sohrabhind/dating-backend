<?php

/*!
 * ifsoft.co.uk
 *
 * http://ifsoft.com.ua, https://ifsoft.co.uk, https://hindbyte.com
 * hindbyte@gmail.com
 *
 * Copyright 2012-2020 Demyanchuk Dmitry (hindbyte@gmail.com)
 */

class images extends db_connect
{

	private $requestFrom = 0;
    private $language = 'en';

	public function __construct($dbo = NULL)
    {
		parent::__construct($dbo);
	}

    public function allCommentsCount()
    {
        $stmt = $this->db->prepare("SELECT max(id) FROM images_comments");
        $stmt->execute();

        return $number_of_rows = $stmt->fetchColumn();
    }

    public function getCommentsCount()
    {
        $stmt = $this->db->prepare("SELECT count(*) FROM images_comments WHERE removeAt = 0");
        $stmt->execute();

        return $number_of_rows = $stmt->fetchColumn();
    }

    public function commentsCount($imageId)
    {
        $stmt = $this->db->prepare("SELECT count(*) FROM images_comments WHERE imageId = (:imageId) AND removeAt = 0");
        $stmt->bindParam(":imageId", $imageId, PDO::PARAM_INT);
        $stmt->execute();

        return $number_of_rows = $stmt->fetchColumn();
    }

    public function commentsCreate($imageId, $text, $notifyId = 0, $replyToUserId = 0)
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

        $photos = new photos($this->db);

        $imageInfo = $photos->info($imageId);

        $currentTime = time();
        $ip_addr = helper::ip_addr();
        $u_agent = helper::u_agent();

        $stmt = $this->db->prepare("INSERT INTO images_comments (fromUserId, replyToUserId, imageId, comment, createAt, notifyId, ip_addr, u_agent) value (:fromUserId, :replyToUserId, :imageId, :comment, :createAt, :notifyId, :ip_addr, :u_agent)");
        $stmt->bindParam(":fromUserId", $this->requestFrom, PDO::PARAM_INT);
        $stmt->bindParam(":replyToUserId", $replyToUserId, PDO::PARAM_INT);
        $stmt->bindParam(":imageId", $imageId, PDO::PARAM_INT);
        $stmt->bindParam(":comment", $text, PDO::PARAM_STR);
        $stmt->bindParam(":createAt", $currentTime, PDO::PARAM_INT);
        $stmt->bindParam(":notifyId", $notifyId, PDO::PARAM_INT);
        $stmt->bindParam(":ip_addr", $ip_addr, PDO::PARAM_STR);
        $stmt->bindParam(":u_agent", $u_agent, PDO::PARAM_STR);

        if ($stmt->execute()) {

            $result = array("error" => false,
                            "error_code" => ERROR_SUCCESS,
                            "commentId" => $this->db->lastInsertId(),
                            "comment" => $this->commentsInfo($this->db->lastInsertId()));

            $account = new account($this->db, $this->requestFrom);
            $account->setLastActive();
            unset($account);

            if (($this->requestFrom != $imageInfo['fromUserId']) && ($replyToUserId != $imageInfo['fromUserId'])) {

                $account = new account($this->db, $imageInfo['fromUserId']);

                $fcm = new fcm($this->db);
                $fcm->setRequestFrom($this->getRequestFrom());
                $fcm->setRequestTo($imageInfo['fromUserId']);
                $fcm->setType(GCM_NOTIFY_IMAGE_COMMENT);
                $fcm->setTitle("You have a new comment.");
                $fcm->prepare();
                $fcm->send();
                unset($fcm);

                $notify = new notify($this->db);
                $notifyId = $notify->createNotify($imageInfo['fromUserId'], $this->requestFrom, NOTIFY_TYPE_IMAGE_COMMENT, $imageInfo['id']);
                unset($notify);

                $this->commentsSetNotifyId($result['commentId'], $notifyId);

                unset($account);
            }

            if ($replyToUserId != $this->requestFrom && $replyToUserId != 0) {

                $account = new account($this->db, $replyToUserId);

                $fcm = new fcm($this->db);
                $fcm->setRequestFrom($this->getRequestFrom());
                $fcm->setRequestTo($replyToUserId);
                $fcm->setType(GCM_NOTIFY_IMAGE_COMMENT_REPLY);
                $fcm->setTitle("You have a new reply to comment.");
                $fcm->prepare();
                $fcm->send();
                unset($fcm);

                $notify = new notify($this->db);
                $notifyId = $notify->createNotify($replyToUserId, $this->requestFrom, NOTIFY_TYPE_IMAGE_COMMENT_REPLY, $imageInfo['id']);
                unset($notify);

                $this->commentsSetNotifyId($result['commentId'], $notifyId);

                unset($account);
            }

            $photos->recalculate($imageId);
        }

        unset($photos);

        return $result;
    }

    private function commentsSetNotifyId($commentId, $notifyId)
    {
        $stmt = $this->db->prepare("UPDATE images_comments SET notifyId = (:notifyId) WHERE id = (:commentId)");
        $stmt->bindParam(":commentId", $commentId, PDO::PARAM_INT);
        $stmt->bindParam(":notifyId", $notifyId, PDO::PARAM_INT);

        $stmt->execute();
    }

    public function commentsRemove($commentId)
    {
        $result = array(
            "error" => true,
            "error_code" => ERROR_CODE_INITIATE
        );

        $commentInfo = $this->commentsInfo($commentId);

        if ($commentInfo['error']) {

            return $result;
        }

        $currentTime = time();

        $stmt = $this->db->prepare("UPDATE images_comments SET removeAt = (:removeAt) WHERE id = (:commentId)");
        $stmt->bindParam(":commentId", $commentId, PDO::PARAM_INT);
        $stmt->bindParam(":removeAt", $currentTime, PDO::PARAM_INT);

        if ($stmt->execute()) {

            $notify = new notify($this->db);
            $notify->remove($commentInfo['notifyId']);
            unset($notify);

            $photos = new photos($this->db);
            $photos->recalculate($commentInfo['imageId']);
            unset($photos);

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

    public function commentsInfo($commentId)
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

                $time = new language($this->db, $this->language);

                $profile = new profile($this->db, $row['fromUserId']);
                $fromUserId = $profile->getVeryShort();
                unset($profile);

                $replyToUserId = $row['replyToUserId'];
                $replyToUserUsername = "";
                $replyToFullname = "";

                if ($replyToUserId != 0) {

                    $profile = new profile($this->db, $row['replyToUserId']);
                    $replyToUser = $profile->getVeryShort();
                    unset($profile);

                    $replyToUserUsername = $replyToUser['username'];
                    $replyToFullname = $replyToUser['fullname'];
                }

                $bigPhotoUrl = "/assets/img/profile_default_photo.png";

                if (strlen($fromUserId['bigPhotoUrl']) != 0) {

                    $bigPhotoUrl = $fromUserId['bigPhotoUrl'];
                }

                $photos = new photos($this->db);
                $photos->setRequestFrom($this->getRequestFrom());

                $imageInfo = $photos->info($row['imageId']);

                $result = array("error" => false,
                                "error_code" => ERROR_SUCCESS,
                                "id" => $row['id'],
                                "comment" => htmlspecialchars_decode(stripslashes($row['comment'])),
                                "fromUserId" => $row['fromUserId'],
                                "fromUserState" => $fromUserId['state'],
                                "fromUserUsername" => $fromUserId['username'],
                                "fromUserFullname" => $fromUserId['fullname'],
                                "fromUserPhotoUrl" => $bigPhotoUrl,
                                "replyToUserId" => $replyToUserId,
                                "replyToUserUsername" => $replyToUserUsername,
                                "replyToFullname" => $replyToFullname,
                                "imageId" => $row['imageId'],
                                "imageFromUserId" => $imageInfo['fromUserId'],
                                "createAt" => $row['createAt'],
                                "notifyId" => $row['notifyId'],
                                "timeAgo" => $time->timeAgo($row['createAt']));
            }
        }

        return $result;
    }

    public function commentsGet($imageId, $commentId = 0)
    {
        if ($commentId == 0) {

            $commentId = $this->allCommentsCount() + 1;
        }

        $comments = array("error" => false,
                         "error_code" => ERROR_SUCCESS,
                         "commentId" => $commentId,
                         "imageId" => $imageId,
                         "comments" => array());

        $stmt = $this->db->prepare("SELECT id FROM images_comments WHERE imageId = (:imageId) AND id < (:commentId) AND removeAt = 0 ORDER BY id DESC LIMIT 70");
        $stmt->bindParam(':imageId', $imageId, PDO::PARAM_INT);
        $stmt->bindParam(':commentId', $commentId, PDO::PARAM_INT);

        if ($stmt->execute()) {

            while ($row = $stmt->fetch()) {

                $commentInfo = $this->commentsInfo($row['id']);

                array_push($comments['comments'], $commentInfo);

                $comments['commentId'] = $commentInfo['id'];

                unset($commentInfo);
            }
        }

        return $comments;
    }

    public function commentsStream($itemId = 0)
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

        $stmt = $this->db->prepare("SELECT id FROM images_comments WHERE removeAt = 0 AND id < (:itemId) ORDER BY id DESC LIMIT 20");
        $stmt->bindParam(':itemId', $itemId, PDO::PARAM_INT);

        if ($stmt->execute()) {

            if ($stmt->rowCount() > 0) {

                while ($row = $stmt->fetch()) {

                    $commentInfo = $this->commentsInfo($row['id']);

                    array_push($result['items'], $commentInfo);

                    $result['itemId'] = $commentInfo['id'];

                    unset($commentInfo);
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
