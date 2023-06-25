<?php



if (!empty($_POST)) {

    

    $accountId = isset($_POST['accountId']) ? $_POST['accountId'] : 0;
    $accessToken = isset($_POST['accessToken']) ? $_POST['accessToken'] : '';

    $email = isset($_POST['email']) ? $_POST['email'] : "";
    $subject = isset($_POST['subject']) ? $_POST['subject'] : "";
    $detail = isset($_POST['detail']) ? $_POST['detail'] : "";

    
    $accountId = helper::clearInt($accountId);

    $email = helper::clearText($email);
    $email = helper::escapeText($email);

    $subject = helper::clearText($subject);
    $subject = helper::escapeText($subject);

    $detail = helper::clearText($detail);
    $detail = helper::escapeText($detail);

    $result = array("error" => true,
                    "error_code" => ERROR_CODE_INITIATE);

    $auth = new auth($dbo);

    if (!$auth->authorize($accountId, $accessToken)) {

        api::printError(ERROR_ACCESS_TOKEN, "Error authorization.");
    }

    $support = new support($dbo);
    $support->setRequestFrom($accountId);

    $result = $support->createTicket($accountId, $email, $subject, $detail);

    echo json_encode($result);
    exit;
}
