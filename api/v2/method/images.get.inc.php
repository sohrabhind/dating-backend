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

    

    $accountId = isset($_POST['accountId']) ? $_POST['accountId'] : 0;
    $accessToken = isset($_POST['accessToken']) ? $_POST['accessToken'] : '';

    $itemId = isset($_POST['itemId']) ? $_POST['itemId'] : 0;

    
    $accountId = helper::clearInt($accountId);

    $itemId = helper::clearInt($itemId);

    $result = array("error" => true,
                    "error_code" => ERROR_CODE_INITIATE);

    $auth = new auth($dbo);

    if (!$auth->authorize($accountId, $accessToken)) {

        api::printError(ERROR_ACCESS_TOKEN, "Error authorization.");
    }

    $images = new images($dbo);
    $images->setRequestFrom($accountId);

    $itemInfo = $images->info($itemId);

    if (!$itemInfo['error']) {

        if ($itemInfo['fromUserId'] != $accountId && $itemInfo['removeAt'] != 0) {

            exit;
        }

        $image = new image($dbo);
        $image->setRequestFrom($accountId);

        $result = array("error" => false,
                        "error_code" => ERROR_SUCCESS,
                        "itemId" => $itemId,
                        "comments" => $image->commentsGet($itemId, 0),
                        "items" => array());

        array_push($result['items'], $itemInfo);
    }

    echo json_encode($result);
    exit;
}
