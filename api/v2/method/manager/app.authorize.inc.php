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

    $accountId = helper::clearInt($accountId);

    $result = array(
        "error" => true,
        "error_code" => ERROR_CODE_INITIATE
    );

    $admin = new admin($dbo);
    $admin->setId($accountId);

    if (!$admin->authorize($accountId, $accessToken)) {

        api::printError(ERROR_ACCESS_TOKEN, "Error authorization.");
    }

    $result = array(
        "error" => false,
        "error_code" => ERROR_SUCCESS,
        "accessToken" => $accessToken,
        "accountId" => $accountId,
        "account" => array()
    );

    $result['account'] = $admin->get();

    echo json_encode($result);
    exit;
}
