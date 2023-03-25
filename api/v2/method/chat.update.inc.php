<?php

/*!
 * ifsoft.co.uk
 *
 * http://ifsoft.com.ua, http://ifsoft.co.uk
 * hindbyte@gmail.com
 *
 * Copyright 2012-2019 Demyanchuk Dmitry (hindbyte@gmail.com)
 */

if (!empty($_POST)) {

    $clientId = isset($_POST['clientId']) ? $_POST['clientId'] : 0;

    $accountId = isset($_POST['accountId']) ? $_POST['accountId'] : 0;
    $accessToken = isset($_POST['accessToken']) ? $_POST['accessToken'] : '';

    $chatFromUserId = isset($_POST['chatFromUserId']) ? $_POST['chatFromUserId'] : 0;
    $chatToUserId = isset($_POST['chatToUserId']) ? $_POST['chatToUserId'] : 0;

    $chatId = isset($_POST['chatId']) ? $_POST['chatId'] : 0;

    $freeMessagesCount = isset($_POST['freeMessagesCount']) ? $_POST['freeMessagesCount'] : 0;

    $clientId = helper::clearInt($clientId);
    $accountId = helper::clearInt($accountId);

    $chatFromUserId = helper::clearInt($chatFromUserId);
    $chatToUserId = helper::clearInt($chatToUserId);

    $chatId = helper::clearInt($chatId);

    $freeMessagesCount = helper::clearInt($freeMessagesCount);

    $result = array(
        "error" => false,
        "error_code" => ERROR_CODE_INITIATE
    );

//    $auth = new auth($dbo);
//
//    if (!$auth->authorize($accountId, $accessToken)) {
//
//        api::printError(ERROR_ACCESS_TOKEN, "Error authorization.");
//    }

    // Set Free Messages Count for Function "Pro Mode"

    if (auth::isSession()) {

        $freeMessagesCount = auth::getCurrentFreeMessagesCount();
    }

    $account = new account($dbo, $accountId);
    $account->setFreeMessagesCount($freeMessagesCount);
    unset($account);

    // Update Chat info

    $msg = new msg($dbo);
    $msg->setRequestFrom($accountId);

    $profileId = $chatFromUserId;

    if ($profileId == $accountId) {

        $msg->setChatLastView_FromId($chatId);

        $msg->setSeen($chatId, $chatToUserId);

        // GCM_MESSAGE_ONLY_FOR_PERSONAL_USER = 2
        // GCM_NOTIFY_SEEN= 15
        // GCM_NOTIFY_TYPING= 16
        // GCM_NOTIFY_TYPING_START = 27
        // GCM_NOTIFY_TYPING_END = 28

        $fcm = new fcm($dbo);
        $fcm->setRequestFrom($chatFromUserId);
        $fcm->setRequestTo($chatToUserId);
        $fcm->setType(15);
        $fcm->setTitle("Seen");
        $fcm->setItemId($chatId);
        $fcm->prepare();
        $fcm->send();
        unset($fcm);

    } else {

        $msg->setChatLastView_ToId($chatId);

        $msg->setSeen($chatId, $chatFromUserId);

        // GCM_MESSAGE_ONLY_FOR_PERSONAL_USER = 2
        // GCM_NOTIFY_SEEN= 15
        // GCM_NOTIFY_TYPING= 16
        // GCM_NOTIFY_TYPING_START = 27
        // GCM_NOTIFY_TYPING_END = 28

        $fcm = new fcm($dbo);
        $fcm->setRequestFrom($chatToUserId);
        $fcm->setRequestTo($chatFromUserId);
        $fcm->setType(15);
        $fcm->setTitle("Seen");
        $fcm->setItemId($chatId);
        $fcm->prepare();
        $fcm->send();
        unset($fcm);
    }

    echo json_encode($result);
    exit;
}
