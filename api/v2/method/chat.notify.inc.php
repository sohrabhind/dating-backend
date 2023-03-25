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

    $notifyId = isset($_POST['notifyId']) ? $_POST['notifyId'] : 0;

    $android_fcm_regId = isset($_POST['android_fcm_regId']) ? $_POST['android_fcm_regId'] : "";

    $clientId = helper::clearInt($clientId);
    $accountId = helper::clearInt($accountId);

    $chatFromUserId = helper::clearInt($chatFromUserId);
    $chatToUserId = helper::clearInt($chatToUserId);

    $chatId = helper::clearInt($chatId);

    $notifyId = helper::clearInt($notifyId);

    $result = array("error" => false,
                    "android_fcm_regId" => $android_fcm_regId,
                    "error_code" => ERROR_CODE_INITIATE);

    $profileId = $chatFromUserId;
    if ($profileId == $accountId) {
        $fcm = new fcm($dbo);
        $fcm->setRequestFrom($accountId);
        $fcm->setRequestTo($chatToUserId);
        $fcm->setType($notifyId);
        $fcm->setTitle("Seen");
        $fcm->setItemId($chatId);
        $fcm->prepare();
        $fcm->send();
        unset($fcm);
    } else {
        $fcm = new fcm($dbo);
        $fcm->setRequestFrom($accountId);
        $fcm->setRequestTo($chatFromUserId);
        $fcm->setType($notifyId);
        $fcm->setTitle("Seen");
        $fcm->setItemId($chatId);
        $fcm->prepare();
        $fcm->send();
        unset($fcm);
    }

    echo json_encode($result);
    exit;
}
