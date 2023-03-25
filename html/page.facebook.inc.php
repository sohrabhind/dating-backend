<?php

    /*!
     * https://hindbyte.com
     * hindbyte@gmail.com
     *
     * Copyright 2012-2022 Demyanchuk Dmitry (hindbyte@gmail.com)
     */

    if (auth::isSession()) {

        header("Location: /");
        exit;
    }

    if (isset($_SESSION['oauth'])) {

        unset($_SESSION['oauth']);
        unset($_SESSION['uid']);
        unset($_SESSION['fullname']);
        unset($_SESSION['email']);
        unset($_SESSION['oauth_link']);
        unset($_SESSION['oauth_img_link']);

        header("Location: /signup");
        exit;
    }

    header("Location: /");