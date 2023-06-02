<?php



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

    $result['level'] = $level;
    $result = $account->setLevel($level);
    $levelMessages = 0;
    if ($level == 1) {
        $levelMessages = 1000;
    } elseif ($level == 2) {
        $levelMessages = 5000;
    } elseif ($level == 3) {
        $levelMessages = 10000;
    }
    $result = $account->setLevelMessagesCount($account->getLevelMessagesCount() + $levelMessages);
        
    $payments = new payments($dbo);
    $payments->setRequestFrom($accountId);
    $payments->create(PA_BUY_LEVEL, $paymentType, $level, $amount);
    unset($payments);
        
    echo json_encode($result);
    exit;
}
