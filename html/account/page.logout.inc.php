<?php

    /*!
     * ifsoft.co.uk
     *
     * http://ifsoft.com.ua, http://ifsoft.co.uk
     * hindbyte@gmail.com
     *
     * Copyright 2012-2019 Demyanchuk Dmitry (hindbyte@gmail.com)
     */

    if (!$auth->authorize(auth::getCurrentUserId(), auth::getAccessToken())) {

        header('Location: /');
        exit;
    }

    if (isset($_GET['access_token'])) {

        $accessToken = (isset($_GET['access_token'])) ? ($_GET['access_token']) : '';
        $continue = (isset($_GET['continue'])) ? ($_GET['continue']) : '/';

        if (auth::getAccessToken() === $accessToken) {

            $account = new account($dbo);
            $account->logout(auth::getCurrentUserId(), auth::getAccessToken());
            $account->setLastActive();

            auth::unsetSession();
            auth::clearCookie();

            header('Location: '.$continue);
            exit;
        }
    }

    header('Location: /');

?>