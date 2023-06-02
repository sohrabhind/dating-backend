<?php

/*!
 * ifsoft.co.uk
 *
 * http://ifsoft.com.ua, http://ifsoft.co.uk
 * hindbyte@gmail.com
 *
 * Copyright 2012-2020 Demyanchuk Dmitry (hindbyte@gmail.com)
 */

if (!empty($_POST)) {

    

    $accountId = isset($_POST['accountId']) ? $_POST['accountId'] : 0;
    $accessToken = isset($_POST['accessToken']) ? $_POST['accessToken'] : '';

    $itemId = isset($_POST['itemId']) ? $_POST['itemId'] : 0;
    $limit = isset($_POST['limit']) ? $_POST['limit'] : 100;

    
    $accountId = helper::clearInt($accountId);

    $itemId = helper::clearInt($itemId);
    $limit = helper::clearInt($limit);

    $result = array(
        "error" => true,
        "error_code" => ERROR_CODE_INITIATE
    );

    $auth = new auth($dbo);

    if (!$auth->authorize($accountId, $accessToken)) {

        api::printError(ERROR_ACCESS_TOKEN, "Error authorization.");
    }

    $stickers = new sticker($dbo);
    $stickers->setRequestFrom($accountId);

    $result = $stickers->db_get($itemId, $limit);

    echo json_encode($result);
    exit;
}
