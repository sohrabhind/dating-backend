<?php



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

        error_log(date('[Y-m-d H:i:s] ') . "Running");
        if (!$error) {
            $extension = strtolower(pathinfo($_FILES['uploaded_file']['name'], PATHINFO_EXTENSION));
            if ($extension == "jpg" || $extension == "jpeg" || $extension == "png" || $extension == "gif") {

                $new_file_name = TEMP_PATH.helper::generateHash(32).".".$extension;
                $temp_file_name = TEMP_PATH.helper::generateHash(32).".".$extension;
                move_uploaded_file($_FILES['uploaded_file']['tmp_name'], $new_file_name);

                if (file_exists($new_file_name)) {

                    switch ($imgType) {

                        case IMAGE_TYPE_PROFILE_PHOTO: {

                            $result = $imglib->newProfilePhoto($new_file_name, $temp_file_name);

                            if (!$result['error']) {

                                // Delete old photos from server
                                $profile = new profile($dbo, $accountId);
                                $profileInfo = $profile->getVeryShort();
                                unset($profile);

                                @unlink(PROFILE_PHOTO_PATH."/".basename($profileInfo['bigPhotoUrl']));
                                unset($profileInfo);
                                // Set new images

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
}
