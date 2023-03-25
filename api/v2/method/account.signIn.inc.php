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

    $appType = isset($_POST['appType']) ? $_POST['appType'] : 2; // 2 = APP_TYPE_ANDROID
    $fcm_regId = isset($_POST['fcm_regId']) ? $_POST['fcm_regId'] : '';
    $lang = isset($_POST['lang']) ? $_POST['lang'] : '';

    $email = isset($_POST['email']) ? $_POST['email'] : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    $clientId = helper::clearInt($clientId);
    $appType = helper::clearInt($appType);

    $lang = helper::clearText($lang);
    $lang = helper::escapeText($lang);

    $email = helper::clearText($email);
    $email = helper::escapeText($email);

    $password = helper::clearText($password);
    $password = helper::escapeText($password);

    $fcm_regId = helper::clearText($fcm_regId);
    $fcm_regId = helper::escapeText($fcm_regId);

    if ($clientId != CLIENT_ID) {

        api::printError(ERROR_CLIENT_ID, "Error client Id.");
    }

    $access_data = array();

    $account = new account($dbo);
    $access_data = $account->signin($email, $password);

    unset($account);

    if (!$access_data["error"]) {

        $account = new account($dbo, $access_data['accountId']);

        switch ($account->getState()) {

            case ACCOUNT_STATE_BLOCKED: {

                break;
            }

            case ACCOUNT_STATE_DISABLED: {

                break;
            }

            default: {

                $auth = new auth($dbo);
                $access_data = $auth->create($access_data['accountId'], $clientId, $appType, $fcm_regId, $lang);

                if (!$access_data['error']) {

                    $account = new account($dbo, $access_data['accountId']);
                    $account->setState(ACCOUNT_STATE_ENABLED);
                    $account->setLastActive();

                    $access_data['account'] = array();
                    array_push($access_data['account'], $account->get());
                }

                break;
            }
        }
    }

    echo json_encode($access_data);
    exit;
}
