<?php

/*!
 * ifsoft.co.uk
 *
 * http://ifsoft.com.ua, https://ifsoft.co.uk, https://hindbyte.com
 * hindbyte@gmail.com
 *
 * Copyright 2012-2020 Demyanchuk Dmitry (hindbyte@gmail.com)
 */

if (!defined("APP_SIGNATURE")) {

    header("Location: /");
    exit;
}

if (!empty($_POST)) {

    $accountId = isset($_POST['accountId']) ? $_POST['accountId'] : 0;
    $accessToken = isset($_POST['accessToken']) ? $_POST['accessToken'] : '';

    $level = isset($_POST['level']) ? $_POST['level'] : 0;
    $paymentType = isset($_POST['paymentType']) ? $_POST['paymentType'] : 0;
    $amount = isset($_POST['amount']) ? $_POST['amount'] : 0;

    $level = helper::clearInt($level);
    $paymentType = helper::clearInt($paymentType);
    $amount = helper::clearInt($amount);

    $auth = new auth($dbo);

    if (!$auth->authorize($accountId, $accessToken)) {

        api::printError(ERROR_ACCESS_TOKEN, "Error authorization.");
    }

    $result = array(
        "error" => true,
        "error_code" => ERROR_CODE_INITIATE
    );

    $account = new account($dbo, $accountId);

    if (!$result['error']) {
        $result = $account->setLevel($level);
        $freeMessages = 0;
        if ($level == 1) {
            $freeMessages = 1000;
        } elseif ($level == 2) {
            $freeMessages = 5000;
        } elseif ($level == 3) {
            $freeMessages = 10000;
        }
        $result = $account->setFreeMessagesCount($account->getFreeMessagesCount() + $freeMessages);

        $result['level'] = $level;
        $payments = new payments($dbo);
        $payments->setRequestFrom($accountId);
        $payments->create(PA_BUY_LEVEL, $paymentType, $level, $amount);
        unset($payments);
    }
    echo json_encode($result);
    exit;
}
