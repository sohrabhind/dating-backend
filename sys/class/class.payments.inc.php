<?php



class payments extends db_connect
{

	private $requestFrom = 0;
    private $language = 'en';

	public function __construct($dbo = NULL)
    {
		parent::__construct($dbo);
	}

    public function count()
    {
        $stmt = $this->db->prepare("SELECT count(*) FROM payments");
        $stmt->execute();

        return $number_of_rows = $stmt->fetchColumn();
    }
    

    public function quickInfo($row, $details = false)
    {
        $time = new language($this->db, $this->language);

        $result = array(
            "error" => false,
            "error_code" => ERROR_SUCCESS,
            "id" => $row['id'],
            "user_id" => $row['user_id'],
            "added" => $row['added'],
            "level" => $row['level'],
            "amount" => $row['amount'],
            "currency" => $row['currency'],
            "date" => date("Y-m-d H:i:s", $row['createAt']),
            "createAt" => $row['createAt'],
            "timeAgo" => $time->timeAgo($row['createAt'])
        );

        if ($details) {

            $profile = new profile($this->db, $row['user_id']);
            $profileInfo = $profile->getVeryShort();
            unset($profile);

            $result['owner'] = $profileInfo;
        }

        return $result;
    }

    public function get($itemId, $limit = 20)
    {
        if ($itemId == 0) {

            $itemId = 4294967295;
        }

        $result = array(
            "error" => false,
            "error_code" => ERROR_SUCCESS,
            "itemId" => $itemId,
            "items" => array()
        );

        $stmt = $this->db->prepare("SELECT * FROM payments WHERE user_id = (:user_id) AND id < (:itemId) AND added = 1  ORDER BY id DESC LIMIT :limit");
        $stmt->bindParam(':user_id', $this->requestFrom);
        $stmt->bindParam(':itemId', $itemId);
        $stmt->bindParam(':limit', $limit);

        if ($stmt->execute()) {

            while ($row = $stmt->fetch()) {

                $itemInfo = $this->quickInfo($row);

                array_push($result['items'], $itemInfo);

                $result['itemId'] = $itemInfo['id'];

                unset($itemInfo);
            }
        }

        return $result;
    }

    public function stream($itemId, $limit = 20)
    {
        $result = array(
            "error" => false,
            "error_code" => ERROR_SUCCESS,
            "itemId" => $itemId,
            "items" => array()
        );

        if ($itemId == 0) {

            $itemId = 4294967295;
        }

        $stmt = $this->db->prepare("SELECT * FROM payments WHERE id < (:itemId) AND added = 1  ORDER BY id DESC LIMIT :limit");
        $stmt->bindParam(':itemId', $itemId);
        $stmt->bindParam(':limit', $limit);

        if ($stmt->execute()) {

            while ($row = $stmt->fetch()) {

                $itemInfo = $this->quickInfo($row, true);

                array_push($result['items'], $itemInfo);

                $result['itemId'] = $itemInfo['id'];

                unset($itemInfo);
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
