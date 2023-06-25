<?php

    
    if (!admin::isSession()) {

        header('Location: /');
        exit;
    }

    if (isset($_GET['access_token'])) {

        $accessToken = (isset($_GET['access_token'])) ? ($_GET['access_token']) : '';
        $continue = (isset($_GET['continue'])) ? ($_GET['continue']) : '/';

        if (admin::getAccessToken() === $accessToken) {

            $admins = new admin($dbo);
            $admins->removeAuthorization(admin::getCurrentAdminId(), admin::getAccessToken());
            unset($admins);

            admin::unsetSession();

            header('Location: '.$continue);
            exit;
        }
    }

    header('Location: /');
    exit;