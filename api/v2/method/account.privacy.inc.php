<?php

/*!
 * ifsoft.co.uk
 *
 * http://ifsoft.com.ua, https://ifsoft.co.uk
 * hindbyte@gmail.com
 *
 * Copyright 2012-2020 Demyanchuk Dmitry (hindbyte@gmail.com)
 */

if (!empty($_POST)) {

    $accountId = isset($_POST['accountId']) ? $_POST['accountId'] : 0;
    $accessToken = isset($_POST['accessToken']) ? $_POST['accessToken'] : '';

    $allowShowMyLikes = isset($_POST['allowShowMyLikes']) ? $_POST['allowShowMyLikes'] : 0;
    $allowShowMyGifts = isset($_POST['allowShowMyGifts']) ? $_POST['allowShowMyGifts'] : 0;
    $allowShowMyFriends = isset($_POST['allowShowMyFriends']) ? $_POST['allowShowMyFriends'] : 0;
    $allowShowMyGallery = isset($_POST['allowShowMyGallery']) ? $_POST['allowShowMyGallery'] : 0;
    $allowShowMyInfo = isset($_POST['allowShowMyInfo']) ? $_POST['allowShowMyInfo'] : 0;

    $allowShowMyLikes = helper::clearInt($allowShowMyLikes);
    $allowShowMyGifts = helper::clearInt($allowShowMyGifts);
    $allowShowMyFriends = helper::clearInt($allowShowMyFriends);
    $allowShowMyGallery = helper::clearInt($allowShowMyGallery);
    $allowShowMyInfo = helper::clearInt($allowShowMyInfo);

    $result = array("error" => true,
                    "error_code" => ERROR_CODE_INITIATE);

    $auth = new auth($dbo);

    if (!$auth->authorize($accountId, $accessToken)) {

        api::printError(ERROR_ACCESS_TOKEN, "Error authorization.");
    }

    $result = array("error" => false,
                    "error_code" => ERROR_SUCCESS);

    $account = new account($dbo, $accountId);

    $account->setPrivacySettings($allowShowMyLikes, $allowShowMyGifts, $allowShowMyFriends, $allowShowMyGallery, $allowShowMyInfo);

    $result = $account->getPrivacySettings();

    echo json_encode($result);
    exit;
}
