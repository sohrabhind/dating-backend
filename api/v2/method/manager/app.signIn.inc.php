<?php

/*!
 * https://hindbyte.com
 * hindbyte@gmail.com
 *
 * Copyright 2012-2021 Demyanchuk Dmitry (hindbyte@gmail.com)
 */;

if (!defined("APP_SIGNATURE")) {
    header("Location: /");
    exit;
}

if (!empty($_POST)) {

    $clientId = isset($_POST['clientId']) ? $_POST['clientId'] : 0;

    $appType = isset($_POST['appType']) ? $_POST['appType'] : 0; // 0 = APP_TYPE_MANAGER
    $fcm_regId = isset($_POST['fcm_regId']) ? $_POST['fcm_regId'] : '';
    $lang = isset($_POST['lang']) ? $_POST['lang'] : '';

    $username = isset($_POST['username']) ? $_POST['username'] : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    $clientId = helper::clearInt($clientId);
    $appType = helper::clearInt($appType);

    $lang = helper::clearText($lang);
    $lang = helper::escapeText($lang);

    $username = helper::clearText($username);
    $username = helper::escapeText($username);

    $password = helper::clearText($password);
    $password = helper::escapeText($password);

    $fcm_regId = helper::clearText($fcm_regId);
    $fcm_regId = helper::escapeText($fcm_regId);

    if ($clientId != CLIENT_ID) {

        api::printError(ERROR_CLIENT_ID, "Error client Id.");
    }

    $access_data = array();

    $admin = new admin($dbo);
    $access_data = $admin->signin($username, $password);

    if (!$access_data["error"]) {

        $admin->setId($access_data['accountId']);

        $access_data = $admin->createAuthorization($access_data['accountId'], $clientId, $appType, $fcm_regId, $lang);

        if (!$access_data['error']) {

            $access_data['account'] = $admin->get();
        }
    }

    echo json_encode($access_data);
    exit;
}
