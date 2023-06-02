<?php

/*!
 * ifsoft.co.uk
 *
 * http://ifsoft.com.ua, https://ifsoft.co.uk, https://hindbyte.com
 * hindbyte@gmail.com
 *
 * Copyright 2012-2020 Demyanchuk Dmitry (hindbyte@gmail.com)
 */

if (!empty($_POST)) {

    

    $accountId = isset($_POST['accountId']) ? $_POST['accountId'] : 0;
    $accessToken = isset($_POST['accessToken']) ? $_POST['accessToken'] : '';

    $itemId = isset($_POST['itemId']) ? $_POST['itemId'] : 0;

    
    $accountId = helper::clearInt($accountId);

    $itemId = helper::clearInt($itemId);

    $result = array(
        "error" => true,
        "error_code" => ERROR_CODE_INITIATE
    );

    $auth = new auth($dbo);

    if (!$auth->authorize($accountId, $accessToken)) {

        api::printError(ERROR_ACCESS_TOKEN, "Error authorization.");
    }

    $images = new gallery($dbo);
    $images->setRequestFrom($accountId);

    $itemInfo = $images->info($itemId);

    if (!$itemInfo['error'] && $itemInfo['removeAt'] == 0) {

        $comments = new comments($dbo);
        $comments->setRequestFrom($accountId);

        $result = array("error" => false,
                        "error_code" => ERROR_SUCCESS,
                        "itemId" => $itemId,
                        "comments" => $comments->get($itemId, 0, $itemInfo),
                        "items" => array());

        array_push($result['items'], $itemInfo);

        unset($comments);
    }

    echo json_encode($result);
    exit;
}
