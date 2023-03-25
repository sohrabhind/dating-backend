<?php

    /*!
     * ifsoft.co.uk
     *
     * http://ifsoft.com.ua, https://ifsoft.co.uk, https://hindbyte.com
     * hindbyte@gmail.com
     *
     * Copyright 2012-2020 Demyanchuk Dmitry (hindbyte@gmail.com)
     */

    if (!admin::isSession()) {

        header("Location: /admin/login");
        exit;
    }

    $stats = new stats($dbo);
    $admin = new admin($dbo);

    $itemId = 0;
    $itemInfo = array();

    if (isset($_GET['id'])) {

        $itemId = isset($_GET['id']) ? $_GET['id'] : 0;
        $accessToken = isset($_GET['access_token']) ? $_GET['access_token'] : '';
        $fromUserId = isset($_GET['fromUserId']) ? $_GET['fromUserId'] : 0;

        $itemId = helper::clearInt($itemId);
        $fromUserId = helper::clearInt($fromUserId);

        if ($accessToken === admin::getAccessToken() && !APP_DEMO) {

            $gallery = new gallery($dbo);
            $gallery->setRequestFrom($fromUserId);
            $gallery->remove($itemId);
            unset($gallery);

            $reports = new reports($dbo);
            $reports->remove(REPORT_TYPE_GALLERY_ITEM, $itemId);
            unset($reports);
        }

    } else {

        header("Location: /admin/main");
        exit;
    }
