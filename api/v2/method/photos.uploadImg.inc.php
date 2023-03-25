<?php

    /*!
     * ifsoft.co.uk
     *
     * http://ifsoft.com.ua, http://ifsoft.co.uk
     * hindbyte@gmail.com
     *
     * Copyright 2012-2019 Demyanchuk Dmitry (hindbyte@gmail.com)
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

            if (!$error && !$imglib->isImageFile($_FILES['uploaded_file']['tmp_name'], true, false)) {

                $error = true;
                $error_code = ERROR_FILE_FORMAT;
                $error_description = 'Error file format';
            }

            if (!$error) {

                $response = $imglib->createMyPhoto($_FILES['uploaded_file']['tmp_name'], $_FILES['uploaded_file']['name']);

                if (!$response['error']) {

                    $result['error'] = false;
                    $result['error_code'] = ERROR_SUCCESS;
                    $result['error_description'] = "ok.";
                    $result['originPhotoUrl'] = $response['originPhotoUrl'];
                    $result['normalPhotoUrl'] = $response['normalPhotoUrl'];
                    $result['previewPhotoUrl'] = $response['previewPhotoUrl'];
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
