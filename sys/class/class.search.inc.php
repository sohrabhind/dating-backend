<?php



class search extends db_connect
{

    private $requestFrom = 0;
    private $language = 'en';

    public function __construct($dbo = NULL)
    {
        parent::__construct($dbo);
    }

    private function getCount($queryText, $gender = -1, $online = -1, $photo = -1, $ageFrom = 18, $ageTo = 110)
    {
        $queryText = "%".$queryText."%";

        $genderSql = "";

        if ($gender != -1) {

            $genderSql = " AND gender = {$gender}";
        }

        $onlineSql = "";

        if ($online != -1) {

            $current_time = time() - (15 * 60);

            $onlineSql = " AND last_authorize > {$current_time}";
        }

        $photoSql = "";

        if ($photo != -1) {

            $photoSql = " AND bigPhotoUrl <> ''";
        }

        $ageTo = $ageTo+1;
        $ageFrom = $ageFrom-1;

        $dateSql = " AND u_age < {$ageTo} AND u_age > {$ageFrom}";
        $sql = "SELECT count(*) FROM users WHERE state = 0 AND (username LIKE '{$queryText}' OR fullname LIKE '{$queryText}' OR email LIKE '{$queryText}' OR country LIKE '{$queryText}')".$genderSql.$onlineSql.$photoSql.$dateSql;

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return $number_of_rows = $stmt->fetchColumn();
    }

    public function lastIndex()
    {
        $stmt = $this->db->prepare("SELECT count(*) FROM users");
        $stmt->execute();

        return $number_of_rows = $stmt->fetchColumn() + 1;
    }

    public function query($queryText = '', $userId = 0, $gender = -1, $online = -1, $photo = -1, $ageFrom = 18, $ageTo = 110)
    {
        $originQuery = $queryText;

        if ($userId == 0) {

            $userId = $this->lastIndex();
            $userId++;
        }

        $endSql = " ORDER BY regtime DESC LIMIT 20";

        $genderSql = "";

        if ($gender != -1) {

            $genderSql = " AND gender = {$gender}";
        }

        $onlineSql = "";

        if ($online != -1) {

            $current_time = time() - (15 * 60);

            $onlineSql = " AND last_authorize > {$current_time}";
        }

        $photoSql = "";

        if ($photo != -1) {

            $photoSql = " AND bigPhotoUrl <> ''";
        }

        
        $ageTo = $ageTo+1;
        $ageFrom = $ageFrom-1;

        $dateSql = " AND u_age < {$ageTo} AND u_age > {$ageFrom}";

        $users = array("error" => false,
                       "error_code" => ERROR_SUCCESS,
                       "itemCount" => $this->getCount($originQuery, $gender, $online, $photo, $ageFrom, $ageTo),
                       "itemId" => $userId,
                       "query" => $originQuery,
                       "items" => array());

        $queryText = "%".$queryText."%";

        $sql = "SELECT id, regtime FROM users WHERE state = 0 AND (username LIKE '{$queryText}' OR fullname LIKE '{$queryText}' OR email LIKE '{$queryText}' OR country LIKE '{$queryText}') AND id < {$userId}".$genderSql.$onlineSql.$photoSql.$dateSql.$endSql;
        $stmt = $this->db->prepare($sql);

        if ($stmt->execute()) {

            if ($stmt->rowCount() > 0) {

                while ($row = $stmt->fetch()) {

                    $profile = new profile($this->db, $row['id']);
                    $profile->setRequestFrom($this->requestFrom);

                    array_push($users['items'], $profile->get());

                    $users['itemId'] = $row['id'];

                    unset($profile);
                }
            }
        }

        return $users;
    }

    public function preload($itemId = 0, $gender = -1, $online = -1, $photo = -1, $ageFrom = 13, $ageTo = 110)
    {
        if ($itemId == 0) {

            $itemId = $this->lastIndex();
            $itemId++;
        }

        $endSql = " ORDER BY regtime DESC LIMIT 20";

        $genderSql = "";

        if ($gender != -1) {

            $genderSql = " AND gender = {$gender}";
        }

        $onlineSql = "";

        if ($online != -1) {

            $current_time = time() - (15 * 60);

            $onlineSql = " AND last_authorize > {$current_time}";
        }

        $photoSql = "";

        if ($photo != -1) {

            $photoSql = " AND bigPhotoUrl <> ''";
        }

        
        $ageTo = $ageTo+1;
        $ageFrom = $ageFrom-1;

        $dateSql = " AND u_age < {$ageTo} AND u_age > {$ageFrom}";

        $result = array("error" => false,
                        "error_code" => ERROR_SUCCESS,
                        "itemCount" => $this->getCount("", $gender, $online, $photo),
                        "itemId" => $itemId,
                        "items" => array());

        $sql = "SELECT id, regtime FROM users WHERE state = 0 AND id < {$itemId}".$genderSql.$onlineSql.$photoSql.$dateSql.$endSql;
        $stmt = $this->db->prepare($sql);

        if ($stmt->execute()) {

            if ($stmt->rowCount() > 0) {

                while ($row = $stmt->fetch()) {

                    $profile = new profile($this->db, $row['id']);
                    $profile->setRequestFrom($this->requestFrom);

                    array_push($result['items'], $profile->get());

                    $result['itemId'] = $row['id'];

                    unset($profile);
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

