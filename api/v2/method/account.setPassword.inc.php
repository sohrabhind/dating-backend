<?php




if (!empty($_POST)) {

    $accountId = isset($_POST['accountId']) ? $_POST['accountId'] : 0;
    $accessToken = isset($_POST['accessToken']) ? $_POST['accessToken'] : '';

    $currentPassword = isset($_POST['currentPassword']) ? $_POST['currentPassword'] : '';
    $newPassword = isset($_POST['newPassword']) ? $_POST['newPassword'] : '';

    $currentPassword = helper::clearText($currentPassword);
    $currentPassword = helper::escapeText($currentPassword);

    $newPassword = helper::clearText($newPassword);
    $newPassword = helper::escapeText($newPassword);

    $auth = new auth($dbo);

    if (!$auth->authorize($accountId, $accessToken)) {

        api::printError(ERROR_ACCESS_TOKEN, "Error authorization.");
    }

    $result = array(
        "error" => true,
        "error_code" => ERROR_CODE_INITIATE
    );

    $account = new account($dbo, $accountId);
    $result = $account->setPassword($currentPassword, $newPassword);

    echo json_encode($result);
    exit;
}
