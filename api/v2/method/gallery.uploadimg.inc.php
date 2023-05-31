<?php

/*!
 * ifsoft.co.uk
 *
 * http://ifsoft.com.ua, https://ifsoft.co.uk, https://hindbyte.com
 * hindbyte@gmail.com
 *
 * Copyright 2012-2020 Demyanchuk Dmitry (hindbyte@gmail.com)
 */

$result = array(
    "error" => true,
    "error_code" => ERROR_CODE_INITIATE,
    "error_description" => '');

$error = false;
$error_code = ERROR_CODE_INITIATE;
$error_description = "";

if (!empty($_POST)) {

    $accountId = isset($_POST['accountId']) ? $_POST['accountId'] : 0;
    $accessToken = isset($_POST['accessToken']) ? $_POST['accessToken'] : '';

    $accessMode = isset($_POST['accessMode']) ? $_POST['accessMode'] : 0;

    $itemType = isset($_POST['itemType']) ? $_POST['itemType'] : 0;

    $comment = isset($_POST['comment']) ? $_POST['comment'] : "";

    $accountId = helper::clearInt($accountId);

    $accessMode = helper::clearInt($accessMode);
    $itemType = helper::clearInt($itemType);

    $comment = helper::clearText($comment);

    $comment = preg_replace("/[\r\n]+/", "<br>", $comment); //replace all new lines to one new line
    $comment  = preg_replace('/\s+/', ' ', $comment);        //replace all white spaces to one space

    $comment = helper::escapeText($comment);

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

        $imglib = new imglib($dbo);

        if (!$error) {
            $extension = strtolower(pathinfo($_FILES['uploaded_file']['name'], PATHINFO_EXTENSION));
            if ($extension == "jpg" || $extension == "jpeg" || $extension == "png" || $extension == "gif") {

                $new_file_name = TEMP_PATH.helper::generateHash(32).".".$extension;
                $temp_file_name = TEMP_PATH.helper::generateHash(32).".".$extension;
                @move_uploaded_file($_FILES['uploaded_file']['tmp_name'], $new_file_name);

                if (file_exists($new_file_name)) {
                    $response = $imglib->createMyImage($new_file_name, $temp_file_name);
                    if (!$response['error']) {
                        $gallery = new gallery($dbo);
                        $gallery->setRequestFrom($accountId);
                        $gallery->add($accessMode, $comment, $response['normalImageUrl'], $itemType);

                        $result['error'] = false;
                        $result['error_code'] = ERROR_SUCCESS;
                        $result['error_description'] = "OK.";
                        $result['normalImageUrl'] = $response['normalImageUrl'];
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
    }

    echo json_encode($result);
    exit;
}
