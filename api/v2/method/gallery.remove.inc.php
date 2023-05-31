<?php

/*!
 * ifsoft.co.uk
 *
 * http://ifsoft.com.ua, https://ifsoft.co.uk, https://rccoonsquare.com
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

    $gallery = new gallery($dbo);
    $gallery->setRequestFrom($accountId);

    $result = $gallery->remove($itemId);

    $reports = new reports($dbo);
    $reports->remove(REPORT_TYPE_GALLERY_ITEM, $itemId);
    unset($reports);

    echo json_encode($result);
    exit;
}
