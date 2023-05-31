<?php

/*!
 * ifsoft.co.uk
 *
 * http://ifsoft.com.ua, http://ifsoft.co.uk
 * qascript@ifsoft.co.uk
 *
 * Copyright 2012-2015 Demyanchuk Dmitry (hindbyte@gmail.com)
 */

if (!empty($_POST)) {

    

    $accountId = isset($_POST['accountId']) ? $_POST['accountId'] : '';
    $accessToken = isset($_POST['accessToken']) ? $_POST['accessToken'] : '';

    $itemId = isset($_POST['itemId']) ? $_POST['itemId'] : 0;
    $commentText = isset($_POST['commentText']) ? $_POST['commentText'] : '';

    $replyToUserId = isset($_POST['replyToUserId']) ? $_POST['replyToUserId'] : 0;

    
    $accountId = helper::clearInt($accountId);


    $itemId = helper::clearInt($itemId);

    $commentText = helper::clearText($commentText);

    $commentText = preg_replace( "/[\r\n]+/", " ", $commentText); //replace all new lines to one new line
    $commentText  = preg_replace('/\s+/', ' ', $commentText);        //replace all white spaces to one space

    $commentText = helper::escapeText($commentText);

    $replyToUserId = helper::clearInt($replyToUserId);

    $result = array("error" => true,
                    "error_code" => ERROR_CODE_INITIATE);

    $auth = new auth($dbo);

    if (!$auth->authorize($accountId, $accessToken)) {

        api::printError(ERROR_ACCESS_TOKEN, "Error authorization.");
    }

    if (strlen($commentText) != 0) {

        $images = new images($dbo);
        $images->setRequestFrom($accountId);

        $imageInfo = $images->info($itemId);

        $blacklist = new blacklist($dbo);
        $blacklist->setRequestFrom($imageInfo['fromUserId']);

        if ($blacklist->isExists($accountId)) {

            echo json_encode($result);
            exit;
        }

        if ($imageInfo['fromUserAllowPhotosComments'] == 0) {

            exit;
        }

        $image = new image($dbo);
        $image->setRequestFrom($accountId);

        $notifyId = 0;

        $result = $image->commentsCreate($itemId, $commentText, $notifyId, $replyToUserId);
    }

    echo json_encode($result);
    exit;
}
