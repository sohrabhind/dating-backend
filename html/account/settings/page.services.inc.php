<?php

    /*!
     * https://hindbyte.com
     * hindbyte@gmail.com
     *
     * Copyright 2012-2022 Demyanchuk Dmitry (hindbyte@gmail.com)
     */

    if (!auth::isSession()) {

        header('Location: /');
        exit;
    }

    include_once('sys/config/gconfig.inc.php');

	$error = false;
    $error_message = '';

    $account = new account($dbo, auth::getCurrentUserId());
    $gl_id = $account->getGoogleFirebaseId();

    if (!empty($_POST)) {

    }

	$page_id = "services";

    $css_files = array("main.css", "my.css");
    $page_title = $LANG['page-services']." | ".APP_TITLE;

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

                        <h1><?php echo $LANG['page-services']; ?></h1>

                        <div class="tab-container">
                            <nav class="tabs">
                                <a href="/account/settings"><span class="tab"><?php echo $LANG['page-profile-settings']; ?></span></a>
                                <a href="/account/settings/privacy"><span class="tab"><?php echo $LANG['page-privacy-settings']; ?></span></a>
                                <a href="/account/balance"><span class="tab"><?php echo $LANG['page-balance']; ?></span></a>
                                <a href="/account/settings/services"><span class="tab active"><?php echo $LANG['label-services']; ?></span></a>
                                <a href="/account/settings/password"><span class="tab"><?php echo $LANG['label-password']; ?></span></a>
                                <a href="/account/settings/blacklist"><span class="tab"><?php echo $LANG['page-blacklist']; ?></span></a>
                                <a href="/account/settings/otp"><span class="tab"><?php echo $LANG['page-otp']; ?></span></a>
                                <a href="/account/settings/deactivation"><span class="tab"><?php echo $LANG['page-deactivate-account']; ?></span></a>
                            </nav>
                        </div>

                        <?php

                        $msg = $LANG['page-services-sub-title'];

                        if (isset($_GET['bio'])) {

                            switch($_GET['bio']) {

                                case "connected": {

                                    $msg = $LANG['label-services-facebook-connected'];
                                    break;
                                }

                                case "g_connected": {

                                    $msg = $LANG['label-services-google-connected'];
                                    break;
                                }

                                case "error": {

                                    $msg = $LANG['label-services-facebook-error'];
                                    break;
                                }

                                case "g_error": {

                                    $msg = $LANG['label-services-google-error'];
                                    break;
                                }

                                case "disconnected": {

                                    $msg = $LANG['label-services-facebook-disconnected'];
                                    break;
                                }

                                case "g_disconnected": {

                                    $msg = $LANG['label-services-google-disconnected'];
                                    break;
                                }

                                default: {

                                    $msg = $LANG['page-services-sub-title'];
                                    break;
                                }
                            }
                        }
                        ?>

                        <div class="warning-container">
                            <ul>
                                <?php echo $msg; ?>
                            </ul>
                        </div>

                        <header class="top-banner mt-2" style="padding: 0">

                            <div class="info">
                                <h1>Google</h1>

                                <?php

                                if (strlen($gl_id) != 0) {

                                    ?>
                                        <p><?php echo $LANG['label-connected-with-google']; ?></p>
                                    <?php
                                }
                                ?>

                            </div>

                            <div class="prompt">

                                <?php

                                if (strlen($gl_id) == 0) {

                                    ?>
                                        <a class="button green" href="<?php echo $google_client->createAuthUrl(); ?>"><?php echo $LANG['action-connect-google']; ?></a>
                                    <?php

                                } else {

                                    ?>
                                        <a class="button red" onclick="disconnect()"><?php echo $LANG['action-disconnect']; ?></a>
                                    <?php
                                }
                                ?>

                            </div>

                        </header>

                    </div>


                </div>
            </div>
        </div>


    </div>

        <?php

            include_once("html/common/site_footer.inc.php");
        ?>

        <script>

            function disconnect() {

                $.ajax({
                    type: 'POST',
                    url: "/api/" + options.api_version + "/method/account.google",
                    data: 'account_id=' + account.id + '&access_token=' + account.accessToken + '&action=disconnect',
                    dataType: 'json',
                    timeout: 30000,
                    success: function(response) {

                        if (response.hasOwnProperty('error')) {

                            if (!response.error) {

                                window.location = "/account/settings/services?bio=g_disconnected";
                            }
                        }
                    },
                    error: function(xhr, type){


                    }
                });
            }

        </script>

</body>
</html>