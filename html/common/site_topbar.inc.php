<?php

    /*!
     * ifsoft.co.uk
     *
     * http://ifsoft.com.ua, http://ifsoft.co.uk, https://hindbyte.com
     * hindbyte@gmail.com
     *
     * Copyright 2012-2020 Demyanchuk Dmitry (hindbyte@gmail.com)
     */


    if (!auth::isSession()) {

        ?>

        <div class="top-header">
            <div class="container">
                <div class="d-flex">

                    <a class="logo" href="/">
                        <img class="header-brand-img" src="/assets/img/logo.png" alt="<?php echo APP_NAME; ?>>" title="<?php echo APP_TITLE; ?>">
                    </a>


                </div>
            </div>
        </div>

        <?php

    }

    if (!isset($_COOKIE['privacy'])) {

        ?>
            <div class="header-message">
                <div class="wrap">
                    <p class="message"><?php echo $LANG['label-cookie-message']; ?> <a href="/terms"><?php echo $LANG['page-terms']; ?></a></p>
                </div>

                <button class="close-message-button close-privacy-message">×</button>
            </div>
        <?php
    }
?>