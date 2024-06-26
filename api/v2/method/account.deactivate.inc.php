<?php



if (!empty($_POST)) {

    $accountId = isset($_POST['accountId']) ? $_POST['accountId'] : '';
    $accessToken = isset($_POST['accessToken']) ? $_POST['accessToken'] : '';

    $currentPassword = isset($_POST['currentPassword']) ? $_POST['currentPassword'] : '';

    $currentPassword = helper::clearText($currentPassword);
    $currentPassword = helper::escapeText($currentPassword);

    $auth = new auth($dbo);

    if (!$auth->authorize($accountId, $accessToken)) {

        api::printError(ERROR_ACCESS_TOKEN, "Error authorization.");
    }

    $result = array("error" => true, "error_code" => ERROR_CODE_INITIATE);

    // Remove All Medias

    $images = new gallery($dbo);
    $images->setRequestFrom($accountId);
    $images->removeAll();
    $images->checkAndRemoveOrphanedFiles();
    unset($images);

    $account = new account($dbo, $accountId);

    // Remove Avatar
    $photos = array("error" => false, "bigPhotoUrl" => "");
    $account->setPhoto($photos);

    // Deactivate Account

    $result = $account->deactivation($currentPassword);

    echo json_encode($result);
    exit;
}
