<?php

if (!empty($_POST)) {

    $accountId = isset($_POST['accountId']) ? $_POST['accountId'] : 0;
    $accessToken = isset($_POST['accessToken']) ? $_POST['accessToken'] : '';

    $profileId = isset($_POST['profileId']) ? $_POST['profileId'] : 0;

    $chatId = isset($_POST['chatId']) ? $_POST['chatId'] : 0;
    $messageText = isset($_POST['messageText']) ? $_POST['messageText'] : "";

    $listId = isset($_POST['listId']) ? $_POST['listId'] : 0;

    $accountId = helper::clearInt($accountId);

    $profileId = helper::clearInt($profileId);

    $chatId = helper::clearInt($chatId);

    $listId = helper::clearInt($listId);

    $messageText = helper::clearText($messageText);

    $messageText = preg_replace( "/[\r\n]+/", "<br>", $messageText); //replace all new lines to one new line
    $messageText  = preg_replace('/\s+/', ' ', $messageText);        //replace all white spaces to one space

    $messageText = helper::escapeText($messageText);

    $result = array("error" => true, "error_code" => ERROR_CODE_INITIATE);

    
    $auth = new auth($dbo);

    if (!$auth->authorize($accountId, $accessToken)) {
        api::printError(ERROR_ACCESS_TOKEN, "Error authorization.");
    }

    $profile = new profile($dbo, $profileId);
    $profile->setRequestFrom($accountId);

    $profileInfo = $profile->getShort();

    if ($profileInfo['state'] != ACCOUNT_STATE_ENABLED) {
        echo json_encode($result);
        exit;
    }

    if (!$profileInfo['inBlackList']) {
        $messages = new messages($dbo);
        $messages->setRequestFrom($accountId);
        $result = $messages->create($profileId, $chatId, $messageText, "", $listId);
    }
    
    echo json_encode($result);
    exit;
}
