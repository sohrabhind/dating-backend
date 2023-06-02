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

    $accountId = isset($_POST['accountId']) ? $_POST['accountId'] : '';
    $accessToken = isset($_POST['accessToken']) ? $_POST['accessToken'] : '';

    $commentId = isset($_POST['commentId']) ? $_POST['commentId'] : 0;

    $accountId = helper::clearInt($accountId);

    $commentId = helper::clearInt($commentId);

    $result = array("error" => true,
                    "error_code" => ERROR_CODE_INITIATE);

    $auth = new auth($dbo);

    if (!$auth->authorize($accountId, $accessToken)) {

        api::printError(ERROR_ACCESS_TOKEN, "Error authorization.");
    }

    $image = new image($dbo);
    $image->setRequestFrom($accountId);

    $commentInfo = $image->commentsInfo($commentId);

    if ($commentInfo['fromUserId'] == $accountId) {

        $image->commentsRemove($commentId);

    } else {

        $images = new images($dbo);
        $images->setRequestFrom($accountId);

        $imageInfo = $images->info($commentInfo['imageId']);

        if ($imageInfo['fromUserId'] == $accountId) {

            $image->commentsRemove($commentId);
        }
    }

    unset($comments);
    unset($post);

    echo json_encode($result);
    exit;
}
