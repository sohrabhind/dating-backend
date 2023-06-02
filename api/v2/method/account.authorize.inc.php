<?php

/*!
 * https://hindbyte.com
 * hindbyte@gmail.com
 *
 * Copyright 2012-2022 Demyanchuk Dmitry (hindbyte@gmail.com)
 */



if (!empty($_POST)) {

    $accountId = isset($_POST['accountId']) ? $_POST['accountId'] : '';
    $accessToken = isset($_POST['accessToken']) ? $_POST['accessToken'] : '';

    $accountId = helper::clearInt($accountId);

    $result = array("error" => true);

    $auth = new auth($dbo);

    if (!$auth->authorize($accountId, $accessToken)) {

        api::printError(ERROR_ACCESS_TOKEN, "Error authorization.");
    }

    $account = new account($dbo, $accountId);
    $account->setLastActive();

    $result = array(
        "error" => false,
        "error_code" => ERROR_SUCCESS,
        "accessToken" => $accessToken,
        "accountId" => $accountId,
        "account" => array()
    );

    array_push($result['account'], $account->get());

    echo json_encode($result);
    exit;
}
