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


                    <div class="d-flex align-items-center order-lg-2 ml-auto">

                        <?php

                        if (isset($page_id) && $page_id === "main") {

                            ?>

                            <span class="nav-label mr-2 d-md-block d-none"><?php echo $LANG['label-missing-account']; ?></span>

                            <div class="nav-item">
                                <a href="/signup" class="topbar-button" title="">
                                    <span class="new-item d-sm-inline-block"><?php echo $LANG['topbar-signup']; ?></span>
                                </a>
                            </div>

                            <?php

                        } else if (isset($page_id) && $page_id === "signup") {

                            ?>

                            <span class="nav-label mr-2 d-md-block d-none"><?php echo $LANG['label-existing-account']; ?></span>

                            <div class="nav-item">
                                <a href="/" class="topbar-button" title="">
                                    <span class="new-item d-sm-inline-block"><?php echo $LANG['topbar-signin']; ?></span>
                                </a>
                            </div>

                            <?php

                        } else {

                            ?>

                            <div class="nav-item">
                                <a href="/" class="topbar-button" title="">
                                    <span class="new-item d-sm-inline-block"><?php echo $LANG['topbar-signin']; ?></span>
                                </a>
                            </div>

                            <div class="nav-item">
                                <a href="/signup" class="topbar-button" title="">
                                    <span class="new-item d-sm-inline-block"><?php echo $LANG['topbar-signup']; ?></span>
                                </a>
                            </div>

                            <?php
                        }
                        ?>

                    </div>

                </div>
            </div>
        </div>

        <?php

    } else {

        ?>

        <div id="backdrop" class="sn-backdrop" style="opacity: 0;"></div>

        <div id="sidenav" class="sn-sidenav" style="transform: translate3d(-380px, 0px, 0px);">

            <div class="top-header" id="sn-topbar">
                <div class="container">

                    <div class="d-flex">

                        <div class="burger-icon d-block menu-toggle">
                            <div class="burger-container">
                                <span class="burger-bun-top"></span>
                                <span class="burger-filling"></span>
                                <span class="burger-bun-bot"></span>
                            </div>
                        </div>

                        <a class="logo" href="/">
                            <img class="header-brand-img" src="/assets/img/logo.png" alt="<?php echo APP_NAME; ?>" title="<?php echo APP_TITLE; ?>">
                        </a>
                    </div>
                </div>
            </div>

            <div class="sidenav-content">

            </div>

        </div>

        <div class="top-header" id="topbar">
            <div class="container">

                <div class="d-flex">

                    <div class="burger-icon d-block d-lg-none menu-toggle">
                        <div class="burger-container">
                            <span class="burger-bun-top"></span>
                            <span class="burger-filling"></span>
                            <span class="burger-bun-bot"></span>
                        </div>
                    </div>

                    <a class="logo" href="/">
                        <img class="header-brand-img" src="/assets/img/logo.png" alt="<?php echo APP_NAME; ?>" title="<?php echo APP_TITLE; ?>">
                    </a>

                    <div class="d-flex align-items-center order-lg-2 ml-auto">

                        <a class="nav-link py-2 icon" href="/<?php echo auth::getCurrentUserLogin(); ?>/friends">
                            <i class="icofont icofont-users"></i>
                            <span class="nav-unread hidden friends-badge"></span>
                        </a>

                        <a class="nav-link py-2 icon" href="/account/messages">
                            <i class="icofont icofont-ui-message"></i>
                            <span class="nav-unread hidden messages-badge"></span>
                        </a>

                        <a class="nav-link py-2 icon" href="/account/notifications">
                            <i class="icofont icofont-notification"></i>
                            <span class="nav-unread hidden notifications-badge"></span>
                        </a>

                        <div class="dropdown">

                            <a href="/<?php echo auth::getCurrentUserLogin(); ?>" class="nav-link pr-0 leading-none" data-toggle="dropdown">
                                <span class="avatar" style="background-image: url(<?php echo auth::getCurrentUserPhotoUrl(); ?>); background-position: center; background-size: cover;"></span>
                                <span class="ml-2 d-none d-lg-block profile-menu-nav-link">
                                    <span class="text-default"><?php echo auth::getCurrentUserFullname(); ?></span>
                                    <small class="text-muted d-block mb-1">@<?php echo auth::getCurrentUserLogin(); ?></small>
                                </span>
                            </a>

                            <div class="dropdown-menu dropdown-menu-right dropdown-menu-arrow">

                                <a class="dropdown-item" href="/<?php echo auth::getCurrentUserLogin(); ?>"><i class="dropdown-icon icofont icofont-ui-user"></i><?php echo $LANG['topbar-profile']; ?></a>

                                <a class="dropdown-item d-block d-md-none" href="/<?php echo auth::getCurrentUserLogin(); ?>/friends">
                                    <span class="float-right">
                                        <span class="badge badge-primary friends-badge friends-primary-badge"></span>
                                    </span>
                                    <i class="dropdown-icon icofont icofont-users"></i><?php echo $LANG['topbar-friends']; ?>
                                </a>

                                <a class="dropdown-item d-block d-md-none" href="/account/messages">
                                    <span class="float-right">
                                        <span class="badge badge-primary messages-badge messages-primary-badge"></span>
                                    </span>
                                    <i class="dropdown-icon icofont icofont-ui-message"></i><?php echo $LANG['topbar-messages']; ?>
                                </a>

                                <a class="dropdown-item d-block d-md-none" href="/account/notifications">
                                    <span class="float-right">
                                        <span class="badge badge-primary notifications-badge notifications-primary-badge"></span>
                                    </span>
                                    <i class="dropdown-icon icofont icofont-notification"></i><?php echo $LANG['topbar-notifications']; ?>
                                </a>

                                <a class="dropdown-item" href="/account/settings"><i class="dropdown-icon icofont icofont-gear-alt"></i><?php echo $LANG['topbar-settings']; ?></a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="/support"><i class="dropdown-icon icofont icofont-support"></i><?php echo $LANG['topbar-support']; ?></a>
                                <a class="dropdown-item" href="/account/logout?access_token=<?php echo auth::getAccessToken(); ?>&amp;continue=/"><i class="dropdown-icon icofont icofont-logout"></i><?php echo $LANG['topbar-logout']; ?></a>
                            </div>
                        </div>

                    </div>

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