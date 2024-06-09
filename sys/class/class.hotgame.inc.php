<?php



class hotgame extends db_connect
{
    private $requestFrom = 0;

    public function __construct($dbo = null)
    {
        parent::__construct($dbo);
    }

    private function getMaxId()
    {
        $stmt = $this->db->prepare("SELECT MAX(id) FROM users");
        $stmt->execute();

        return $number_of_rows = $stmt->fetchColumn();
    }

    public function get($itemId, $origLat, $origLon, $distance = 1000, $gender = 0, $country = '')
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

        $dist = $distance; // This is the maximum distance (in miles) away from $origLat, $origLon in which to search


        $gender_sql = " and (gender = {$gender}) ";

        if ($origLat == 0 && $origLon == 0) {
            $sql = "SELECT id, lat, lng, 12733 *
            ASIN(SQRT( POWER(SIN(($origLat - lat)*pi()/180/2),2)
            +COS($origLat*pi()/180 )*COS(lat*pi()/180)
            *POWER(SIN(($origLon-lng)*pi()/180/2),2)))
            as distance 
            FROM users WHERE
            (id <> $this->requestFrom)
            $gender_sql
            and (state = 0)
            and country = '$country'  
            ORDER BY RAND() 
            LIMIT 10";
        } else {
            $sql = "SELECT id, lat, lng, 12733 *
            ASIN(SQRT( POWER(SIN(($origLat - lat)*pi()/180/2),2)
            +COS($origLat*pi()/180 )*COS(lat*pi()/180)
            *POWER(SIN(($origLon-lng)*pi()/180/2),2)))
            as distance, country as cntry 
            FROM users WHERE
            (id <> $this->requestFrom)
            $gender_sql
            and (state = 0)
            having distance < $dist or cntry = '$country' 
            ORDER BY RAND() 
            LIMIT 10";
        }


        $stmt = $this->db->prepare($sql);
        if ($stmt->execute()) {
            if ($stmt->rowCount() > 0) {
                while ($row = $stmt->fetch()) {
                    $profile = new profile($this->db, $row['id']);
                    $profile->setRequestFrom($this->requestFrom);
                    $profileInfo = $profile->get();
                    if ($profileInfo['access_level'] == 0) {
                        if ($origLat != 0 && $origLon != 0) {
                            $profileInfo['distance'] = round($row['distance'], 1);
                        }
                    }
                    unset($profile);
                    array_push($result['items'], $profileInfo);
                    $result['itemId'] = $row['id'];
                }
            }
        }

        return $result;
    }

    public function getDistance($fromLat, $fromLng, $toLat, $toLng)
    {
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

    public function setRequestFrom($requestFrom)
    {
        $this->requestFrom = $requestFrom;
    }

    public function getRequestFrom()
    {
        return $this->requestFrom;
    }
}
