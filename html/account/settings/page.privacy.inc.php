<?php

    /*!
     * ifsoft.co.uk
     *
     * http://ifsoft.com.ua, https://ifsoft.co.uk, https://hindbyte.com
     * hindbyte@gmail.com
     *
     * Copyright 2012-2020 Demyanchuk Dmitry (hindbyte@gmail.com)
     */


    if (!$auth->authorize(auth::getCurrentUserId(), auth::getAccessToken())) {

        header('Location: /');
        exit;
    }

    $accountId = auth::getCurrentUserId();

    $account = new account($dbo, $accountId);

    $error = false;
    $send_status = false;
    $fullname = "";

    if (auth::isSession()) {

        $ticket_email = "";
    }

    if (!empty($_POST)) {

        $token = isset($_POST['authenticity_token']) ? $_POST['authenticity_token'] : '';

        $allowMessages = isset($_POST['allowMessages']) ? $_POST['allowMessages'] : '';

        $allowShowMyGallery = isset($_POST['allowShowMyGallery']) ? $_POST['allowShowMyGallery'] : '';
        $allowShowMyInfo = isset($_POST['allowShowMyInfo']) ? $_POST['allowShowMyInfo'] : '';

        $allowShowMyFriends = isset($_POST['allowShowMyFriends']) ? $_POST['allowShowMyFriends'] : '';
        $allowShowMyLikes = isset($_POST['allowShowMyLikes']) ? $_POST['allowShowMyLikes'] : '';

        $allowMessages = helper::clearText($allowMessages);
        $allowMessages = helper::escapeText($allowMessages);

        $allowShowMyGallery = helper::clearText($allowShowMyGallery);
        $allowShowMyGallery = helper::escapeText($allowShowMyGallery);

        $allowShowMyInfo = helper::clearText($allowShowMyInfo);
        $allowShowMyInfo = helper::escapeText($allowShowMyInfo);

        $allowShowMyFriends = helper::clearText($allowShowMyFriends);
        $allowShowMyFriends = helper::escapeText($allowShowMyFriends);

        $allowShowMyLikes = helper::clearText($allowShowMyLikes);
        $allowShowMyLikes = helper::escapeText($allowShowMyLikes);

        if (auth::getAuthenticityToken() !== $token) {

            $error = true;
        }

        if (!$error) {

            if ($allowMessages === "on") {

                $account->setAllowMessages(1);

            } else {

                $account->setAllowMessages(0);
            }

            $privacy_settings = $account->getPrivacySettings();

            $privacy_likes = $privacy_settings['allowShowMyLikes'];
            $privacy_friends = $privacy_settings['allowShowMyFriends'];
            $privacy_gallery = $privacy_settings['allowShowMyGallery'];
            $privacy_info = $privacy_settings['allowShowMyInfo'];

            if ($allowShowMyGallery === "on") {

                $privacy_gallery = 1;

            } else {

                $privacy_gallery = 0;
            }

            if ($allowShowMyInfo === "on") {

                $privacy_info = 1;

            } else {

                $privacy_info = 0;
            }

            if ($allowShowMyFriends === "on") {

                $privacy_friends = 1;

            } else {

                $privacy_friends = 0;
            }

            if ($allowShowMyLikes === "on") {

                $privacy_likes = 1;

            } else {

                $privacy_likes = 0;
            }

            $account->setPrivacySettings($privacy_likes, $privacy_friends, $privacy_gallery, $privacy_info);

            header("Location: /account/settings/privacy?error=false");
            exit;
        }

        header("Location: /account/settings/privacy?error=true");
        exit;
    }

    $account->setLastActive();

    $accountInfo = $account->get();

    auth::newAuthenticityToken();

    $page_id = "settings_privacy";

    $css_files = array("main.css", "my.css");
    $page_title = $LANG['page-privacy-settings']." | ".APP_TITLE;

    include_once("html/common/site_header.inc.php");

?>

<body class="settings-page">

    <?php

        include_once("html/common/site_topbar.inc.php");
    ?>

    <div class="wrap content-page">

        <div class="main-column row">

            <?php

                include_once("html/common/site_sidenav.inc.php");
            ?>

            <div class="col-lg-9 col-md-12" id="content">

                <div class="main-content">

                    <div class="standard-page">

                        <h1><?php echo $LANG['page-privacy-settings']; ?></h1>

                        <div class="tab-container">
                            <nav class="tabs">
                                <a href="/account/settings"><span class="tab"><?php echo $LANG['page-profile-settings']; ?></span></a>
                                <a href="/account/settings/privacy"><span class="tab active"><?php echo $LANG['page-privacy-settings']; ?></span></a>
                                <a href="/account/balance"><span class="tab"><?php echo $LANG['page-balance']; ?></span></a>
                                <a href="/account/settings/services"><span class="tab"><?php echo $LANG['label-services']; ?></span></a>
                                <a href="/account/settings/password"><span class="tab"><?php echo $LANG['label-password']; ?></span></a>
                                <a href="/account/settings/blacklist"><span class="tab"><?php echo $LANG['page-blacklist']; ?></span></a>
                                <a href="/account/settings/otp"><span class="tab"><?php echo $LANG['page-otp']; ?></span></a>
                                <a href="/account/settings/deactivation"><span class="tab"><?php echo $LANG['page-deactivate-account']; ?></span></a>
                            </nav>
                        </div>

                        <form accept-charset="UTF-8" action="/account/settings/privacy" autocomplete="off" class="edit_user" id="settings-form" method="post">

                            <input autocomplete="off" type="hidden" name="authenticity_token" value="<?php echo auth::getAuthenticityToken(); ?>">

                            <div class="tabbed-content">

                                <?php

                                if ( isset($_GET['error']) ) {

                                    switch ($_GET['error']) {

                                        case "true" : {

                                            ?>

                                            <div class="errors-container" style="margin-top: 15px;">
                                                <ul>
                                                    <?php echo $LANG['msg-error-unknown']; ?>
                                                </ul>
                                            </div>

                                            <?php

                                            break;
                                        }

                                        default: {

                                            ?>

                                            <div class="success-container" style="margin-top: 15px;">
                                                <ul>
                                                    <b><?php echo $LANG['label-thanks']; ?></b>
                                                    <br>
                                                    <?php echo $LANG['label-settings-saved']; ?>
                                                </ul>
                                            </div>

                                            <?php

                                            break;
                                        }
                                    }
                                }
                                ?>

                                <div class="errors-container" style="margin-top: 15px; <?php if (!$error) echo "display: none"; ?>">
                                    <ul>
                                        <?php echo $LANG['ticket-send-error']; ?>
                                    </ul>
                                </div>

                                <div class="tab-pane active form-table">

                                    <div class="link-preference form-row">
                                        <div class="form-cell left">
                                            <h2><?php echo $LANG['label-messages-privacy']; ?></h2>
                                        </div>

                                        <div class="form-cell">
                                            <div class="opt-in">
                                                <input id="allowMessages" name="allowMessages" type="checkbox" <?php if ($accountInfo['allowMessages'] == 1) echo "checked=\"checked\""; ?>>
                                                <label for="allowMessages"><?php echo $LANG['label-messages-allow']; ?></label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="link-preference form-row">
                                        <div class="form-cell left">
                                            <h2><?php echo $LANG['label-gallery-privacy']; ?></h2>
                                        </div>

                                        <div class="form-cell">
                                            <div class="opt-in">
                                                <input id="allowShowMyGallery" name="allowShowMyGallery" type="checkbox" <?php if ($accountInfo['allowShowMyGallery'] == 1) echo "checked=\"checked\""; ?>>
                                                <label for="allowShowMyGallery"><?php echo $LANG['label-gallery-allow']; ?></label>
                                            </div>
                                        </div>
                                    </div>


                                    <div class="link-preference form-row">
                                        <div class="form-cell left">
                                            <h2><?php echo $LANG['label-info-privacy']; ?></h2>
                                        </div>

                                        <div class="form-cell">
                                            <div class="opt-in">
                                                <input id="allowShowMyInfo" name="allowShowMyInfo" type="checkbox" <?php if ($accountInfo['allowShowMyInfo'] == 1) echo "checked=\"checked\""; ?>>
                                                <label for="allowShowMyInfo"><?php echo $LANG['label-info-allow']; ?></label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="link-preference form-row">
                                        <div class="form-cell left">
                                            <h2><?php echo $LANG['label-friends-list-privacy']; ?></h2>
                                        </div>

                                        <div class="form-cell">
                                            <div class="opt-in">
                                                <input id="allowShowMyFriends" name="allowShowMyFriends" type="checkbox" <?php if ($accountInfo['allowShowMyFriends'] == 1) echo "checked=\"checked\""; ?>>
                                                <label for="allowShowMyFriends"><?php echo $LANG['label-friends-list-allow']; ?></label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="link-preference form-row">
                                        <div class="form-cell left">
                                            <h2><?php echo $LANG['label-likes-list-privacy']; ?></h2>
                                        </div>

                                        <div class="form-cell">
                                            <div class="opt-in">
                                                <input id="allowShowMyLikes" name="allowShowMyLikes" type="checkbox" <?php if ($accountInfo['allowShowMyLikes'] == 1) echo "checked=\"checked\""; ?>>
                                                <label for="allowShowMyLikes"><?php echo $LANG['label-likes-list-allow']; ?></label>
                                            </div>
                                        </div>
                                    </div>

                                </div>

                            </div>

                            <input style="margin-top: 25px" class="red" name="commit" type="submit" value="<?php echo $LANG['action-save']; ?>">

                        </form>
                    </div>


                </div>

            </div>

        </div>

    </div>


        <?php

            include_once("html/common/site_footer.inc.php");
        ?>

</body>
</html>