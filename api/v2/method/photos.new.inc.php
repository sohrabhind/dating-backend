<?php

/*!
 * ifsoft.co.uk
 *
 * http://ifsoft.com.ua, http://ifsoft.co.uk, https://racconsquare.com
 * racconsquare@gmail.com
 *
 * Copyright 2012-2019 Demyanchuk Dmitry (racconsquare@gmail.com)
 */

if (!empty($_POST)) {

    $clientId = isset($_POST['clientId']) ? $_POST['clientId'] : 0;

    $accountId = isset($_POST['accountId']) ? $_POST['accountId'] : 0;
    $accessToken = isset($_POST['accessToken']) ? $_POST['accessToken'] : '';

    $accessMode = isset($_POST['accessMode']) ? $_POST['accessMode'] : 0;

    $itemType = isset($_POST['itemType']) ? $_POST['itemType'] : 0;
    $itemShowInStream = isset($_POST['itemShowInStream']) ? $_POST['itemShowInStream'] : 1;

    $comment = isset($_POST['comment']) ? $_POST['comment'] : "";
    $imgUrl = isset($_POST['imgUrl']) ? $_POST['imgUrl'] : "";

    $clientId = helper::clearInt($clientId);
    $accountId = helper::clearInt($accountId);

    $accessMode = helper::clearInt($accessMode);
    $itemType = helper::clearInt($itemType);
    $itemShowInStream = helper::clearInt($itemShowInStream);

    $comment = helper::clearText($comment);

    $comment = preg_replace( "/[\r\n]+/", "<br>", $comment); //replace all new lines to one new line
    $comment  = preg_replace('/\s+/', ' ', $comment);        //replace all white spaces to one space

    $comment = helper::escapeText($comment);

    $imgUrl = helper::clearText($imgUrl);
    $imgUrl = helper::escapeText($imgUrl);

    $result = array("error" => true,
                    "error_code" => ERROR_CODE_INITIATE);

    $auth = new auth($dbo);

    if (!$auth->authorize($accountId, $accessToken)) {

        api::printError(ERROR_ACCESS_TOKEN, "Error authorization.");
    }

    $photos = new photos($dbo);
    $photos->setRequestFrom($accountId);

    $result = $photos->add($accessMode, $comment, $imgUrl, $itemType, $itemShowInStream);

    echo json_encode($result);
    exit;
}
