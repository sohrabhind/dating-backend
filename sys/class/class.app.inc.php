<?php



class app extends db_connect
{
    public function __construct($dbo = NULL)
    {
        parent::__construct($dbo);

    }

    public function getPreviewProfiles($limit = 6)
    {
        $result = array("error" => false,
                        "error_code" => ERROR_SUCCESS,
                        "items" => array());

        $stmt = $this->db->prepare("SELECT * FROM users WHERE state = 0 ORDER BY regtime DESC LIMIT :limit");
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);

        if ($stmt->execute()) {
            if ($stmt->rowCount() > 2) {
                while ($row = $stmt->fetch()) {
                    array_push($result['items'], array(

                            "id" => $row['id'],
                            "username" => $row['username'],
                            "fullname" => $row['fullname'],
                            "photoUrl" => $row['bigPhotoUrl']
                        )
                    );
                }

            } else {
                for ($i = 1; $i < 7; $i++) {
                    array_push($result['items'], array(
                            "id" => $i,
                            "username" => $i.'username',
                            "fullname" => $i.'fullname',
                            "photoUrl" => '/assets/img/'.$i.'.jpg'
                        )
                    );
                }
            }
        }

        return $result;
    }
}
