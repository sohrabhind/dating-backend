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

    $error = false;

    if (!empty($_POST)) {

        $token = isset($_POST['authenticity_token']) ? $_POST['authenticity_token'] : '';

        $password = isset($_POST['pswd']) ? $_POST['pswd'] : '';

        $password = helper::clearText($password);
        $password = helper::escapeText($password);

        if (auth::getAuthenticityToken() !== $token) {

            $error = true;
        }

        if (!$error) {

            // Deactivate Account

            $account = new account($dbo, $accountId);

            $result = $account->deactivation($password);

            if (!$result['error']) {

                // Remove All Medias

                $gallery = new gallery($dbo);
                $gallery->setRequestFrom($accountId);
                $gallery->removeAll();
                unset($gallery);

                $result = array();

                // Remove Avatar

                $photos = array(
                        "error" => false,
                        "originPhotoUrl" => "",
                        "normalPhotoUrl" => "",
                        "bigPhotoUrl" => "",
                        "lowPhotoUrl" => "");

                $account->setPhoto($photos);

                // Unset Facebook Id

                $account->setFacebookId("");

                // Unset Email

                $account->setEmail("");

                header("Location: /account/logout?access_token=".auth::getAccessToken());
                exit;
            }
        }

        header("Location: /account/settings/deactivation?error=true");
        exit;
    }

    auth::newAuthenticityToken();

    $page_id = "settings_deactivation";

    $css_files = array("main.css", "my.css");
    $page_title = $LANG['page-profile-deactivation']." | ".APP_TITLE;

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

                        <h1><?php echo $LANG['page-profile-deactivation']; ?></h1>

                        <form accept-charset="UTF-8" action="/account/settings/deactivation" autocomplete="off" class="edit_user" id="settings-form" method="post">

                            <input autocomplete="off" type="hidden" name="authenticity_token" value="<?php echo auth::getAuthenticityToken(); ?>">

                            <div class="tabbed-content m-0">

                                <div class="tab-container">
                                    <nav class="tabs">
                                        <a href="/account/settings"><span class="tab"><?php echo $LANG['page-profile-settings']; ?></span></a>
                                        <a href="/account/settings/privacy"><span class="tab"><?php echo $LANG['page-privacy-settings']; ?></span></a>
                                        <a href="/account/balance"><span class="tab"><?php echo $LANG['page-balance']; ?></span></a>
                                        <a href="/account/settings/services"><span class="tab"><?php echo $LANG['label-services']; ?></span></a>
                                        <a href="/account/settings/password"><span class="tab"><?php echo $LANG['label-password']; ?></span></a>
                                        <a href="/account/settings/blacklist"><span class="tab"><?php echo $LANG['page-blacklist']; ?></span></a>
                                        <a href="/account/settings/otp"><span class="tab"><?php echo $LANG['page-otp']; ?></span></a>
                                        <a href="/account/settings/deactivation"><span class="tab active"><?php echo $LANG['page-deactivate-account']; ?></span></a>
                                    </nav>
                                </div>

                                <div class="warning-container">
                                    <ul>
                                        <?php echo $LANG['page-profile-deactivation-sub-title']; ?>
                                    </ul>
                                </div>

                                <?php

                                if ( isset($_GET['error']) ) {

                                    ?>

                                    <div class="errors-container" style="margin-top: 15px;">
                                        <ul>
                                            <?php echo $LANG['msg-error-deactivation']; ?>
                                        </ul>
                                    </div>

                                    <?php
                                }
                                ?>

                                <div class="tab-pane active form-table">

                                    <div class="profile-basics form-row">
                                        <div class="form-cell left">
                                            <p class="info"><?php echo $LANG['label-settings-deactivation-sub-title']; ?></p>
                                        </div>

                                        <div class="form-cell">
                                            <input id="pswd" name="pswd" placeholder="<?php echo $LANG['label-password']; ?>" maxlength="32" type="password" value="">
                                        </div>
                                    </div>

                                </div>

                            </div>

                            <input style="margin-top: 25px" name="commit" class="red" type="submit" value="<?php echo $LANG['action-deactivation-profile']; ?>">

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