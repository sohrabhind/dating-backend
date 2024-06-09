<?php

;



if (!empty($_POST)) {

    $appType = isset($_POST['appType']) ? $_POST['appType'] : 2; // 2 = APP_TYPE_ANDROID
    $fcm_regId = isset($_POST['fcm_regId']) ? $_POST['fcm_regId'] : '';
    
    $email = isset($_POST['email']) ? $_POST['email'] : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    $appType = helper::clearInt($appType);

    $email = helper::clearText($email);
    $email = helper::escapeText($email);

    $password = helper::clearText($password);
    $password = helper::escapeText($password);

    $fcm_regId = helper::clearText($fcm_regId);
    $fcm_regId = helper::escapeText($fcm_regId);

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
                $access_data = $auth->create($access_data['accountId'], $appType, $fcm_regId);

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
