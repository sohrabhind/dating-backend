<?php



class notify extends db_connect
{
    private $requestFrom = 0;
    private $language = 'en';

    public function __construct($dbo = NULL)
    {

        parent::__construct($dbo);
    }

    private function getMaxId()
    {
        $stmt = $this->db->prepare("SELECT MAX(id) FROM notifications");
        $stmt->execute();

        return $number_of_rows = $stmt->fetchColumn();
    }

    public function getAll($notifyId = 0)
    {

        if ($notifyId == 0) {

            $notifyId = $this->getMaxId();
            $notifyId++;
        }

        $notifications = array("error" => false,
                               "error_code" => ERROR_SUCCESS,
                               "notifyId" => $notifyId,
                               "notifications" => array());

        $stmt = $this->db->prepare("SELECT * FROM notifications WHERE notifyToId = (:notifyToId) AND id < (:notifyId) ORDER BY id DESC LIMIT 20");
        $stmt->bindParam(':notifyToId', $this->requestFrom);
        $stmt->bindParam(':notifyId', $notifyId);

        if ($stmt->execute()) {
            if ($stmt->rowCount() > 0) {
                while ($row = $stmt->fetch()) {
                    $time = new language($this->db, $this->language);
                    if ($row['notifyFromId'] == 0) {
                        $profileInfo = array("id" => 0,
                                             "state" => 0,
                                             "username" => "",
                                             "fullname" => "",
                                             "online" => false,
                                             "verify" => 0,
                                             "bigPhotoUrl" => "/assets/icons/profile_default_photo.png");

                    } else {
                        $profile = new profile($this->db, $row['notifyFromId']);
                        $profileInfo = $profile->getVeryShort();
                        unset($profile);
                    }

                    $bigPhotoUrl = "";
                    if ($profileInfo['bigPhotoUrl'] != '') {
                        $bigPhotoUrl = APP_URL . "/" . PROFILE_PHOTO_PATH . basename($profileInfo['bigPhotoUrl']);
                    }

                    $data = array("id" => $row['id'],
                                  "type" => $row['notifyType'],
                                  "itemId" => $row['itemId'],
                                  "fromUserId" => $profileInfo['id'],
                                  "fromUserState" => $profileInfo['state'],
                                  "fromUserUsername" => $profileInfo['username'],
                                  "fromUserFullname" => $profileInfo['fullname'],
                                  "fromUserPhotoUrl" => $bigPhotoUrl,
                                  "fromUserOnline" => $profileInfo['online'],
                                  "createAt" => $row['createAt'],
                                  "timeAgo" => $time->timeAgo($row['createAt']));

                    array_push($notifications['notifications'], $data);

                    $notifications['notifyId'] = $row['id'];

                    unset($data);
                }
            }
        }

        return $notifications;
    }

    public function createNotify($notifyToId, $notifyFromId, $notifyType, $itemId = 0)
    {
        $createAt = time();

        $stmt = $this->db->prepare("INSERT INTO notifications (notifyToId, notifyFromId, notifyType, itemId, createAt) value (:notifyToId, :notifyFromId, :notifyType, :itemId, :createAt)");
        $stmt->bindParam(":notifyToId", $notifyToId);
        $stmt->bindParam(":notifyFromId", $notifyFromId);
        $stmt->bindParam(":notifyType", $notifyType);
        $stmt->bindParam(":itemId", $itemId);
        $stmt->bindParam(":createAt", $createAt);
        $stmt->execute();

        return $this->db->lastInsertId();
    }

    public function remove($notifyId)
    {
        $stmt = $this->db->prepare("DELETE FROM notifications WHERE id = (:notifyId)");
        $stmt->bindParam(":notifyId", $notifyId);
        $stmt->execute();
    }

    public function delete($notifyId)
    {
        $stmt = $this->db->prepare("DELETE FROM notifications WHERE id = (:notifyId) AND notifyToId = (:notifyToId)");
        $stmt->bindParam(":notifyId", $notifyId);
        $stmt->bindParam(":notifyToId", $this->requestFrom);
        $stmt->execute();
    }

    public function removeNotify($notifyToId, $notifyFromId, $notifyType, $itemId = 0)
    {
        $stmt = $this->db->prepare("DELETE FROM notifications WHERE notifyToId = (:notifyToId) AND notifyFromId = (:notifyFromId) AND notifyType = (:notifyType) AND itemId = (:itemId)");
        $stmt->bindParam(":notifyToId", $notifyToId);
        $stmt->bindParam(":notifyFromId", $notifyFromId);
        $stmt->bindParam(":notifyType", $notifyType);
        $stmt->bindParam(":itemId", $itemId);
        $stmt->execute();
    }

    public function getAllCount()
    {
        $stmt = $this->db->prepare("SELECT count(*) FROM notifications WHERE notifyToId = (:notifyToId)");
        $stmt->bindParam(":notifyToId", $this->requestFrom);
        $stmt->execute();

        return $number_of_rows = $stmt->fetchColumn();
    }

    public function getNewCount($LastNotify)
    {
        $stmt = $this->db->prepare("SELECT count(*) FROM notifications WHERE notifyToId = (:notifyToId) AND createAt > (:LastNotify) AND removeAt = 0");
        $stmt->bindParam(":notifyToId", $this->requestFrom);
        $stmt->bindParam(":LastNotify", $LastNotify);
        $stmt->execute();

        return $number_of_rows = $stmt->fetchColumn();
    }

    public function clear()
    {
        $result = array("error" => false,
                        "error_code" => ERROR_SUCCESS);

        $stmt = $this->db->prepare("DELETE FROM notifications WHERE notifyToId = (:notifyToId)");
        $stmt->bindParam(":notifyToId", $this->requestFrom);
        $stmt->execute();

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
