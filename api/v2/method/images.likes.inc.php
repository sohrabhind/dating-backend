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

    $itemId = isset($_POST['itemId']) ? $_POST['itemId'] : 0;
    $likeId = isset($_POST['likeId']) ? $_POST['likeId'] : 0;

    
    $accountId = helper::clearInt($accountId);

    $itemId = helper::clearInt($itemId);
    $likeId = helper::clearInt($likeId);

    $result = array("error" => true,
                    "error_code" => ERROR_CODE_INITIATE);

    $auth = new auth($dbo);

    if (!$auth->authorize($accountId, $accessToken)) {

        api::printError(ERROR_ACCESS_TOKEN, "Error authorization.");
    }

    $images = new images($dbo);
    $images->setRequestFrom($accountId);

    $result = $images->getLikers($itemId, $likeId);

    echo json_encode($result);
    exit;
}
