<?php



class app extends db_connect
{
    public function __construct($dbo = NULL)
    {
        parent::__construct($dbo);

    }

    public function getPreviewProfiles()
    {
        $result = array("error" => false,
                        "error_code" => ERROR_SUCCESS,
                        "items" => array());
        $stmt = $this->db->prepare("SELECT * FROM users WHERE state = 0 AND gender = 1 ORDER BY RAND() DESC LIMIT 6");
        if ($stmt->execute()) {
            if ($stmt->rowCount() > 2) {
                while ($row = $stmt->fetch()) {
                    $bigPhotoUrl = "";
                    if ($row['bigPhotoUrl'] != '') {
                        $bigPhotoUrl = APP_URL . "/" . PROFILE_PHOTO_PATH . $row['bigPhotoUrl'];
                    }
                    array_push($result['items'], array(
                            "id" => $row['id'],
                            "username" => $row['username'],
                            "fullname" => $row['fullname'],
                            "photoUrl" => $bigPhotoUrl
                        )
                    );
                }

            } 
        }
        return $result;
    }
}
