<?php

$result = array("error" => true,
                "error_code" => ERROR_CODE_INITIATE,
                "error_description" => '');

$error = false;
$error_code = ERROR_CODE_INITIATE;
$error_description = "";

if (!empty($_POST)) {

    $accountId = isset($_POST['accountId']) ? $_POST['accountId'] : 0;

    $accessToken = isset($_POST['accessToken']) ? $_POST['accessToken'] : '';

    $profileId = isset($_POST['profileId']) ? $_POST['profileId'] : 0;

    $chatFromUserId = isset($_POST['chatFromUserId']) ? $_POST['chatFromUserId'] : 0;
    $chatToUserId = isset($_POST['chatToUserId']) ? $_POST['chatToUserId'] : 0;

    $chatId = isset($_POST['chatId']) ? $_POST['chatId'] : 0;
    $messageText = isset($_POST['messageText']) ? $_POST['messageText'] : "";

    $listId = isset($_POST['listId']) ? $_POST['listId']


    $accountId = helper::clearInt($accountId);

    $profileId = helper::clearInt($profileId);

    $chatFromUserId = helper::clearInt($chatFromUserId);
    $chatToUserId = helper::clearInt($chatToUserId);

    $chatId = helper::clearInt($chatId);

    $listId = helper::clearInt($listId);

    $messageText = helper::clearText($messageText);

    $messageText = preg_replace("/[\r\n]+/", "<br>", $messageText); //replace all new lines to one new line
    $messageText  = preg_replace('/\s+/', ' ', $messageText);        //replace all white spaces to one space

    $messageText = helper::escapeText($messageText);


    $auth = new auth($dbo);

    if (!$auth->authorize($accountId, $accessToken)) {

        api::printError(ERROR_ACCESS_TOKEN, "Error authorization.");
    }


    if (!empty($_FILES['uploaded_file']['name'])) {

        switch ($_FILES['uploaded_file']['error']) {

            case UPLOAD_ERR_OK:

                break;

            case UPLOAD_ERR_NO_FILE:

                $error = true;
                $error_code = ERROR_UPLOAD_NO_FILE;
                $error_description = 'No file sent.'; // No file sent.

                break;

            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:

                $error = true;
                $error_code = ERROR_UPLOAD_FILE_SIZE;
                $error_description = "Exceeded file size limit.";

                break;

            default:

                $error = true;
                $error_code = ERROR_CODE_INITIATE;
                $error_description = 'Unknown error.';
        }

        
        $profile = new profile($dbo, $profileId);
        $profile->setRequestFrom($accountId);

        $profileInfo = $profile->getShort();

        if ($profileInfo['state'] != ACCOUNT_STATE_ENABLED) {
            echo json_encode($result);
            exit;
        }

        if ($profileInfo['allowMessages'] == 0) {
            if (!$profileInfo['myFan']) {
                echo json_encode($result);
                exit;
            }
        }

        if ($profileInfo['inBlackList']) {
            echo json_encode($result);
            exit;
        }

        $account = new account($dbo, $accountId);
        $free_messages_count = $account->getFreeMessagesCount();
        $level_messages_count = $account->getLevelMessagesCount();
        $messages = new messages($dbo);
        $messages->setRequestFrom($accountId);

        if ($account->getGender() == 1) {
            $free_messages_count = 1;
        } else if (($free_messages_count == 0 || $messages->getMessagesFromUser($accountId, $profileId) > 0) && ($account->getLevel() == 0 || $level_messages_count == 0)) {
            $result = array(
                "error" => true,
                "error_code" => 402
            );
            echo json_encode($result);
            exit;
        }
        
        if (!$error) {
            $extension = strtolower(pathinfo($_FILES['uploaded_file']['name'], PATHINFO_EXTENSION));
            if ($extension == "jpg" || $extension == "jpeg" || $extension == "png" || $extension == "gif") {
                $new_file_name = TEMP_PATH.helper::generateHash(32).".".$extension;
                $temp_file_name = TEMP_PATH.helper::generateHash(32).".".$extension;
                move_uploaded_file($_FILES['uploaded_file']['tmp_name'], $new_file_name);

                if (file_exists($new_file_name)) {
                    $imglib = new imglib($dbo);
                    $response = $imglib->createChatImg($new_file_name, $temp_file_name);
                    if (!$response['error']) {
                        $result['error'] = false;
                        $result['error_code'] = ERROR_SUCCESS;
                        $result['error_description'] = "ok.";
                        $messageImg = $response['imgUrl'];

                        $result = $messages->create($profileId, $chatId, $messageText, $messageImg, $listId);
                    }
                }
            } else {
                $result['error'] = true;
            }

        } else {

            $result['error'] = $error;
            $result['error_code'] = $error_code;
            $result['error_description'] = $error_description;
        }


        unset($imglib);
    }

    echo json_encode($result);
    exit;
}
