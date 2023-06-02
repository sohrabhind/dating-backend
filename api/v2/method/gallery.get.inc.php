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

    $profileId = isset($_POST['profileId']) ? $_POST['profileId'] : 0;
    $itemId = isset($_POST['itemId']) ? $_POST['itemId'] : 0;

    
    $accountId = helper::clearInt($accountId);

    $profileId = helper::clearInt($profileId);
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

    $accessMode = false;

    if ($accountId == $profileId) $accessMode = true;

    $result = $images->get($itemId, $profileId, $accessMode, 20);
    echo json_encode($result);
    exit;
}
