<?php


if (!empty($_POST)) {

    $accountId = isset($_POST['accountId']) ? $_POST['accountId'] : 0;
    $accessToken = isset($_POST['accessToken']) ? $_POST['accessToken'] : '';

    $profileId = isset($_POST['profileId']) ? $_POST['profileId'] : 0;

    $chatFromUserId = isset($_POST['chatFromUserId']) ? $_POST['chatFromUserId'] : 0;
    $chatToUserId = isset($_POST['chatToUserId']) ? $_POST['chatToUserId'] : 0;

    $chatId = isset($_POST['chatId']) ? $_POST['chatId'] : 0;
    $lastMessageId = isset($_POST['lastMessageId']) ? $_POST['lastMessageId'] : 0;

    $accountId = helper::clearInt($accountId);

    $profileId = helper::clearInt($profileId);

    $chatFromUserId = helper::clearInt($chatFromUserId);
    $chatToUserId = helper::clearInt($chatToUserId);

    $chatId = helper::clearInt($chatId);
    $lastMessageId = helper::clearInt($lastMessageId);

    $result = array("error" => true,
                    "error_code" => ERROR_CODE_INITIATE);

    $auth = new auth($dbo);

    if (!$auth->authorize($accountId, $accessToken)) {

        api::printError(ERROR_ACCESS_TOKEN, "Error authorization.");
    }

    $msg = new messages($dbo);
    $msg->setRequestFrom($accountId);

    if ($chatId == 0) {
        $chatId = $msg->getChatId($accountId, $profileId);
    }

    if ($chatId != 0) {
        $result = $msg->get($chatId, $lastMessageId, $chatFromUserId, $chatToUserId);
    }

    echo json_encode($result);
    exit;
}
