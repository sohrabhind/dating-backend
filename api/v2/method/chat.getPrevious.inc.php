<?php



if (!empty($_POST)) {

    $accountId = isset($_POST['accountId']) ? $_POST['accountId'] : '';
    $accessToken = isset($_POST['accessToken']) ? $_POST['accessToken'] : '';

    $profileId = isset($_POST['profileId']) ? $_POST['profileId'] : '';

    $chatId = isset($_POST['chatId']) ? $_POST['chatId'] : 0;
    $lastMessageId = isset($_POST['lastMessageId']) ? $_POST['lastMessageId'] : 0;

    $accountId = helper::clearInt($accountId);

    $profileId = helper::clearInt($profileId);

    $chatId = helper::clearInt($chatId);
    $lastMessageId = helper::clearInt($lastMessageId);

    $result = array("error" => true,
                    "error_code" => ERROR_CODE_INITIATE);

    $auth = new auth($dbo);

    if (!$auth->authorize($accountId, $accessToken)) {

        api::printError(ERROR_ACCESS_TOKEN, "Error authorization.");
    }

    $messages = new messages($dbo);
    $messages->setRequestFrom($accountId);

    if ($chatId == 0) {

        $chatId = $messages->getChatId($accountId, $profileId);
    }

    if ($chatId != 0) {

        $result = $messages->getPreviousMessages($chatId, $lastMessageId);
    }

    echo json_encode($result);
    exit;
}
