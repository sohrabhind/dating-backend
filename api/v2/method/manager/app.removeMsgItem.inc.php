<?php

;



if (!empty($_POST)) {

    $accountId = isset($_POST['accountId']) ? $_POST['accountId'] : 0;
    $accessToken = isset($_POST['accessToken']) ? $_POST['accessToken'] : '';

    $itemId = isset($_POST['itemId']) ? $_POST['itemId'] : 0;

    $itemId = helper::clearInt($itemId);

    $result = array(
        "error" => true,
        "error_code" => ERROR_CODE_INITIATE
    );

    $admin = new admin($dbo);
    $admin->setId($accountId);

    if (!$admin->authorize($accountId, $accessToken)) {

        api::printError(ERROR_ACCESS_TOKEN, "Error authorization.");
    }

    $result = array(
        "error" => false,
        "error_code" => ERROR_SUCCESS
    );

    $admin_info = $admin->get();

    if (!$admin_info['error'] && $admin_info['access_level'] != ADMIN_ACCESS_LEVEL_READ_ONLY_RIGHTS) {

        $messages = new messages($dbo);
        $messages->remove($itemId);
        unset($messages);
    }

    unset($admin_info);

    echo json_encode($result);
    exit;
}
