<?php



class cleaner extends db_connect
{

	private $requestFrom = 0;

	public function __construct($dbo = NULL)
    {
		parent::__construct($dbo);
	}

    public function cleanPhotos()
    {
        $result = array("error" => true, "error_code" => ERROR_CODE_INITIATE);
        $stmt = $this->db->prepare("UPDATE users SET bigPhotoUrl = ''");

        if ($stmt->execute()) {

            $result = array("error" => false,
                            "error_code" => ERROR_SUCCESS);
        }

        return $result;
    }

    public function cleanMessages()
    {
        $result = array("error" => true,
                        "error_code" => ERROR_CODE_INITIATE);

        $stmt = $this->db->prepare("UPDATE messages SET removeAt = 1 WHERE imgUrl > ''");

        if ($stmt->execute()) {

            $result = array("error" => false,
                            "error_code" => ERROR_SUCCESS);
        }

        return $result;
    }

    public function cleanGallery()
    {
        $result = array("error" => true,
                        "error_code" => ERROR_CODE_INITIATE);

        $stmt = $this->db->prepare("UPDATE users SET images_count = 0 WHERE images_count > 0");

        if ($stmt->execute()) {

            $stmt2 = $this->db->prepare("UPDATE images SET removeAt = 1 WHERE removeAt = 0");

            if ($stmt2->execute()) {

                $stmt5 = $this->db->prepare("UPDATE notifications SET removeAt = 1 WHERE notifyType > 6 AND notifyType < 10");
                $stmt5->execute();

                $result = array("error" => false,
                                "error_code" => ERROR_SUCCESS);
            }
        }

        return $result;
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

