<?php



class find extends db_connect
{

    private $requestFrom = 0;
    private $language = 'en';

    public function __construct($dbo = NULL)
    {
        parent::__construct($dbo);
    }


    public function lastIndex()
    {
        $stmt = $this->db->prepare("SELECT count(*) FROM users");
        $stmt->execute();

        return $number_of_rows = $stmt->fetchColumn() + 1;
    }


    public function start($itemId = 0, $gender = 3, $online = 0, $levelMode = 0, $ageFrom = 18, $ageTo = 105, $distance = 12500, $lat = 0.0000, $lng = 0.0000)
    {

        if ($itemId == 0) {
            $itemId = 90000000;
            $itemId++;
        }

        $ageFrom = $ageFrom - 1;
        $ageTo = $ageTo + 1;

        $result = array(
            "error" => false,
            "error_code" => ERROR_SUCCESS,
            "itemId" => $itemId,
            "items" => array()
        );


        $origLat = $lat;
        $origLng = $lng;
        $dist = $distance; // This is the maximum distance (in kilometers) away from $origLat, $origLon in which to search

        $endSql = " having distance < {$dist} ORDER BY regtime DESC LIMIT 20";

        $genderSql = "";
        if ($gender != 3) {
            $genderSql = " AND gender = {$gender}";
        }

        $onlineSql = "";
        if ($online > 0) {
            $current_time = time() - (15 * 60);
            $onlineSql = " AND last_authorize > {$current_time}";
        }

        $levelModeSql = "";
        if ($levelMode > 0) {
            $levelModeSql = " AND level != 0";
        }

        $dateSql = " AND u_age >= {$ageFrom} AND u_age <= {$ageTo}";

        $sql = "SELECT id, regtime, lat, lng, 12733 *
                    ASIN(SQRT( POWER(SIN(($origLat - lat)*pi()/180/2),2)
                    +COS($origLat*pi()/180 )*COS(lat*pi()/180)
                    *POWER(SIN(($origLng-lng)*pi()/180/2),2)))
                    as distance  FROM users WHERE state = 0  AND id < {$itemId}".$genderSql.$onlineSql.$levelModeSql.$dateSql.$endSql;
        $stmt = $this->db->prepare($sql);

        if ($stmt->execute()) {
            if ($stmt->rowCount() > 0) {
                while ($row = $stmt->fetch()) {
                    $profile = new profile($this->db, $row['id']);
                    $profile->setRequestFrom($this->getRequestFrom());
                    $profileInfo = $profile->getVeryShort();
                    $profileInfo['distance'] = round($row['distance'], 1);//round($this->getDistance($lat, $lng, $profileInfo['lat'], $profileInfo['lng']), 1);
                    unset($profile);
                    array_push($result['items'], $profileInfo);
                    $result['itemId'] = $row['id'];
                }
            }
        }
        return $result;
    }

    public function getDistance($fromLat, $fromLng, $toLat, $toLng) {

        $latFrom = deg2rad($fromLat);
        $lonFrom = deg2rad($fromLng);
        $latTo = deg2rad($toLat);
        $lonTo = deg2rad($toLng);

        $delta = $lonTo - $lonFrom;

        $alpha = pow(cos($latTo) * sin($delta), 2) + pow(cos($latFrom) * sin($latTo) - sin($latFrom) * cos($latTo) * cos($delta), 2);
        $beta = sin($latFrom) * sin($latTo) + cos($latFrom) * cos($latTo) * cos($delta);

        $angle = atan2(sqrt($alpha), $beta);

        return ($angle * 6371000) / 1000;
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

