<?php



if (!empty($_POST)) {

    $accountId = isset($_POST['accountId']) ? $_POST['accountId'] : 0;
    $accessToken = isset($_POST['accessToken']) ? $_POST['accessToken'] : '';

    $profileId = isset($_POST['profileId']) ? $_POST['profileId'] : 0;

    $profileId = helper::clearInt($profileId);

    $result = array("error" => true,
                    "error_code" => ERROR_CODE_INITIATE);

    $auth = new auth($dbo);

//    if (!$auth->authorize($accountId, $accessToken)) {
//
//        api::printError(ERROR_ACCESS_TOKEN, "Error authorization.");
//    }

    $profile = new profile($dbo, $profileId);
    $profile->setRequestFrom($accountId);

    $account = new account($dbo, $accountId);
    $accountInfo = $account->get();

    if ($profileId == $accountId) {

        $account->setLastActive();

    }

    $account->updateCounters();
    $result = $profile->get();
    echo json_encode($result);
    exit;
}
