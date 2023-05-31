<?php

/*!
 * https://hindbyte.com
 * hindbyte@gmail.com
 *
 * Copyright 2012-2022 Demyanchuk Dmitry (hindbyte@gmail.com)
 */

class stats extends db_connect
{
    private $requestFrom = 0;

    public function __construct($dbo = NULL)
    {
        parent::__construct($dbo);

    }

    public function getChatsCount()
    {
        $stmt = $this->db->prepare("SELECT count(*) FROM chats WHERE removeAt = 0");
        $stmt->execute();

        return $number_of_rows = $stmt->fetchColumn();
    }

    public function getChatsTotal()
    {
        $stmt = $this->db->prepare("SELECT count(*) FROM chats");
        $stmt->execute();

        return $number_of_rows = $stmt->fetchColumn();
    }

    public function getMessagesCount()
    {
        $stmt = $this->db->prepare("SELECT count(*) FROM messages WHERE removeAt = 0");
        $stmt->execute();

        return $number_of_rows = $stmt->fetchColumn();
    }

    public function getMessagesTotal()
    {
        $stmt = $this->db->prepare("SELECT count(*) FROM messages");
        $stmt->execute();

        return $number_of_rows = $stmt->fetchColumn();
    }

    public function getUsersCount()
    {
        $stmt = $this->db->prepare("SELECT count(*) FROM users");
        $stmt->execute();

        return $number_of_rows = $stmt->fetchColumn();
    }

    public function getUsersCountByState($accountState)
    {
        $stmt = $this->db->prepare("SELECT count(*) FROM users WHERE state = (:state)");
        $stmt->bindParam(":state", $accountState, PDO::PARAM_INT);
        $stmt->execute();

        return $number_of_rows = $stmt->fetchColumn();
    }

    public function getImagesCount()
    {
        $stmt = $this->db->prepare("SELECT count(*) FROM images");
        $stmt->execute();

        return $number_of_rows = $stmt->fetchColumn();
    }

    public function getActiveImagesCount()
    {
        $stmt = $this->db->prepare("SELECT count(*) FROM images WHERE removeAt = 0");
        $stmt->execute();

        return $number_of_rows = $stmt->fetchColumn();
    }


    private function getMaxAccountId()
    {
        $stmt = $this->db->prepare("SELECT MAX(id) FROM users");
        $stmt->execute();

        return $number_of_rows = $stmt->fetchColumn();
    }

    private function getMaxPurchaseId()
    {
        $stmt = $this->db->prepare("SELECT MAX(id) FROM refill_history");
        $stmt->execute();

        return $number_of_rows = $stmt->fetchColumn();
    }


    private function getMaxAuthId()
    {
        $stmt = $this->db->prepare("SELECT MAX(id) FROM access_data");
        $stmt->execute();

        return $number_of_rows = $stmt->fetchColumn();
    }

    public function getPurchases($itemId = 0)
    {
        if ($itemId == 0) {

            $itemId = $this->getMaxPurchaseId();
            $itemId++;
        }

        $result = array("error" => false,
                        "error_code" => ERROR_SUCCESS,
                        "itemId" => $itemId,
                        "items" => array());

        $stmt = $this->db->prepare("SELECT * FROM refill_history WHERE id < (:itemId) ORDER BY id DESC LIMIT 50");
        $stmt->bindParam(':itemId', $itemId, PDO::PARAM_INT);

        if ($stmt->execute()) {

            while ($row = $stmt->fetch()) {

                $info = array("id" => $row['id'],
                              "toUserId" => $row['toUserId'],
                              "refillType" => $row['refillType'],
                              "amount" => $row['amount'],
                              "createAt" => $row['createAt']);

                array_push($result['items'], $info);

                $result['itemId'] = $row['id'];

                unset($info);
            }
        }

        return $result;
    }

    public function getAccounts($userId = 0)
    {
        if ($userId == 0) {

            $userId = $this->getMaxAccountId();
            $userId++;
        }

        $users = array("error" => false,
                        "error_code" => ERROR_SUCCESS,
                        "userId" => $userId,
                        "users" => array());

        $stmt = $this->db->prepare("SELECT id FROM users WHERE id < (:userId) ORDER BY id DESC LIMIT 20");
        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);

        if ($stmt->execute()) {

            while ($row = $stmt->fetch()) {

                $account = new account($this->db, $row['id']);

                $accountInfo = $account->get();

                array_push($users['users'], $accountInfo);

                $users['userId'] = $accountInfo['id'];

                unset($accountInfo);
            }
        }

        return $users;
    }


    public function searchAccounts($userId = 0, $query = "")
    {
        if ($userId == 0) {

            $userId = $this->getMaxAccountId();
            $userId++;
        }

        $users = array("error" => false,
                        "error_code" => ERROR_SUCCESS,
                        "userId" => $userId,
                        "query" => $query,
                        "users" => array());

        $searchText = '%'.$query.'%';

        $stmt = $this->db->prepare("SELECT id FROM users WHERE id < (:userId) AND username LIKE (:query) OR email LIKE (:query) OR fullname LIKE (:query) ORDER BY id DESC LIMIT 100");
        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':query', $searchText, PDO::PARAM_STR);

        if ($stmt->execute()) {

            while ($row = $stmt->fetch()) {

                $account = new account($this->db, $row['id']);

                $accountInfo = $account->get();

                array_push($users['users'], $accountInfo);

                $users['userId'] = $accountInfo['id'];

                unset($accountInfo);
            }
        }

        return $users;
    }

    public function getAuthData($accountId, $authId = 0)
    {
        if ($authId == 0) {

            $authId = $this->getMaxAuthId();
            $authId++;
        }

        $result = array("error" => false,
                        "error_code" => ERROR_SUCCESS,
                        "authId" => $authId,
                        "data" => array());

        $stmt = $this->db->prepare("SELECT * FROM access_data WHERE accountId = (:accountId) AND id < (:authId) ORDER BY id DESC LIMIT 200");
        $stmt->bindParam(':authId', $authId, PDO::PARAM_INT);
        $stmt->bindParam(':accountId', $accountId, PDO::PARAM_INT);

        if ($stmt->execute()) {

            while ($row = $stmt->fetch()) {;

                $dataInfo = array("id" => $row['id'],
                                  "accountId" => $row['accountId'],
                                  "accessToken" => $row['accessToken'],
                                  "clientId" => $row['clientId'],
                                  "createAt" => $row['createAt'],
                                  "removeAt" => $row['removeAt'],
                                  "ip_addr" => $row['ip_addr']);

                array_push($result['data'], $dataInfo);

                $result['authId'] = $row['id'];

                unset($dataInfo);
            }
        }

        return $result;
    }

    public function getAccountGcmHistory($accountId)
    {
        $result = array("error" => false,
                        "error_code" => ERROR_SUCCESS,
                        "data" => array());

        $stmt = $this->db->prepare("SELECT * FROM gcm_history WHERE accountId = (:accountId) ORDER BY id DESC LIMIT 20");
        $stmt->bindParam(':accountId', $accountId, PDO::PARAM_INT);

        if ($stmt->execute()) {

            while ($row = $stmt->fetch()) {;

                $dataInfo = array("id" => $row['id'],
                                  "msg" => $row['msg'],
                                  "msgType" => $row['msgType'],
                                  "status" => $row['status'],
                                  "success" => $row['success'],
                                  "createAt" => $row['createAt']);

                array_push($result['data'], $dataInfo);

                unset($dataInfo);
            }
        }

        return $result;
    }

    public function getGcmHistory()
    {
        $result = array("error" => false,
                        "error_code" => ERROR_SUCCESS,
                        "data" => array());

        $stmt = $this->db->prepare("SELECT * FROM gcm_history WHERE accountId = 0 ORDER BY id DESC LIMIT 20");

        if ($stmt->execute()) {

            while ($row = $stmt->fetch()) {;

                $dataInfo = array("id" => $row['id'],
                                  "msg" => $row['msg'],
                                  "msgType" => $row['msgType'],
                                  "status" => $row['status'],
                                  "success" => $row['success'],
                                  "createAt" => $row['createAt']);

                array_push($result['data'], $dataInfo);

                unset($dataInfo);
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

