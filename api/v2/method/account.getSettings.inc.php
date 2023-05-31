<?php

/*!
 * https://hindbyte.com
 * hindbyte@gmail.com
 *
 * Copyright 2012-2022 Demyanchuk Dmitry (hindbyte@gmail.com)
 */

if (!defined("APP_SIGNATURE")) {

    header("Location: /");
    exit;
}

if (!empty($_POST)) {

    $accountId = isset($_POST['accountId']) ? $_POST['accountId'] : 0;
    $accessToken = isset($_POST['accessToken']) ? $_POST['accessToken'] : '';

    $result = array(
        "error" => true,
        "error_code" => ERROR_CODE_INITIATE
    );

    $auth = new auth($dbo);

    if (!$auth->authorize($accountId, $accessToken)) {

        api::printError(ERROR_ACCESS_TOKEN, "Error authorization.");
    }

    $result = array(
        "error" => false,
        "error_code" => ERROR_SUCCESS
    );

    $account = new account($dbo, $accountId);
    $accountInfo = $account->get();
    unset($account);


    $notifications_count = 0;
    $level_messages_count = 0;

    // Get new messages count

    if (APP_MESSAGES_COUNTERS) {

        $msg = new msg($dbo);
        $msg->setRequestFrom($accountId);

        $level_messages_count = $msg->getNewMessagesCount();

        unset($msg);
    }

    // Get new notifications count

    $notifications = new notify($dbo);
    $notifications->setRequestFrom($accountId);

    $notifications_count = $notifications->getNewCount($accountInfo['lastNotifyView']);

    unset($notifications);


    // Get chat settings

    $settings = new settings($dbo);

    $config = $settings->get();

    $arr = array();

    $arr = $config['allowSeenTyping'];
    $result['seenTyping'] = $arr['intValue'];

    $result['messagesCount'] = $level_messages_count;
    $result['notificationsCount'] = $notifications_count;
    
    $result['free_messages_count'] = $accountInfo['free_messages_count'];
    $result['level_messages_count'] = $accountInfo['level_messages_count'];
    $result['level'] = $accountInfo['level'];

    echo json_encode($result);
    exit;
}
