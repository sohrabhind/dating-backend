<?php

/*!
 * ifsoft.co.uk
 *
 * http://ifsoft.com.ua, https://ifsoft.co.uk, https://hindbyte.com
 * hindbyte@gmail.com
 *
 * Copyright 2012-2020 Demyanchuk Dmitry (hindbyte@gmail.com)
 */

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
        $stmt = $this->db->prepare("SELECT count(*) FROM photos WHERE removeAt = 0");
        $stmt->execute();

        return $number_of_rows = $stmt->fetchColumn();
    }

    public function getCount($itemType)
    {
        $stmt = $this->db->prepare("SELECT count(*) FROM photos WHERE removeAt = 0 AND itemType = (:itemType) AND itemShowInStream <> 0");
        $stmt->bindParam(':itemType', $itemType, PDO::PARAM_INT);
        $stmt->execute();

        return $number_of_rows = $stmt->fetchColumn();
    }

    private function getMaxId()
    {
        $stmt = $this->db->prepare("SELECT MAX(id) FROM photos");
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

            $stmt = $this->db->prepare("SELECT id FROM photos WHERE removeAt = 0 AND id < (:itemId) ORDER BY id DESC LIMIT 20");
            $stmt->bindParam(':itemId', $itemId, PDO::PARAM_INT);

        } else {

            if ($itemType != -1) {

                $stmt = $this->db->prepare("SELECT id FROM photos WHERE removeAt = 0 AND itemType = (:itemType) AND itemShowInStream <> 0 AND id < (:itemId) ORDER BY id DESC LIMIT 20");
                $stmt->bindParam(':itemType', $itemType, PDO::PARAM_INT);
                $stmt->bindParam(':itemId', $itemId, PDO::PARAM_INT);

            } else {

                $stmt = $this->db->prepare("SELECT id FROM photos WHERE removeAt = 0 AND itemShowInStream <> 0 AND id < (:itemId) ORDER BY id DESC LIMIT 20");
                $stmt->bindParam(':itemId', $itemId, PDO::PARAM_INT);
            }
        }

        if ($stmt->execute()) {

            if ($stmt->rowCount() > 0) {

                while ($row = $stmt->fetch()) {

                    $photo = new photos($this->db);
                    $photo->setRequestFrom($this->requestFrom);
                    $photoInfo = $photo->info($row['id']);
                    unset($post);

                    array_push($result['items'], $photoInfo);

                    $result['itemId'] = $photoInfo['id'];

                    unset($photoInfo);
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

