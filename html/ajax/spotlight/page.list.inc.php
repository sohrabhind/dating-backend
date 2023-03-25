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

    if (!$auth::isSession()) {

        header('Location: /');
        exit;
    }

    $balance = auth::getCurrentUserBalance();

    $settings = new settings($dbo);
    $config = $settings->get();

    ?>
    <div class="row" style="border-bottom: 1px solid #dee2e6;">
        <div class="gallery-intro-header col-12 p-3 py-4 m-0">

            <h1 class="gallery-title"><?php echo $LANG['label-you-balance']; ?>: <span class="account-balance" data-balance="<?php echo $balance; ?>"><?php echo $balance; ?></span> <?php echo $LANG['label-credits']; ?></h1>

            <a class="add-button button green" href="/account/balance">
                <?php echo $LANG['action-buy']; ?>
            </a>

        </div>
    </div>

    <?php
