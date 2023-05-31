<?php

/*!
 * ifsoft.co.uk
 *
 * http://ifsoft.com.ua, https://ifsoft.co.uk, https://hindbyte.com
 * hindbyte@gmail.com
 *
 * Copyright 2012-2020 Demyanchuk Dmitry (hindbyte@gmail.com)
 */

if (!defined("APP_SIGNATURE")) {

    header("Location: /");
    exit;
}

class reports extends db_connect
{
	private $requestFrom = 0;
    private $itemsInRequest = 20;
    private $tableName = "reports";

	public function __construct($dbo = NULL)
    {
		parent::__construct($dbo);
	}

    public function add($itemType, $itemId, $abuseId, $description = "")
    {
        $result = array(
            "error" => true,
            "error_code" => ERROR_CODE_INITIATE
        );

        $itemInfo = array(
            'error' => true
        );

        $toUseId = 0;

        if ($itemType == REPORT_TYPE_GALLERY_ITEM) {

            $items = new gallery($this->db);
            $itemInfo = $items->info($itemId);

            if ($itemInfo['error'] || $itemInfo['removeAt'] != 0) {

                return $result;

            } else {

                $toUseId = $itemInfo['owner']['id'];
            }

        } else if ($itemType == REPORT_TYPE_PROFILE) {

            $profile = new profile($this->db, $itemId);
            $itemInfo = $profile->getVeryShort();

            if ($itemInfo['error'] || $itemInfo['state'] != 0 || $itemInfo['id'] == $this->getRequestFrom()) {

                return $result;

            } else {

                $toUseId = $itemInfo['id'];
            }
        }

        $create_at = time();
        $ip_addr = helper::ip_addr();

        $stmt = $this->db->prepare("INSERT INTO reports (itemType, fromUserId, toUserId, itemId, abuseId, description, createAt, ip_addr) value (:itemType, :fromUserId, :toUserId, :itemId, :abuseId, :description, :createAt, :ip_addr)");
        $stmt->bindParam(":itemType", $itemType, PDO::PARAM_INT);
        $stmt->bindParam(":fromUserId", $this->requestFrom, PDO::PARAM_INT);
        $stmt->bindParam(":toUserId", $toUseId, PDO::PARAM_INT);
        $stmt->bindParam(":itemId", $itemId, PDO::PARAM_INT);
        $stmt->bindParam(":abuseId", $abuseId, PDO::PARAM_INT);
        $stmt->bindParam(":description", $description, PDO::PARAM_STR);
        $stmt->bindParam(":createAt", $create_at, PDO::PARAM_INT);
        $stmt->bindParam(":ip_addr", $ip_addr, PDO::PARAM_STR);

        if ($stmt->execute()) {

            if ($itemType == REPORT_TYPE_GALLERY_ITEM) {

                //$items = new gallery($this->db);
                //$items->setReportsCount($itemInfo['id'], ++$itemInfo['reportsCount']);
            }

            $result = array(
                "error" => false,
                "error_code" => ERROR_SUCCESS
            );
        }

        return $result;
    }

    public function delete($itemId)
    {
        $result = array(
            "error" => true,
            "error_code" => ERROR_CODE_INITIATE
        );

        $remove_at = time();

        $stmt = $this->db->prepare("UPDATE reports SET removeAt = (:removeAt) WHERE id = (:itemId) AND removeAt = 0");
        $stmt->bindParam(":removeAt", $remove_at, PDO::PARAM_INT);
        $stmt->bindParam(":itemId", $itemId, PDO::PARAM_INT);

        if ($stmt->execute()) {

            $result = array(
                "error" => false,
                "error_code" => ERROR_SUCCESS);
        }

        return $result;
    }

    public function remove($itemType, $itemId)
    {
        $result = array(
            "error" => true,
            "error_code" => ERROR_CODE_INITIATE
        );

        $remove_at = time();

        $stmt = $this->db->prepare("UPDATE reports SET removeAt = (:removeAt) WHERE itemType = (:itemType) AND itemId = (:itemId) AND removeAt = 0");
        $stmt->bindParam(":removeAt", $remove_at, PDO::PARAM_INT);
        $stmt->bindParam(":itemType", $itemType, PDO::PARAM_INT);
        $stmt->bindParam(":itemId", $itemId, PDO::PARAM_INT);

        if ($stmt->execute()) {

            $result = array(
                "error" => false,
                "error_code" => ERROR_SUCCESS
            );
        }

        return $result;
    }

    // Clear all reports by type

    public function clear($itemType)
    {
        $result = array(
            "error" => true,
            "error_code" => ERROR_CODE_INITIATE
        );

        $remove_at = time();

        $stmt = $this->db->prepare("UPDATE reports SET removeAt = (:removeAt) WHERE itemType = (:itemType) AND removeAt = 0");
        $stmt->bindParam(":removeAt", $remove_at, PDO::PARAM_INT);
        $stmt->bindParam(":itemType", $itemType, PDO::PARAM_INT);

        if ($stmt->execute()) {

            $result = array(
                "error" => false,
                "error_code" => ERROR_SUCCESS
            );
        }

        return $result;
    }

    // Get items list

    public function getItems($pageId = 0, $itemType = -1, $itemId = -1)
    {
        $itemsCount = 0;

        if ($pageId == 0) $itemsCount = $this->getItemsCount($itemType, $itemId);

        $result = array(
            "error" => false,
            "error_code" => ERROR_SUCCESS,
            "pageId" => $pageId,
            "itemsCount" => $itemsCount,
            "items" => array());

        if ($pageId == 0) {

            $limitSql = " LIMIT 0, {$this->itemsInRequest}";

        } else {

            $offset = $pageId * $this->itemsInRequest;
            $count  = $this->itemsInRequest;

            $limitSql = " LIMIT {$offset}, {$count}";
        }

        $itemIdSql = "";

        if ($itemId != -1) {

            $itemIdSql = " AND itemId = $itemId";
        }

        $itemTypeSql = "";

        if ($itemType != -1) {

            $itemTypeSql = " AND itemType = $itemType";
        }

        $sql = "SELECT * FROM $this->tableName WHERE removeAt = 0".$itemIdSql.$itemTypeSql." ORDER BY id DESC $limitSql";

        $stmt = $this->db->prepare($sql);

        if ($stmt->execute()) {

            if ($stmt->rowCount() > 0) {

                while ($row = $stmt->fetch()) {

                    array_push($result['items'], $this->quickInfo($row));
                }
            }
        }

        return $result;
    }

    // Get items count

    public function getItemsCount($itemType = -1, $itemId = -1)
    {
        $itemIdSql = "";

        if ($itemId != -1) {

            $itemIdSql = " AND itemId = $itemId";
        }

        $itemTypeSql = "";

        if ($itemType != -1) {

            $itemTypeSql = " AND itemType = $itemType";
        }

        $sql = "SELECT count(*) FROM $this->tableName WHERE removeAt = 0".$itemIdSql.$itemTypeSql;
        $stmt = $this->db->prepare($sql);

        $stmt->execute();

        return $number_of_rows = $stmt->fetchColumn();
    }

    // Get item info

    public function quickInfo($row)
    {
        $time = new language($this->db, "en");

        $profileInfo = array(
            "error" => false,
            "error_code" => ERROR_SUCCESS
        );

        if ($row['fromUserId'] != 0) {

            $profile = new profile($this->db, $row['fromUserId']);
            $profileInfo = $profile->getVeryShort();
            unset($profile);
        }

        $result = array(
            "error" => false,
            "error_code" => ERROR_SUCCESS,
            "id" => $row['id'],
            "itemType" => $row['itemType'],
            "fromUserId" => $row['fromUserId'],
            "owner" => $profileInfo,
            "toUserId" => $row['toUserId'],
            "suspect" => array(
                "error" => false,
                "error_code" => ERROR_SUCCESS
            ),
            "itemId" => $row['itemId'],
            "abuseId" => $row['abuseId'],
            "description" => htmlspecialchars_decode(stripslashes($row['description'])),
            "createAt" => $row['createAt'],
            "removeAt" => $row['removeAt'],
            "date" => date("Y-m-d H:i:s", $row['createAt']),
            "timeAgo" => $time->timeAgo($row['createAt']),
            "ip_addr" => $row['ip_addr']);

        if ($row['toUserId'] != 0) {

            $profile = new profile($this->db, $row['toUserId']);
            $profileInfo = $profile->getVeryShort();
            unset($profile);

            $result['suspect'] = $profileInfo;
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

