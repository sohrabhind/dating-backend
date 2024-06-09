<?php



if (!empty($_POST)) {

    $accountId = isset($_POST['accountId']) ? $_POST['accountId'] : 0;
    $accessToken = isset($_POST['accessToken']) ? $_POST['accessToken'] : '';

    $notifyId = isset($_POST['notifyId']) ? $_POST['notifyId'] : 0;

    $notifyId = helper::clearInt($notifyId);

    $result = array("error" => true,
                    "error_code" => ERROR_CODE_INITIATE);

    $auth = new auth($dbo);

    if (!$auth->authorize($accountId, $accessToken)) {

        api::printError(ERROR_ACCESS_TOKEN, "Error authorization.");
    }

    if ($notifyId == 0) {
        $account = new account($dbo, $accountId);
        $account->setLastNotify();
        unset($account);
    }

    $notify = new notify($dbo);
    $notify->setRequestFrom($accountId);
    $result = $notify->getAll($notifyId);

    echo json_encode($result);
    exit;
}
