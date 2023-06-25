<?php



if (!empty($_POST)) {

    $accountId = isset($_POST['accountId']) ? $_POST['accountId'] : 0;
    $accessToken = isset($_POST['accessToken']) ? $_POST['accessToken'] : '';

    $profileId = isset($_POST['profileId']) ? $_POST['profileId'] : 0;

    $reason = isset($_POST['reason']) ? $_POST['reason'] : 0;
    $description = isset($_POST['description']) ? $_POST['description'] : '';

    $profileId = helper::clearInt($profileId);

    $reason = helper::clearInt($reason);

    $description = helper::clearText($description);
    $description = helper::escapeText($description);

    $result = array(
        "error" => true,
        "error_code" => ERROR_CODE_INITIATE
    );

    $auth = new auth($dbo);

    if (!$auth->authorize($accountId, $accessToken)) {

        api::printError(ERROR_ACCESS_TOKEN, "Error authorization.");
    }

    if ($profileId == 0 || $profileId == $accountId) {

        echo json_encode($result);
        exit;
    }

    $reports = new reports($dbo);
    $reports->setRequestFrom($accountId);
    $result = $reports->add(REPORT_TYPE_PROFILE, $profileId, $reason, $description);

    echo json_encode($result);
    exit;
}
