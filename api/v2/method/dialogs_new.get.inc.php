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

    $messageCreateAt = isset($_POST['messageCreateAt']) ? $_POST['messageCreateAt'] : 0;

    
    $accountId = helper::clearInt($accountId);

    $messageCreateAt = helper::clearInt($messageCreateAt);

    $result = array("error" => true,
                    "error_code" => ERROR_CODE_INITIATE);

//    $auth = new auth($dbo);
//
//    if (!$auth->authorize($accountId, $accessToken)) {
//
//        api::printError(ERROR_ACCESS_TOKEN, "Error authorization.");
//    }

    $messages = new msg($dbo);
    $messages->setRequestFrom($accountId);

    $result = $messages->getDialogs_new($messageCreateAt);

    echo json_encode($result);
    exit;
}
