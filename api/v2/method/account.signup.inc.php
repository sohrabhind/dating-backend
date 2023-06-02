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

    $appType = isset($_POST['appType']) ? $_POST['appType'] : 2; // 2 = APP_TYPE_ANDROID
    $fcm_regId = isset($_POST['fcm_regId']) ? $_POST['fcm_regId'] : '';

    $uid = isset($_POST['uid']) ? $_POST['uid'] : '';
    $oauth_type = isset($_POST['oauth_type']) ? $_POST['oauth_type'] : 0;

    $username = isset($_POST['username']) ? $_POST['username'] : '';
    $fullname = isset($_POST['fullname']) ? $_POST['fullname'] : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $email = isset($_POST['email']) ? $_POST['email'] : '';

    $photoUrl = isset($_POST['photo']) ? $_POST['photo'] : '';

    $user_gender = isset($_POST['gender']) ? $_POST['gender'] : 0;

    $u_age = isset($_POST['age']) ? $_POST['age'] : 0;
    $interests = isset($_POST['interests']) ? $_POST['interests'] : "";

    $appType = helper::clearInt($appType);

    $user_gender = helper::clearInt($user_gender);
    $u_age = helper::clearInt($u_age);
    $interests = helper::clearText($interests);
    $interests = helper::escapeText($interests);

    $uid = helper::clearText($uid);
    $uid = helper::escapeText($uid);
    $oauth_type = helper::clearInt($oauth_type);

    $username = helper::clearText($username);
    $fullname = helper::clearText($fullname);
    $password = helper::clearText($password);
    $email = helper::clearText($email);
    $photoUrl = helper::clearText($photoUrl);

    $username = helper::escapeText($username);
    $fullname = helper::escapeText($fullname);
    $password = helper::escapeText($password);
    $email = helper::escapeText($email);
    $photoUrl = helper::escapeText($photoUrl);

    $fcm_regId = helper::clearText($fcm_regId);
    $fcm_regId = helper::escapeText($fcm_regId);

    $result = array("error" => true);
    $account = new account($dbo);
    
    $access_level = 0;
    $result = $account->signup($username, $fullname, $password, $email, $user_gender, $access_level, $u_age, "", $interests);
    unset($account);
    if (!$result['error']) {

        $account = new account($dbo);
        $account->setLastActive();
        $result = $account->signin($email, $password);
        unset($account);

        if (!$result['error']) {
            $auth = new auth($dbo);
            $result = $auth->create($result['accountId'], $appType, $fcm_regId);

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
