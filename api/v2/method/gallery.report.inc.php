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
    $abuseId = isset($_POST['abuseId']) ? $_POST['abuseId'] : 0;

    $description = isset($_POST['description']) ? $_POST['description'] : '';

    
    $accountId = helper::clearInt($accountId);

    $itemId = helper::clearInt($itemId);
    $abuseId = helper::clearInt($abuseId);

    $description = helper::clearText($description);
    $description = helper::escapeText($description);

    $result = array(
        "error" => true,
        "error_code" => ERROR_CODE_INITIATE
    );

    $auth = new auth($dbo);

    if (!$auth->authorize($accountId, $accessToken)) {

        api::printError(ERROR_ACCESS_TOKEN, "Error authorization.");
    }

    $reports = new reports($dbo);
    $reports->setRequestFrom($accountId);
    $result = $reports->add(REPORT_TYPE_GALLERY_ITEM, $itemId, $abuseId, $description);

    echo json_encode($result);
    exit;
}
