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

    if (!$auth->authorize(auth::getCurrentUserId(), auth::getAccessToken())) {

        header('Location: /');
        exit;
    }

    $chat_id = 0;
    $user_id = 0;

    if (!empty($_POST)) {

        $access_token = isset($_POST['access_token']) ? $_POST['access_token'] : '';

        $user_id = isset($_POST['user_id']) ? $_POST['user_id'] : 0;
        $chat_id = isset($_POST['chat_id']) ? $_POST['chat_id'] : 0;
        $message_id = isset($_POST['message_id']) ? $_POST['message_id'] : 0;

        $message_text = isset($_POST['message_text']) ? $_POST['message_text'] : "";
        $message_img = isset($_POST['message_img']) ? $_POST['message_img'] : "";

        $stickerId = isset($_POST['stickerId']) ? $_POST['stickerId'] : 0;
        $stickerImgUrl = isset($_POST['stickerImgUrl']) ? $_POST['stickerImgUrl'] : "";

        $user_id = helper::clearInt($user_id);
        $chat_id = helper::clearInt($chat_id);
        $message_id = helper::clearInt($message_id);

        $message_text = helper::clearText($message_text);

        $message_text = preg_replace( "/[\r\n]+/", "<br>", $message_text); //replace all new lines to one new line
        $message_text  = preg_replace('/\s+/', ' ', $message_text);        //replace all white spaces to one space

        $message_text = helper::escapeText($message_text);

        $message_img = helper::clearText($message_img);
        $message_img = helper::escapeText($message_img);

        $stickerId = helper::clearInt($stickerId);

        $stickerImgUrl = helper::clearText($stickerImgUrl);
        $stickerImgUrl = helper::escapeText($stickerImgUrl);

        $result = array(
            "error" => true,
            "error_code" => ERROR_CODE_INITIATE
        );

        if ($access_token != auth::getAccessToken()) {

            api::printError(ERROR_ACCESS_TOKEN, "Error authorization.");
        }

        if (auth::getCurrentLevelMode() == 0 && auth::getCurrentLevelMessagesCount() == 0) {

            $result['promode'] = false;
            $result['app_title'] = APP_NAME;;

            $account = new account($dbo, auth::getCurrentUserId());
            $account->setLevelMessagesCount(0);
            unset($account);

            echo json_encode($result);
            exit;
        }

        $profile = new profile($dbo, $user_id);
        $profile->setRequestFrom(auth::getCurrentUserId());

        $profileInfo = $profile->get();

        if ($profileInfo['state'] != ACCOUNT_STATE_ENABLED) {

            echo json_encode($result);
            exit;
        }

        if ($profileInfo['allowMessages'] == 0 && !$profileInfo['friend']) {

            echo json_encode($result);
            exit;
        }

        if (!$profileInfo['inBlackList']) {
            if (auth::getCurrentLevelMode() == 0 && auth::getCurrentLevelMessagesCount() > 0) {
                auth::setCurrentLevelMessagesCount(auth::getCurrentLevelMessagesCount() - 1);
            }

            $messages = new msg($dbo);
            $messages->setRequestFrom(auth::getCurrentUserId());

            $result = $messages->create($user_id, $chat_id, $message_text, $message_img, 0, 0, 0, $stickerId, $stickerImgUrl);

            $chat_id = $result['chatId'];

            if ($chat_id != 0) {

                $result = $messages->getNextMessages($result['chatId'], $message_id);

                if (count($result['messages']) > 0) {

                    $profile = new profile($dbo, $user_id);
                    $profileInfo = $profile->getVeryShort();
                    unset($profile);

                    ob_start();

                    foreach ($result['messages'] as $key => $value) {

                        draw::messageItem($value, $profileInfo, $LANG, $helper);
                    }

                    $result['html'] = ob_get_clean();
                    $result['items_all'] = $messages->messagesCountByChat($chat_id);
                    $result['chat_id'] = $chat_id;
                }
            }
        }

        echo json_encode($result);
        exit;
    }
