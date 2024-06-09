<?php



if (!empty($_POST)) {

    $accountId = isset($_POST['accountId']) ? $_POST['accountId'] : 0;
    $accessToken = isset($_POST['accessToken']) ? $_POST['accessToken'] : '';

    $itemId = isset($_POST['itemId']) ? $_POST['itemId'] : 0;

    $gender = isset($_POST['gender']) ? $_POST['gender'] : 1;
    $online = isset($_POST['online']) ? $_POST['online'] : 0;
    $levelMode = isset($_POST['level']) ? $_POST['level'] : 0;
    $ageFrom = isset($_POST['ageFrom']) ? $_POST['ageFrom'] : 13;
    $ageTo = isset($_POST['ageTo']) ? $_POST['ageTo'] : 110;

    $distance = isset($_POST['distance']) ? $_POST['distance'] : 24855;

    $lat = isset($_POST['lat']) ? $_POST['lat'] : 0.0000;
    $lng = isset($_POST['lng']) ? $_POST['lng'] : 0.0000;

    $itemId = helper::clearInt($itemId);

    $gender = helper::clearInt($gender);
    $online = helper::clearInt($online);
    $levelMode = helper::clearInt($levelMode);

    $ageFrom = helper::clearInt($ageFrom);
    $ageTo = helper::clearInt($ageTo);

    $distance = helper::clearInt($distance);

    $lat = helper::clearText($lat);
    $lat = helper::escapeText($lat);

    $lng = helper::clearText($lng);
    $lng = helper::escapeText($lng);

    $result = array(
        "error" => true,
        "error_code" => ERROR_CODE_INITIATE
    );

    $auth = new auth($dbo);

    if (!$auth->authorize($accountId, $accessToken)) {

        api::printError(ERROR_ACCESS_TOKEN, "Error authorization.");
    }

    $find = new find($dbo);
    $find->setRequestFrom($accountId);

    $result = $find->start($itemId, $gender, $online, $levelMode, $ageFrom, $ageTo, $distance, $lat, $lng);
    echo json_encode($result);
    exit;
}
