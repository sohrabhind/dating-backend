<?php

/*!
 * ifsoft.co.uk
 *
 * http://ifsoft.com.ua, http://ifsoft.co.uk
 * hindbyte@gmail.com
 *
 * Copyright 2012-2019 Demyanchuk Dmitry (https://vk.com/dmitry.demyanchuk)
 */

class messages extends db_connect
{

	private $requestFrom = 0;
    private $language = 'en';

	public function __construct($dbo = NULL)
    {
		parent::__construct($dbo);
	}

    public function messagesCountByChat($chatId)
    {
        $stmt = $this->db->prepare("SELECT count(*) FROM messages WHERE chatId = (:chatId) AND removeAt = 0");
        $stmt->bindParam(":chatId", $chatId, PDO::PARAM_INT);
        $stmt->execute();

        return $number_of_rows = $stmt->fetchColumn();
    }

    public function getMessagesCount()
    {
        $stmt = $this->db->prepare("SELECT count(*) FROM messages WHERE removeAt = 0");
        $stmt->execute();

        return $number_of_rows = $stmt->fetchColumn();
    }

    public function getMaxMessageId()
    {
        $stmt = $this->db->prepare("SELECT MAX(id) FROM messages");
        $stmt->execute();

        return $number_of_rows = $stmt->fetchColumn();
    }

    public function info2($row)
    {
        $time = new language($this->db, $this->language);

        $profile = new profile($this->db, $row['fromUserId']);
        $profileInfo = $profile->getVeryShort();
        unset($profile);

        $result = array("error" => false,
                        "error_code" => ERROR_SUCCESS,
                        "id" => $row['id'],
                        "fromUserId" => $row['fromUserId'],
                        "fromUserState" => $profileInfo['state'],
                        "fromUserUsername" => $profileInfo['username'],
                        "fromUserFullname" => $profileInfo['fullname'],
                        "fromUserOnline" => $profileInfo['online'],
                        "fromUserPhotoUrl" => $profileInfo['bigPhotoUrl'],
                        "message" => htmlspecialchars_decode(stripslashes($row['message'])),
                        "imgUrl" => $row['imgUrl'],
                        "stickerId" => $row['stickerId'],
                        "stickerImgUrl" => $row['stickerImgUrl'],
                        "createAt" => $row['createAt'],
                        "seenAt" => $row['seenAt'],
                        "date" => date("Y-m-d H:i:s", $row['createAt']),
                        "timeAgo" => $time->timeAgo($row['createAt']),
                        "removeAt" => $row['removeAt']);

        return $result;
    }


    public function getFull($chatId)
    {
        $messages = array("error" => false,
                          "error_code" => ERROR_SUCCESS,
                          "chatId" => $chatId,
                          "messagesCount" => $this->messagesCountByChat($chatId),
                          "messages" => array());

        $stmt = $this->db->prepare("SELECT * FROM messages WHERE chatId = (:chatId) AND removeAt = 0 ORDER BY id ASC");
        $stmt->bindParam(':chatId', $chatId, PDO::PARAM_INT);

        if ($stmt->execute()) {

            while ($row = $stmt->fetch()) {

                $msgInfo = $this->info2($row);

                array_push($messages['messages'], $msgInfo);

                unset($msgInfo);
            }
        }

        return $messages;
    }

    public function getStream($msgId = 0, $language = 'en')
    {
        if ($msgId == 0) {

            $msgId = $this->getMaxMessageId();
            $msgId++;
        }

        $result = array("error" => false,
                        "error_code" => ERROR_SUCCESS,
                        "msgId" => $msgId,
                        "messages" => array());

        $stmt = $this->db->prepare("SELECT * FROM messages WHERE id < (:msgId) AND removeAt = 0 ORDER BY id DESC LIMIT 20");
        $stmt->bindParam(':msgId', $msgId, PDO::PARAM_INT);

        if ($stmt->execute()) {

            if ($stmt->rowCount() > 0) {

                while ($row = $stmt->fetch()) {

                    $msgInfo = $this->info2($row);

                    array_push($result['messages'], $msgInfo);

                    $result['msgId'] = $row['id'];

                    unset($msgInfo);
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
