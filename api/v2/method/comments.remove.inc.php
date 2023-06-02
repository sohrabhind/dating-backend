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

    $commentId = isset($_POST['commentId']) ? $_POST['commentId'] : 0;

    $accountId = helper::clearInt($accountId);

    $commentId = helper::clearInt($commentId);

    $result = array(
        "error" => true,
        "error_code" => ERROR_CODE_INITIATE
    );

    $auth = new auth($dbo);

    if (!$auth->authorize($accountId, $accessToken)) {

        api::printError(ERROR_ACCESS_TOKEN, "Error authorization.");
    }

    $comments = new comments($dbo);
    $comments->setRequestFrom($accountId);

    $commentInfo = $comments->info($commentId);

    if (!$commentInfo['error'] && $commentInfo['removeAt'] == 0) {

        if ($commentInfo['owner']['id'] == $accountId) {

            $result = $comments->remove($commentId, $commentInfo);

        } else {

            $images = new gallery($dbo);
            $images->setRequestFrom($accountId);

            $itemInfo = $images->info($commentInfo['imageId']);

            if ($itemInfo['owner']['id'] == $accountId) {

                $result = $comments->remove($commentId, $commentInfo);
            }

            unset($itemInfo);
            unset($images);
        }
    }

    unset($commentInfo);
    unset($comments);

    echo json_encode($result);
    exit;
}
