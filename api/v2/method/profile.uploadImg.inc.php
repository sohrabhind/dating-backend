<?php

/*!
 * https://hindbyte.com
 * hindbyte@gmail.com
 *
 * Copyright 2012-2022 Demyanchuk Dmitry (hindbyte@gmail.com)
 */

if (!empty($_POST)) {

    $accountId = isset($_POST['accountId']) ? $_POST['accountId'] : 0;
    $accessToken = isset($_POST['accessToken']) ? $_POST['accessToken'] : '';

    $imgType = isset($_POST['imgType']) ? $_POST['imgType'] : 0;

    $auth = new auth($dbo);

    if (!$auth->authorize($accountId, $accessToken)) {

        api::printError(ERROR_ACCESS_TOKEN, "Error authorization.");
    }

    $result = array(
        "error" => true,
        "error_code" => ERROR_CODE_INITIATE,
        "error_description" => ''
    );

    $error = false;
    $error_code = ERROR_CODE_INITIATE;
    $error_description = "";

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

        if (!$error && !$imglib->isImageFile($_FILES['uploaded_file']['tmp_name'], true, false)) {

            $error = true;
            $error_code = ERROR_FILE_FORMAT;
            $error_description = 'Error file format';
        }

        if (!$error) {

//            $imgFilename_ext = pathinfo($_FILES['uploaded_file']['name'], PATHINFO_EXTENSION);
//            $imgNewName = helper::generateHash(7);

            $ext = pathinfo($_FILES['uploaded_file']['name'], PATHINFO_EXTENSION);
            $new_file_name = TEMP_PATH.sha1_file($_FILES['uploaded_file']['tmp_name']).".".$ext;

            @move_uploaded_file($_FILES['uploaded_file']['tmp_name'], $new_file_name);

            if (file_exists($new_file_name)) {

                switch ($imgType) {

                    case IMAGE_TYPE_PROFILE_PHOTO: {

                        $result = $imglib->newProfilePhoto($new_file_name);

                        if (!$result['error']) {

                            // Delete old photos from server
                            $profile = new profile($dbo, $accountId);
                            $profileInfo = $profile->getVeryShort();
                            unset($profile);

                            @unlink(PROFILE_PHOTO_PATH."/".basename($profileInfo['bigPhotoUrl']));
                            unset($profileInfo);
                            // Set new photos

                            $account = new account($dbo, $accountId);
                            $account->setPhoto($result);
                            unset($account);

                            if (auth::isSession()) {
                                auth::setCurrentUserPhotoUrl($result['bigPhotoUrl']);
                            }
                        }
                        break;
                    }

                    default: {

                        break;
                    }
                }

                $result['error'] = false;
                $result['error_code'] = ERROR_SUCCESS;
                $result['error_description'] = "ok.";
            }

            //$imglib->img_resize($_FILES['uploaded_file']['tmp_name'], $_SERVER['DOCUMENT_ROOT']."/tmp/".$imgNewName.".".$imgFilename_ext, 800, 0);



        } else {

            $result['error'] = $error;
            $result['error_code'] = $error_code;
            $result['error_description'] = $error_description;
        }

        unset($imglib);
    }

    echo json_encode($result);
}
