<?php

if (!empty($_POST)) {
    $accountId = isset($_POST['accountId']) ? $_POST['accountId'] : 0;
    $accessToken = isset($_POST['accessToken']) ? $_POST['accessToken'] : '';
    $lastMessageId = isset($_POST['lastMessageId']) ? $_POST['lastMessageId'] : 0;
    
    $accountId = helper::clearInt($accountId);
    $lastMessageId = helper::clearInt($lastMessageId);

    $result = array("error" => true,
                    "error_code" => ERROR_CODE_INITIATE);
                    
    $auth = new auth($dbo);
    if (!$auth->authorize($accountId, $accessToken)) {
        api::printError(ERROR_ACCESS_TOKEN, "Error authorization.");
    }

    $messages = new messages($dbo);
    $messages->setRequestFrom($accountId);

    $result = $messages->getChatsList($lastMessageId);
    echo json_encode($result);
    exit;
}
