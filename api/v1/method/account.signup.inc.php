<?php

/*!
 * https://racconsquare.com
 * racconsquare@gmail.com
 *
 * Copyright 2012-2022 Demyanchuk Dmitry (racconsquare@gmail.com)
 */


if (!defined("APP_SIGNATURE")) {
    header("Location: /");
    exit;
}

if (!empty($_POST)) {
    $appType = isset($_POST['appType']) ? $_POST['appType'] : 2; // 2 = APP_TYPE_ANDROID
    
    $uid = isset($_POST['uid']) ? $_POST['uid'] : '';
    $oauth_type = isset($_POST['oauth_type']) ? $_POST['oauth_type'] : 0;

    $username = isset($_POST['username']) ? $_POST['username'] : '';
    $fullname = isset($_POST['fullname']) ? $_POST['fullname'] : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $email = isset($_POST['email']) ? $_POST['email'] : '';

    $profilePhoto = isset($_POST['profilePhoto']) ? $_POST['profilePhoto'] : '';
    $image1 = isset($_POST['image1']) ? $_POST['image1'] : '';
    $image2 = isset($_POST['image2']) ? $_POST['image2'] : '';
    $image3 = isset($_POST['image3']) ? $_POST['image3'] : '';
    $image4 = isset($_POST['image4']) ? $_POST['image4'] : '';
    $image5 = isset($_POST['image5']) ? $_POST['image5'] : '';
    $image6 = isset($_POST['image6']) ? $_POST['image6'] : '';
    $image7 = isset($_POST['image7']) ? $_POST['image7'] : '';
    $image8 = isset($_POST['image8']) ? $_POST['image8'] : '';
    $image9 = isset($_POST['image9']) ? $_POST['image9'] : '';
    $image10 = isset($_POST['image10']) ? $_POST['image10'] : '';
    $image11 = isset($_POST['image11']) ? $_POST['image11'] : '';

    $user_gender = isset($_POST['gender']) ? $_POST['gender'] : 2;
    $u_age = isset($_POST['age']) ? $_POST['age'] : 0;
    $interests = isset($_POST['interests']) ? $_POST['interests'] : "";

    $appType = helper::clearInt($appType);

    $user_gender = helper::clearInt($user_gender);
    $u_age = helper::clearInt($u_age);
    $interests = helper::clearText($interests);
    $interests = helper::escapeText($interests);

    $uid = helper::clearText($uid);
    $uid = helper::escapeText($uid);
    $oauth_type = helper::clearInt($oauth_type);

    $username = helper::clearText($username);
    $fullname = helper::clearText($fullname);
    $password = helper::clearText($password);
    $email = helper::clearText($email);
    $profilePhoto = trim($profilePhoto);
    $image1 = trim($image1);
    $image2 = trim($image2);
    $image3 = trim($image3);
    $image4 = trim($image4);
    $image5 = trim($image5);
    $image6 = trim($image6);
    $image7 = trim($image7);
    $image8 = trim($image8);
    $image9 = trim($image9);
    $image10 = trim($image10);
    $image11 = trim($image11);

    $username = helper::escapeText($username);
    $fullname = helper::escapeText($fullname);
    $password = helper::escapeText($password);
    $email = helper::escapeText($email);


    $result = array("error" => true,
    "error_description" => "");
    if ($profilePhoto == "") {
        $result['error_description'] = "Profile photo is not selected";
        echo json_encode($result);
        exit;
    }

    $contents = file_get_contents($profilePhoto);
    if (!$contents) {
        $result['error_description'] = "Selected another profile photo";
        echo json_encode($result);
        exit;
    }

    function saveImage($dbo, $imglib, $image, $accountId) {
        if ($image != "") {
            $contents = file_get_contents($image);
            if ($contents) {
                $extension = "jpg";
                $new_file_name = TEMP_PATH.helper::generateHash(32).".".$extension;
                $temp_file_name = TEMP_PATH.helper::generateHash(32).".".$extension;
                file_put_contents($new_file_name, $contents);
                $response = $imglib->createMyImage($new_file_name, $temp_file_name);
                if (!$response['error']) {
                    $gallery = new gallery($dbo);
                    $gallery->setRequestFrom($accountId);
                    $gallery->add(0, "", $response['normalImageUrl'], 0);
                }
            }
        }
    }

    $account = new account($dbo);
    $access_level = 1;
    $result = $account->signup($username, $fullname, $password, $email, $user_gender, $access_level, $u_age, $interests);
    unset($account);
    if (!$result['error']) {
        $account = new account($dbo);
        $account->setLastActive();
        $result = $account->signin($email, $password);

        if (!$result['error']) {
            $accountId = $result['accountId'];
            $account = new account($dbo, $accountId);
            $imglib = new imglib($dbo);

            $extension = strtolower("jpg");
            $new_file_name = TEMP_PATH.helper::generateHash(32).".".$extension;
            $temp_file_name = TEMP_PATH.helper::generateHash(32).".".$extension;
            file_put_contents($new_file_name, $contents);

            if (file_exists($new_file_name)) {
                $result = $imglib->newProfilePhoto($new_file_name, $temp_file_name);
                if (!$result['error']) {
                    // Delete old photos from server
                    $profile = new profile($dbo, $accountId);
                    $profileInfo = $profile->getVeryShort();
                    unset($profile);

                    @unlink(PROFILE_PHOTO_PATH."/".basename($profileInfo['bigPhotoUrl']));
                    unset($profileInfo);
                    // Set new images

                    $account->setPhoto($result);
                    if (auth::isSession()) {
                        auth::setCurrentUserPhotoUrl($result['bigPhotoUrl']);
                    }
                }
            }


            saveImage($dbo, $imglib, $image1, $accountId);
            saveImage($dbo, $imglib, $image2, $accountId);
            saveImage($dbo, $imglib, $image3, $accountId);
            saveImage($dbo, $imglib, $image4, $accountId);
            saveImage($dbo, $imglib, $image5, $accountId);
            saveImage($dbo, $imglib, $image6, $accountId);
            saveImage($dbo, $imglib, $image7, $accountId);
            saveImage($dbo, $imglib, $image8, $accountId);
            saveImage($dbo, $imglib, $image9, $accountId);
            saveImage($dbo, $imglib, $image10, $accountId);
            saveImage($dbo, $imglib, $image11, $accountId);

            $account->updateCounters();
            $result['error'] = false;
            $result['error_code'] = ERROR_SUCCESS;
            $result['error_description'] = "Account Registered";
        }
    }

    echo json_encode($result);
    exit;
}
