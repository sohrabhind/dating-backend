<?php

/*!
 * https://racconsquare.com
 * racconsquare@gmail.com
 *
 * Copyright 2012-2022 Demyanchuk Dmitry (racconsquare@gmail.com)
 */

if (!defined("APP_SIGNATURE")) {
    header("Location: /");
    exit;
}

if (!empty($_POST)) {

    $clientId = isset($_POST['clientId']) ? $_POST['clientId'] : 0;

    $hash = isset($_POST['hash']) ? $_POST['hash'] : '';

    $appType = isset($_POST['appType']) ? $_POST['appType'] : 2; // 2 = APP_TYPE_ANDROID
    $fcm_regId = isset($_POST['fcm_regId']) ? $_POST['fcm_regId'] : '';
    $lang = isset($_POST['lang']) ? $_POST['lang'] : '';

    $uid = isset($_POST['uid']) ? $_POST['uid'] : '';
    $oauth_type = isset($_POST['oauth_type']) ? $_POST['oauth_type'] : 0;

    $username = isset($_POST['username']) ? $_POST['username'] : '';
    $fullname = isset($_POST['fullname']) ? $_POST['fullname'] : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $email = isset($_POST['email']) ? $_POST['email'] : '';

    $photoUrl = isset($_POST['photo']) ? $_POST['photo'] : '';

    $user_gender = isset($_POST['gender']) ? $_POST['gender'] : 0;
    $user_year = isset($_POST['year']) ? $_POST['year'] : 2000;
    $user_month = isset($_POST['month']) ? $_POST['month'] : 1;
    $user_day = isset($_POST['day']) ? $_POST['day'] : 1;

    $u_age = isset($_POST['age']) ? $_POST['age'] : 0;

    $language = isset($_POST['language']) ? $_POST['language'] : '';

    $clientId = helper::clearInt($clientId);
    $appType = helper::clearInt($appType);

    $user_gender = helper::clearInt($user_gender);
    $user_year = helper::clearInt($user_year);
    $user_month = helper::clearInt($user_month);
    $user_day = helper::clearInt($user_day);

    $u_age = helper::clearInt($u_age);

    $uid = helper::clearText($uid);
    $uid = helper::escapeText($uid);
    $oauth_type = helper::clearInt($oauth_type);

    $username = helper::clearText($username);
    $fullname = helper::clearText($fullname);
    $password = helper::clearText($password);
    $email = helper::clearText($email);
    $photoUrl = helper::clearText($photoUrl);
    $language = helper::clearText($language);

    $username = helper::escapeText($username);
    $fullname = helper::escapeText($fullname);
    $password = helper::escapeText($password);
    $email = helper::escapeText($email);
    $photoUrl = helper::escapeText($photoUrl);
    $language = helper::escapeText($language);

    $lang = helper::clearText($lang);
    $lang = helper::escapeText($lang);

    $fcm_regId = helper::clearText($fcm_regId);
    $fcm_regId = helper::escapeText($fcm_regId);

    if ($clientId != CLIENT_ID) {
        api::printError(ERROR_CLIENT_ID, "Error client Id.");
    }

    if ($hash !== md5(md5($username).CLIENT_SECRET)) {
        api::printError(ERROR_CLIENT_SECRET, "Error hash.");
    }

    $result = array("error" => true);
    $account = new account($dbo);
    
    
    $result = $account->signup($username, $fullname, $password, $email, $user_gender, $user_year, $user_month, $user_day, $u_age, $language);
    unset($account);
    if (!$result['error']) {

        $account = new account($dbo);
        $account->setLastActive();
        $result = $account->signin($email, $password);
        unset($account);

        if (!$result['error']) {
            $auth = new auth($dbo);
            $result = $auth->create($result['accountId'], $clientId, $appType, $fcm_regId, $lang);

            if (!$result['error']) {
                $account = new account($dbo, $result['accountId']);
                if (strlen($uid) != 0) {
                    $helper = new helper($dbo);
                    switch ($oauth_type) {
                        case OAUTH_TYPE_GOOGLE: {
                            if ($helper->getUserIdByGoogle($uid) == 0) {
                                $account->setGoogleFirebaseId($uid);
                            }
                            break;
                        }
                        default: {
                            break;
                        }
                    }
                }

                $result['account'] = array();

                array_push($result['account'], $account->get());
            }
        }
    }

    echo json_encode($result);
    exit;
}
