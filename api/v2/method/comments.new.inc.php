<?php

/*!
 * https://hindbyte.com
 * hindbyte@gmail.com
 *
 * Copyright 2012-2021 Demyanchuk Dmitry (hindbyte@gmail.com)
 */

if (!empty($_POST)) {

    $accountId = isset($_POST['accountId']) ? $_POST['accountId'] : 0;
    $accessToken = isset($_POST['accessToken']) ? $_POST['accessToken'] : '';

    $itemId = isset($_POST['itemId']) ? $_POST['itemId'] : 0;
    $itemType = isset($_POST['itemType']) ? $_POST['itemType'] : 0;
    $commentText = isset($_POST['commentText']) ? $_POST['commentText'] : '';

    $replyToUserId = isset($_POST['replyToUserId']) ? $_POST['replyToUserId'] : 0;

    $accountId = helper::clearInt($accountId);


    $itemId = helper::clearInt($itemId);
    $itemType = helper::clearInt($itemType);

    $commentText = helper::clearText($commentText);

    $commentText = preg_replace( "/[\r\n]+/", " ", $commentText);    //replace all new lines to one new line
    $commentText  = preg_replace('/\s+/', ' ', $commentText);        //replace all white spaces to one space

    $commentText = helper::escapeText($commentText);

    $replyToUserId = helper::clearInt($replyToUserId);

    $result = array(
        "error" => true,
        "error_code" => ERROR_CODE_INITIATE
    );

    $auth = new auth($dbo);

    if (!$auth->authorize($accountId, $accessToken)) {

        api::printError(ERROR_ACCESS_TOKEN, "Error authorization.");
    }

    if (strlen($commentText) != 0) {

        $images = new gallery($dbo);
        $images->setRequestFrom($accountId);

        $itemInfo = $images->info($itemId);

        if (!$itemInfo['error']) {

            $blacklist = new blacklist($dbo);
            $blacklist->setRequestFrom($itemInfo['owner']['id']);

            if ($blacklist->isExists($accountId)) {

                echo json_encode($result);
                exit;
            }

            if ($itemInfo['owner']['allowPhotosComments'] == 0) {

                echo json_encode($result);
                exit;
            }

            $comments = new comments($dbo);
            $comments->setRequestFrom($accountId);

            $notifyId = 0;

            $result = $comments->add($itemId, $itemType, $itemInfo, $commentText, $replyToUserId);
        }
    }

    echo json_encode($result);
    exit;
}
