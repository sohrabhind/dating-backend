<?php



class messages extends db_connect
{

	private $requestFrom = 0;
    private $language = 'en';

    private $SPAM_LIST_ARRAY = array(
        "069sex",
        "069sex.com",
        "sex.com");

	public function __construct($dbo = NULL) {
		parent::__construct($dbo);
	}

    public function myActiveChatsCount() {
        $stmt = $this->db->prepare("SELECT count(id) FROM messages WHERE (fromUserId = (:userId) OR toUserId = (:userId)) AND removeAt = 0");
        $stmt->bindParam(":userId", $this->requestFrom, PDO::PARAM_INT);
        $stmt->execute();
        return $number_of_rows = $stmt->fetchColumn();
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
        $stmt = $this->db->prepare("SELECT count(id) FROM messages WHERE removeAt = 0");
        $stmt->execute();

        return $number_of_rows = $stmt->fetchColumn();
    }

    public function getMaxChatId()
    {
        $stmt = $this->db->prepare("SELECT MAX(id) FROM chats");
        $stmt->execute();

        return $number_of_rows = $stmt->fetchColumn();
    }

    public function getMaxMessageId()
    {
        $stmt = $this->db->prepare("SELECT MAX(id) FROM messages");
        $stmt->execute();

        return $number_of_rows = $stmt->fetchColumn();
    }

    public function createChat($fromUserId, $toUserId) {
        $chatId = 0;
        $currentTime = time();
        $stmt = $this->db->prepare("INSERT INTO chats (fromUserId, toUserId, createAt) value (:fromUserId, :toUserId, :createAt)");
        $stmt->bindParam(":fromUserId", $fromUserId, PDO::PARAM_INT);
        $stmt->bindParam(":toUserId", $toUserId, PDO::PARAM_INT);
        $stmt->bindParam(":createAt", $currentTime, PDO::PARAM_INT);

        if ($stmt->execute()) {
            $chatId = $this->db->lastInsertId();
        }
        return $chatId;
    }

    public function getChatId($fromUserId, $toUserId) {
        $chatId = 0;
        $stmt = $this->db->prepare("SELECT id FROM chats WHERE (fromUserId = :fromUserId AND toUserId = :toUserId) OR (fromUserId = :toUserId AND toUserId = :fromUserId) LIMIT 1");
        $stmt->bindParam(":fromUserId", $fromUserId, PDO::PARAM_INT);
        $stmt->bindParam(":toUserId", $toUserId, PDO::PARAM_INT);
        if ($stmt->execute()) {
            if ($stmt->rowCount() > 0) {
                $row = $stmt->fetch();
                $chatId = $row['id'];
            }
        }
        return $chatId;
    }
    
    function emoji_to_unicode($text) {
        $str = preg_replace_callback(
            "%(?:\xF0[\x90-\xBF][\x80-\xBF]{2} | [\xF1-\xF3][\x80-\xBF]{3} | \xF4[\x80-\x8F][\x80-\xBF]{2})%xs",
            function($emoji){
                $emojiStr = mb_convert_encoding($emoji[0], 'UTF-32', 'UTF-8');
                return strtoupper(preg_replace("/^[0]+/","U+", bin2hex($emojiStr)));
            },
            $text
        );
        return $str;
    }

    public function getMessagesFromUser($myUserId, $fromUserId) {
        $stmt = $this->db->prepare("SELECT count(id) FROM messages WHERE toUserId = (:toUserId) AND fromUserId = (:fromUserId)");
        $stmt->bindParam(':toUserId', $myUserId, PDO::PARAM_INT);
        $stmt->bindParam(':fromUserId', $fromUserId, PDO::PARAM_INT);

        if ($stmt->execute()) {
            return $number_of_rows = $stmt->fetchColumn();
        }
        return 0;
    }


    public function create($toUserId, $chatId,  $message = "", $imgUrl = "", $listId = 0, $stickerId = 0, $stickerImgUrl = "")
    {
        $result = array(
            "error" => true,
            "error_code" => ERROR_CODE_INITIATE
        );

        if ($toUserId == 0) {
            return true;
        }
        
        $account = new account($this->db, $this->requestFrom);
        $account->setLastActive();
        $free_messages_count = $account->getFreeMessagesCount();
        $level_messages_count = $account->getLevelMessagesCount();

        if ($account->getGender() == 1) {
            $free_messages_count = 1;
        } else if (($free_messages_count == 0 || $this->getMessagesFromUser($this->requestFrom, $toUserId) > 0) && ($account->getLevel() == 0 || $level_messages_count == 0)) {
            $result = array(
                "error" => true,
                "error_code" => 402
            );
            return $result;
        }

        if (strlen($imgUrl) == 0 && strlen($message) == 0 && strlen($stickerImgUrl) == 0) {

            return $result;
        }

        if (strlen($stickerImgUrl) > 0) {

            $imgUrl = $stickerImgUrl;
        }

        if (strlen($imgUrl) != 0 && strpos($imgUrl, APP_HOST) === false) {
            return $result;
        }

        if ($this->checkSpam($message, $this->SPAM_LIST_ARRAY)) {
            return $result;
        }

        if ($chatId == 0) {
            $chatId = $this->getChatId($this->getRequestFrom(), $toUserId);
            if ($chatId == 0) {
                $chatId = $this->createChat($this->getRequestFrom(), $toUserId);
                if ($chatId == 0) {
                    $result = array(
                        "error" => true,
                        "error_code" => ERROR_OTP_VERIFICATION,
                        "chatId" => 0
                    );
                    return $result;
                }
            }
        }


        $currentTime = time();
        $ip_addr = helper::ip_addr();

        $stmt = $this->db->prepare("INSERT INTO messages (chatId, fromUserId, toUserId, message, imgUrl, stickerId, stickerImgUrl, createAt, ip_addr) value (:chatId, :fromUserId, :toUserId, :message, :imgUrl, :stickerId, :stickerImgUrl, :createAt, :ip_addr)");
        $stmt->bindParam(":chatId", $chatId);
        $stmt->bindParam(":fromUserId", $this->requestFrom);
        $stmt->bindParam(":toUserId", $toUserId);
        $stmt->bindParam(":message", $message);
        $stmt->bindParam(":imgUrl", $imgUrl);
        $stmt->bindParam(":stickerId", $stickerId);
        $stmt->bindParam(":stickerImgUrl", $stickerImgUrl);
        $stmt->bindParam(":createAt", $currentTime);
        $stmt->bindParam(":ip_addr", $ip_addr);


        if ($stmt->execute()) {
            if ($free_messages_count == 0) {
                $account->setLevelMessagesCount($level_messages_count - 1);
            }
            $lastMessageId = $this->db->lastInsertId();
        
            $result = array("error" => false,
                            "error_code" => ERROR_SUCCESS,
                            "chatId" => $chatId,
                            "lastMessageId" => $lastMessageId,
                            "listId" => $listId,
                            "message" => array());

            $time = new language($this->db, $this->language);


            $profile = new profile($this->db, $this->requestFrom);
            $profileInfo = $profile->getVeryShort();
            unset($profile);

            $msgInfo = array("error" => false,
                            "error_code" => ERROR_SUCCESS,
                            "id" => $lastMessageId,
                            "fromUserId" => $this->requestFrom,
                            "fromUserState" => $profileInfo['state'],
                            "fromUserOnline" => $profileInfo['online'],
                            "fromUserUsername" => $profileInfo['username'],
                            "fromUserFullname" => $profileInfo['fullname'],
                            "fromUserPhotoUrl" => $profileInfo['bigPhotoUrl'],
                            "message" => $message,
                            "imgUrl" => $imgUrl,
                            "stickerId" => $stickerId,
                            "stickerImgUrl" => $stickerImgUrl,
                            "createAt" => $currentTime,
                            "seenAt" => 0,
                            "date" => date("Y-m-d H:i:s", $currentTime),
                            "timeAgo" => $time->timeAgo($currentTime),
                            "removeAt" => 0);

            $result['message'] = $msgInfo;

            $fcm = new fcm($this->db);
            $fcm->setRequestFrom($this->getRequestFrom());
            $fcm->setRequestTo($toUserId);
            $fcm->setType(GCM_NOTIFY_MESSAGE);
            $fcm->setTitle("You have new message");
            $fcm->setItemId($chatId);
            $fcm->setMessage($msgInfo);
            $fcm->prepare();
            $fcm->send();
            unset($fcm);
        }

        return $result;
    }

    private function checkSpam($str, array $arr) {
        foreach($arr as $a) {
            if (stripos($str, $a) !== false) return true;
        }
        return false;
    }



    public function removeChat($chatId) {
        $result = array("error" => true, "error_code" => ERROR_CODE_INITIATE);
        $currentTime = time();
        $stmt = $this->db->prepare("UPDATE messages SET removeAt = (:removeAt) WHERE chatId = (:chatId)");
        $stmt->bindParam(":chatId", $chatId, PDO::PARAM_INT);
        $stmt->bindParam(":removeAt", $currentTime, PDO::PARAM_INT);

        if ($stmt->execute()) {
            $result = array("error" => false, "error_code" => ERROR_SUCCESS);
        }
        return $result;
    }


    public function remove($itemId) {
        $result = array("error" => true, "error_code" => ERROR_CODE_INITIATE);

        $currentTime = time();

        $stmt = $this->db->prepare("UPDATE messages SET removeAt = (:removeAt) WHERE id = (:itemId)");
        $stmt->bindParam(":itemId", $itemId, PDO::PARAM_INT);
        $stmt->bindParam(":removeAt", $currentTime, PDO::PARAM_INT);

        if ($stmt->execute()) {
            $result = array("error" => false, "error_code" => ERROR_SUCCESS);
        }
        return $result;
    }

    public function getNewMessagesInChat($chatId, $fromUserId) {
        $stmt = $this->db->prepare("SELECT count(id) FROM messages WHERE chatId = (:chatId) AND fromUserId <> (:fromUserId) AND seenAt = 0 AND removeAt = 0");
        $stmt->bindParam(':fromUserId', $fromUserId, PDO::PARAM_INT);
        $stmt->bindParam(':chatId', $chatId, PDO::PARAM_INT);
        if ($stmt->execute()) {
            return $number_of_rows = $stmt->fetchColumn();
        }
        return 0;
    }

    public function chatInfo($chatId)
    {
        $result = array("error" => true,
                        "error_code" => ERROR_CODE_INITIATE);

        $stmt = $this->db->prepare("SELECT * FROM chats WHERE id = (:chatId) LIMIT 1");
        $stmt->bindParam(":chatId", $chatId, PDO::PARAM_INT);

        if ($stmt->execute()) {

            if ($stmt->rowCount() > 0) {

                $row = $stmt->fetch();

                $time = new language($this->db, $this->language);

                $profileId = $row['fromUserId'];

                if ($profileId == $this->getRequestFrom()) {

                    $profileId = $row['toUserId'];
                }

                $newMessagesCount = 0;

                $profile = new profile($this->db, $profileId);
                $profileInfo = $profile->getVeryShort();
                unset($profile);

                $result = array("error" => false,
                                "error_code" => ERROR_SUCCESS,
                                "id" => $row['id'],
                                "fromUserId" => $row['fromUserId'],
                                "toUserId" => $row['toUserId'],
                                "fromUserId_lastView" => $row['fromUserId_lastView'],
                                "toUserId_lastView" => $row['toUserId_lastView'],
                                "withUserId" => $profileInfo['id'],
                                "withUserState" => $profileInfo['state'],
                                "withUserUsername" => $profileInfo['username'],
                                "withUserFullname" => $profileInfo['fullname'],
                                "withUserPhotoUrl" => $profileInfo['bigPhotoUrl'],
                                "lastMessage" => $row['message'],
                                "lastMessageAgo" => $time->timeAgo($row['messageCreateAt']),
                                "lastMessageCreateAt" => $row['messageCreateAt'],
                                "newMessagesCount" => $newMessagesCount,
                                "createAt" => $row['createAt'],
                                "date" => date("Y-m-d H:i:s", $row['createAt']),
                                "timeAgo" => $time->timeAgo($row['createAt']),
                                "removeAt" => $row['removeAt']);

                unset($profileInfo);
            }
        }

        return $result;
    }
    

    public function getChatsList($lastMessageId = 0) {
        if($lastMessageId == 0) {
            $lastMessageId = 4294967295;
        }
        $chats = array("error" => false,
                       "error_code" => ERROR_SUCCESS,
                       "lastMessageId" => $lastMessageId,
                       "chats" => array());

        $stmt = $this->db->prepare("SELECT m.* FROM messages m 
        JOIN (SELECT chatId, MAX(id) AS maxId FROM messages WHERE (fromUserId = :userId OR toUserId = :userId) 
        AND id < :lastMessageId AND removeAt = 0 GROUP BY chatId) t 
        ON m.chatId = t.chatId AND m.id = t.maxId 
        ORDER BY m.id DESC 
        LIMIT 20");
        $stmt->bindParam(':lastMessageId', $lastMessageId, PDO::PARAM_INT);
        $stmt->bindParam(':userId', $this->requestFrom, PDO::PARAM_INT);

        if ($stmt->execute()) {
            while ($row = $stmt->fetch()) {
                $time = new language($this->db, $this->language);
                
                $profileId = $row['fromUserId'];
                if ($profileId == $this->getRequestFrom()) {
                    $profileId = $row['toUserId'];
                }

                $profile = new profile($this->db, $profileId);
                $profile->setRequestFrom($this->requestFrom);
                $profileInfo = $profile->getVeryShort();
                unset($profile);

                $newMessagesCount = 0;
                if (APP_MESSAGES_COUNTERS) {
                    $newMessagesCount = $this->getNewMessagesInChat($row['chatId'], $this->getRequestFrom());
                }

                $chatInfo = array("error" => false,
                                  "error_code" => ERROR_SUCCESS,
                                  "id" => $row['chatId'],
                                  "chatId" => $row['chatId'],
                                  "fromUserId" => $row['fromUserId'],
                                  "toUserId" => $row['toUserId'],
                                  "withUserId" => $profileInfo['id'],
                                  "withUserState" => $profileInfo['state'],
                                  "withUserOnline" => $profileInfo['online'],
                                  "withUserUsername" => $profileInfo['username'],
                                  "withUserFullname" => $profileInfo['fullname'],
                                  "withUserPhotoUrl" => $profileInfo['bigPhotoUrl'],
                                  "lastMessage" => $row['message'],
                                  "lastMessageAgo" => $time->timeAgo($row['createAt']),
                                  "lastMessageCreateAt" => $row['createAt'],
                                  "newMessagesCount" => $newMessagesCount,
                                  "createAt" => $row['createAt'],
                                  "date" => date("Y-m-d H:i:s", $row['createAt']),
                                  "timeAgo" => $time->timeAgo($row['createAt']),
                                  "removeAt" => $row['removeAt']);

                unset($profileInfo);
                array_push($chats['chats'], $chatInfo);
                $chats['lastMessageId'] = $row['id'];
                unset($chatInfo);
            }
        }

        return $chats;
    }

    public function getNewMessagesCount() {
        $stmt = $this->db->prepare("SELECT count(*) FROM messages WHERE fromUserId <> (:fromUserId) AND seenAt = 0 AND removeAt = 0");
        $stmt->bindParam(':fromUserId', $this->requestFrom, PDO::PARAM_INT);
        if ($stmt->execute()) {
            return $number_of_rows = $stmt->fetchColumn();
        }
        return 0;
    }

    public function getPreviousMessages($chatId, $lastMessageId = 0)
    {
        if($lastMessageId == 0) {
            $lastMessageId = 4294967295;
        }
        $messages = array("error" => false,
                          "error_code" => ERROR_SUCCESS,
                          "chatId" => $chatId,
                          "lastMessageId" => $lastMessageId,
                          "messages" => array());

        $stmt = $this->db->prepare("SELECT * FROM messages WHERE chatId = (:chatId) AND id < (:lastMessageId) AND removeAt = 0 ORDER BY id DESC LIMIT 20");
        $stmt->bindParam(':chatId', $chatId, PDO::PARAM_INT);
        $stmt->bindParam(':lastMessageId', $lastMessageId, PDO::PARAM_INT);

        if ($stmt->execute()) {

            while ($row = $stmt->fetch()) {

                $time = new language($this->db, $this->language);

                $profile = new profile($this->db, $row['fromUserId']);
                $profileInfo = $profile->getVeryShort();
                unset($profile);

                $msgInfo = array("error" => false,
                                 "error_code" => ERROR_SUCCESS,
                                 "id" => $row['id'],
                                 "fromUserId" => $row['fromUserId'],
                                 "fromUserState" => $profileInfo['state'],     //$profileInfo['state'],
                                 "fromUserUsername" => $profileInfo['username'], //$profileInfo['username']
                                 "fromUserFullname" => $profileInfo['fullname'], //$profileInfo['fullname']
                                 "fromUserOnline" => $profileInfo['online'], //$profileInfo['fullname']
                                 "fromUserPhotoUrl" => $profileInfo['bigPhotoUrl'], //$profileInfo['bigPhotoUrl']
                                 "message" => $row['message'],
                                 "imgUrl" => $row['imgUrl'],
                                 "stickerId" => $row['stickerId'],
                                 "stickerImgUrl" => $row['stickerImgUrl'],
                                 "createAt" => $row['createAt'],
                                 "seenAt" => $row['seenAt'],
                                 "date" => date("Y-m-d H:i:s", $row['createAt']),
                                 "timeAgo" => $time->timeAgo($row['createAt']),
                                 "removeAt" => $row['removeAt']);

                array_push($messages['messages'], $msgInfo);
                $messages['lastMessageId'] = $msgInfo['id'];
                unset($msgInfo);
            }
        }

        return $messages;
    }

    public function getNextMessages($chatId, $lastMessageId = 0)
    {
        
        if($lastMessageId == 0) {
            $lastMessageId = 4294967295;
        }
        $messages = array("error" => false,
                          "error_code" => ERROR_SUCCESS,
                          "chatId" => $chatId,
                          "lastMessageId" => $lastMessageId,
                          "messages" => array());

        $stmt = $this->db->prepare("SELECT * FROM messages WHERE chatId = (:chatId) AND id > (:lastMessageId) AND removeAt = 0 ORDER BY id ASC");
        $stmt->bindParam(':chatId', $chatId, PDO::PARAM_INT);
        $stmt->bindParam(':lastMessageId', $lastMessageId, PDO::PARAM_INT);

        if ($stmt->execute()) {

            while ($row = $stmt->fetch()) {

                $time = new language($this->db, $this->language);

                $msgInfo = array("error" => false,
                                "error_code" => ERROR_SUCCESS,
                                "id" => $row['id'],
                                "fromUserId" => $row['fromUserId'],
                                "fromUserState" => 0,     //$profileInfo['state'],
                                "fromUserUsername" => "", //$profileInfo['username']
                                "fromUserFullname" => "", //$profileInfo['fullname']
                                "fromUserPhotoUrl" => "", //$profileInfo['bigPhotoUrl']
                                "fromUserOnline" => "",
                                "message" => $row['message'],
                                "imgUrl" => $row['imgUrl'],
                                "stickerId" => $row['stickerId'],
                                "stickerImgUrl" => $row['stickerImgUrl'],
                                "createAt" => $row['createAt'],
                                "seenAt" => $row['seenAt'],
                                "date" => date("Y-m-d H:i:s", $row['createAt']),
                                "timeAgo" => $time->timeAgo($row['createAt']),
                                "removeAt" => $row['removeAt']);

                array_push($messages['messages'], $msgInfo);

                $messages['lastMessageId'] = $msgInfo['id'];

                unset($msgInfo);
            }
        }

        return $messages;
    }

    public function setSeen($toUserId, $fromUser) {

        $result = array(
            "error" => true,
            "error_code" => ERROR_CODE_INITIATE
        );

        $currentTime = time();
        $stmt = $this->db->prepare("UPDATE messages SET seenAt = (:seenAt) WHERE toUserId = (:toUserId) AND fromUserId = (:fromUserId) AND removeAt = 0 AND seenAt = 0");
        $stmt->bindParam(":seenAt", $currentTime, PDO::PARAM_INT);
        $stmt->bindParam(":toUserId", $toUserId, PDO::PARAM_INT);
        $stmt->bindParam(":fromUserId", $fromUser, PDO::PARAM_INT);
        if ($stmt->execute()) {

            $result = array(
                "error" => false,
                "error_code" => ERROR_SUCCESS
            );
        }

        return $result;
    }

    public function get($chatId, $lastMessageId = 0, $chatFromUserId, $chatToUserId)
    {
        if ($lastMessageId == 0) {
            $lastMessageId = 4294967295;
        }

        if ($chatFromUserId == 0 || $chatToUserId == 0) {

            $chatInfo = $this->chatInfo($chatId);

            $chatFromUserId = $chatInfo['fromUserId'];
            $chatToUserId = $chatInfo['toUserId'];
        }

        $messages = array("error" => false,
                          "error_code" => ERROR_SUCCESS,
                          "chatId" => $chatId,
                          "messagesCount" => $this->messagesCountByChat($chatId),
                          "lastMessageId" => $lastMessageId,
                          "chatFromUserId" => $chatFromUserId,
                          "chatToUserId" => $chatToUserId,
                          "newMessagesCount" => 0,
                          "messages" => array());

        $stmt = $this->db->prepare("SELECT *
        FROM messages
        WHERE (fromUserId = :fromUserId AND toUserId = :toUserId)
               OR (fromUserId = :toUserId AND toUserId = :fromUserId)
              AND id < :lastMessageId
              AND removeAt = 0
        ORDER BY id DESC
        LIMIT 20;");
        $stmt->bindParam(":fromUserId", $chatFromUserId, PDO::PARAM_INT);
        $stmt->bindParam(":toUserId", $chatToUserId, PDO::PARAM_INT);
        $stmt->bindParam(':lastMessageId', $lastMessageId, PDO::PARAM_INT);

        if ($stmt->execute()) {

            $profile_from = new profile($this->db, $chatFromUserId);
            $profileInfo_from = $profile_from->getVeryShort();
            unset($profile_from);

            $profile_to = new profile($this->db, $chatToUserId);
            $profileInfo_to = $profile_to->getVeryShort();
            unset($profile_to);

            while ($row = $stmt->fetch()) {

                $time = new language($this->db, $this->language);

                $profileInfo = array();

                if ($row['fromUserId'] == $profileInfo_to['id']) {

                    $profileInfo = $profileInfo_to;

                }

                if ($row['fromUserId'] == $profileInfo_from['id']) {

                    $profileInfo = $profileInfo_from;

                }

//                $profile = new profile($this->db, $row['fromUserId']);
//                $profileInfo = $profile->getVeryShort();
//                unset($profile);

                $msgInfo = array("error" => false,
                                 "error_code" => ERROR_SUCCESS,
                                 "id" => $row['id'],
                                 "fromUserId" => $profileInfo['id'],
                                 "fromUserState" => $profileInfo['state'],
                                 "fromUserUsername" => $profileInfo['username'],
                                 "fromUserFullname" => $profileInfo['fullname'],
                                 "fromUserPhotoUrl" => $profileInfo['bigPhotoUrl'],
                                 "message" => $row['message'],
                                 "imgUrl" => $row['imgUrl'],
                                 "stickerId" => $row['stickerId'],
                                 "stickerImgUrl" => $row['stickerImgUrl'],
                                 "seenAt" => $row['seenAt'],
                                 "createAt" => $row['createAt'],
                                 "date" => date("Y-m-d H:i:s", $row['createAt']),
                                 "timeAgo" => $time->timeAgo($row['createAt']),
                                 "removeAt" => $row['removeAt']);

                array_push($messages['messages'], $msgInfo);

                $messages['lastMessageId'] = $msgInfo['id'];

                unset($msgInfo);
                unset($profileInfo);
            }
        }

        return $messages;
    }

    public function info($row)
    {
        $time = new language($this->db, $this->language);

        $profile = new profile($this->db, $row['fromUserId']);
        $profileInfoFrom = $profile->getVeryShort();
        unset($profile);
        $profile = new profile($this->db, $row['toUserId']);
        $profileInfoTo = $profile->getVeryShort();
        unset($profile);

        $result = array("error" => false,
                        "error_code" => ERROR_SUCCESS,
                        "id" => $row['id'],
                        "chatId" => $row['chatId'],
                        "fromUserId" => $row['fromUserId'],
                        "fromUserState" => $profileInfoFrom['state'],
                        "fromUserAL" => $profileInfoFrom['access_level'],
                        "fromUserUsername" => $profileInfoFrom['username'],
                        "fromUserFullname" => $profileInfoFrom['fullname'],
                        "fromUserOnline" => $profileInfoFrom['online'],
                        "fromUserPhotoUrl" => $profileInfoFrom['bigPhotoUrl'],
                        "toUserId" => $row['toUserId'],
                        "toUserState" => $profileInfoTo['state'],
                        "toUserAL" => $profileInfoTo['access_level'],
                        "toUserUsername" => $profileInfoTo['username'],
                        "toUserFullname" => $profileInfoTo['fullname'],
                        "toUserOnline" => $profileInfoTo['online'],
                        "toUserPhotoUrl" => $profileInfoTo['bigPhotoUrl'],
                        "message" => $row['message'],
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


    public function getFull($chatId) {
        $messages = array("error" => false,
                          "error_code" => ERROR_SUCCESS,
                          "chatId" => $chatId,
                          "messagesCount" => $this->messagesCountByChat($chatId),
                          "messages" => array());

        $stmt = $this->db->prepare("SELECT * FROM messages WHERE chatId = (:chatId) AND removeAt = 0 ORDER BY id DESC");
        $stmt->bindParam(':chatId', $chatId, PDO::PARAM_INT);

        if ($stmt->execute()) {
            while ($row = $stmt->fetch()) {
                $msgInfo = $this->info($row);
                array_push($messages['messages'], $msgInfo);
                unset($msgInfo);
            }
        }
        return $messages;
    }

    public function getChatsStream($lastMessageId = 0) {
        if ($lastMessageId == 0) {
            $lastMessageId = 4294967295;
        }

        $result = array("error" => false,
                        "error_code" => ERROR_SUCCESS,
                        "lastMessageId" => $lastMessageId,
                        "messages" => array());

        $stmt = $this->db->prepare("SELECT m.* FROM messages m 
        JOIN (SELECT chatId, MAX(id) AS maxId FROM messages WHERE id < :lastMessageId AND removeAt = 0 GROUP BY chatId) t 
        ON m.chatId = t.chatId AND m.id = t.maxId 
        ORDER BY m.id DESC 
        LIMIT 20;");
        $stmt->bindParam(':lastMessageId', $lastMessageId, PDO::PARAM_INT);
        if ($stmt->execute()) {
            if ($stmt->rowCount() > 0) {
                while ($row = $stmt->fetch()) {
                    $msgInfo = $this->info($row);
                    array_push($result['messages'], $msgInfo);
                    $result['lastMessageId'] = $row['id'];
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
