<?php



class stream extends db_connect
{
    private $requestFrom = 0;
    private $requestFromAdmin = false;

    public function __construct($dbo = NULL)
    {
        parent::__construct($dbo);
    }

    public function getAllCount()
    {
        $stmt = $this->db->prepare("SELECT count(*) FROM images WHERE removeAt = 0");
        $stmt->execute();

        return $number_of_rows = $stmt->fetchColumn();
    }

    public function getCount($itemType)
    {
        $stmt = $this->db->prepare("SELECT count(*) FROM images WHERE removeAt = 0 AND itemType = (:itemType)");
        $stmt->bindParam(':itemType', $itemType);
        $stmt->execute();

        return $number_of_rows = $stmt->fetchColumn();
    }

    private function getMaxId()
    {
        $stmt = $this->db->prepare("SELECT MAX(id) FROM images");
        $stmt->execute();

        return $number_of_rows = $stmt->fetchColumn();
    }

    public function get($itemId = 0, $itemType = -1, $language = 'en')
    {
        if ($itemId == 0) {

            $itemId = $this->getMaxId();
            $itemId++;
        }

        $result = array(
            "error" => false,
            "error_code" => ERROR_SUCCESS,
            "itemId" => $itemId,
            "items" => array()
        );

        if ($this->getRequestFromAdmin()) {

            $stmt = $this->db->prepare("SELECT id FROM images WHERE removeAt = 0 AND id < (:itemId) ORDER BY id DESC LIMIT 20");
            $stmt->bindParam(':itemId', $itemId);

        } else {

            if ($itemType != -1) {

                $stmt = $this->db->prepare("SELECT id FROM images WHERE removeAt = 0 AND itemType = (:itemType) AND  id < (:itemId) ORDER BY id DESC LIMIT 20");
                $stmt->bindParam(':itemType', $itemType);
                $stmt->bindParam(':itemId', $itemId);

            } else {

                $stmt = $this->db->prepare("SELECT id FROM images WHERE removeAt = 0 AND id < (:itemId) ORDER BY id DESC LIMIT 20");
                $stmt->bindParam(':itemId', $itemId);
            }
        }

        if ($stmt->execute()) {

            if ($stmt->rowCount() > 0) {

                while ($row = $stmt->fetch()) {

                    $images = new images($this->db);
                    $images->setRequestFrom($this->requestFrom);
                    $imageInfo = $images->info($row['id']);
                    unset($post);

                    array_push($result['items'], $imageInfo);

                    $result['itemId'] = $imageInfo['id'];

                    unset($imageInfo);
                }
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

    public function setRequestFromAdmin($requestFromAdmin)
    {
        $this->requestFromAdmin = $requestFromAdmin;
    }

    public function getRequestFromAdmin()
    {
        return $this->requestFromAdmin;
    }
}

