<?php

    /*!
     * ifsoft.co.uk
     *
     * http://ifsoft.com.ua, https://ifsoft.co.uk
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
    }

    $chat_id = 0;
    $user_id = 0;

    if (!empty($_POST)) {

        $access_token = isset($_POST['access_token']) ? $_POST['access_token'] : '';

        $user_id = isset($_POST['user_id']) ? $_POST['user_id'] : 0;
        $chat_id = isset($_POST['chat_id']) ? $_POST['chat_id'] : 0;
        $message_id = isset($_POST['message_id']) ? $_POST['message_id'] : 0;

        $user_id = helper::clearInt($user_id);
        $chat_id = helper::clearInt($chat_id);
        $message_id = helper::clearInt($message_id);

        $result = array(
            "error" => true,
            "error_code" => ERROR_CODE_INITIATE
        );

        if ($access_token != auth::getAccessToken()) {

            api::printError(ERROR_ACCESS_TOKEN, "Error authorization.");
        }

        $messages = new msg($dbo);
        $messages->setRequestFrom(auth::getCurrentUserId());

        $result = $messages->getNextMessages($chat_id, $message_id);

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
        }

        echo json_encode($result);
        exit;
    }
