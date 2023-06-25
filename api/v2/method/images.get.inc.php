<?php



if (!empty($_POST)) {

    

    $accountId = isset($_POST['accountId']) ? $_POST['accountId'] : 0;
    $accessToken = isset($_POST['accessToken']) ? $_POST['accessToken'] : '';

    $itemId = isset($_POST['itemId']) ? $_POST['itemId'] : 0;

    
    $accountId = helper::clearInt($accountId);

    $itemId = helper::clearInt($itemId);

    $result = array("error" => true,
                    "error_code" => ERROR_CODE_INITIATE);

    $auth = new auth($dbo);

    if (!$auth->authorize($accountId, $accessToken)) {

        api::printError(ERROR_ACCESS_TOKEN, "Error authorization.");
    }

    $images = new images($dbo);
    $images->setRequestFrom($accountId);

    $itemInfo = $images->info($itemId);

    if (!$itemInfo['error']) {

        if ($itemInfo['fromUserId'] != $accountId && $itemInfo['removeAt'] != 0) {

            exit;
        }

        $result = array("error" => false,
                        "error_code" => ERROR_SUCCESS,
                        "itemId" => $itemId,
                        "items" => array());
        array_push($result['items'], $itemInfo);
    }

    echo json_encode($result);
    exit;
}
