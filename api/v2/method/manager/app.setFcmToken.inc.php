<?php





if (!empty($_POST)) {

    $accountId = isset($_POST['accountId']) ? $_POST['accountId'] : 0;
    $accessToken = isset($_POST['accessToken']) ? $_POST['accessToken'] : '';

    $fcm_regId = isset($_POST['fcm_regId']) ? $_POST['fcm_regId'] : '';

    $fcm_regId = helper::clearText($fcm_regId);
    $fcm_regId = helper::escapeText($fcm_regId);

    $result = array(
        "error" => true,
        "error_code" => ERROR_CODE_INITIATE
    );

    $admin = new admin($dbo);
    $admin->setId($accountId);

    if (!$admin->authorize($accountId, $accessToken)) {

        api::printError(ERROR_ACCESS_TOKEN, "Error authorization.");
    }

    $auth_id = $admin->getAuthorizationId($accountId, $accessToken);
    $admin->updateAuthorizationId($auth_id, $fcm_regId);

    $result = array(
        "error" => false,
        "error_code" => ERROR_SUCCESS
    );

    echo json_encode($result);
    exit;
}
