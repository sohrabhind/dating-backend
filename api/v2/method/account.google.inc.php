<?php

/*!
 * https://hindbyte.com
 * hindbyte@gmail.com
 *
 * Copyright 2012-2022 Demyanchuk Dmitry (hindbyte@gmail.com)
 */

if (!defined("APP_SIGNATURE")) {

    header("Location: /");
    exit;
}

if (!empty($_POST)) {

    

    $account_id = isset($_POST['account_id']) ? $_POST['account_id'] : 0;
    $access_token = isset($_POST['access_token']) ? $_POST['access_token'] : '';

    $app_type = isset($_POST['app_type']) ? $_POST['app_type'] : 0; // 0 = APP_TYPE_UNKNOWN
    $fcm_regId = isset($_POST['fcm_regId']) ? $_POST['fcm_regId'] : '';
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    
    $uid = isset($_POST['uid']) ? $_POST['uid'] : '';
    $email = isset($_POST['email']) ? $_POST['email'] : '';

    $app_type = helper::clearInt($app_type);

    $action = helper::clearText($action);
    $action = helper::escapeText($action);

    $fcm_regId = helper::clearText($fcm_regId);
    $fcm_regId = helper::escapeText($fcm_regId);

    $uid = helper::clearText($uid);
    $uid = helper::escapeText($uid);

    
    $email = helper::clearText($email);
    $email = helper::escapeText($email);

    $result = array(
        "error" => true,
        "error_code" => ERROR_CODE_INITIATE
    );

    $helper = new helper($dbo);
    $auth = new auth($dbo);

    switch ($action) {
        case 'connect': {
            //
            if (!$auth->authorize($account_id, $access_token)) {
                api::printError(ERROR_ACCESS_TOKEN, "Error authorization.");
            }

            if ($helper->getUserIdByGoogle($uid) != 0) {
                $result = array(
                    "error" => true,
                    "error_code" => ERROR_FACEBOOK_ID_TAKEN
                );
            } else {
                $account = new account($dbo, $account_id);
                $account->setGoogleFirebaseId($uid);
                unset($account);
                $result = array(
                    "error" => false,
                    "error_code" => ERROR_SUCCESS
                );
            }
            break;
        }

        case 'disconnect': {
            if (!$auth->authorize($account_id, $access_token)) {
                api::printError(ERROR_ACCESS_TOKEN, "Error authorization.");
            }

            $account = new account($dbo, $account_id);
            $account->setGoogleFirebaseId("");
            unset($account);

            $result = array(
                "error" => false,
                "error_code" => ERROR_SUCCESS
            );
            break;
        }
        default: {
            $account_id = $helper->getUserIdByGoogle($uid);
            if ($account_id == 0) {
                $account_id = $helper->getUserIdByEmail($email);
                if ($account_id != 0) {
                    $account = new account($dbo, $account_id);
                    $account->setGoogleFirebaseId($uid);
                    unset($account);
                }
            }
            if ($account_id != 0) {
                $account = new account($dbo, $account_id);
                $account_info = $account->get();
                if ($account_info['state'] == ACCOUNT_STATE_ENABLED) {

                    $auth = new auth($dbo);
                    $result = $auth->create($account_id, $app_type, $fcm_regId);
                    if (!$result['error']) {

                        $account->setLastActive();
                        $result['account'] = array();

                        array_push($result['account'], $account_info);

                        if ($app_type == APP_TYPE_WEB) {

                            auth::setSession($result['accountId'], $account_info['username'], $account_info['fullname'], $account_info['bigPhotoUrl'], $account_info['balance'], $account_info['level'], $account_info['level_messages_count'], 0, $result['accessToken']);
                            auth::updateCookie($account_info['username'], $result['accessToken']);
                        }
                    }
                }

            } else {

                if ($app_type == APP_TYPE_WEB) {

                    $_SESSION['oauth'] = 'google';
                    $_SESSION['uid'] = $uid;
                    $_SESSION['fullname'] = "";
                    $_SESSION['email'] = "";
                    $_SESSION['oauth_link'] = "";
                }
            }

            break;
        }
    }

    echo json_encode($result);
    exit;
}
