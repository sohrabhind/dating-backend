<?php

    /*!
     * https://hindbyte.com
     * hindbyte@gmail.com
     *
     * Copyright 2012-2022 Demyanchuk Dmitry (hindbyte@gmail.com)
     */

    if (!defined("APP_SIGNATURE")) {

        header("Location: /");
        exit;
    }

    if (auth::isSession()) {

        header("Location: /account/find");
    }

    include('sys/config/gconfig.inc.php');

    require_once 'html/recaptcha/autoload.php';

    $user_username = '';

    $error = false;
    $error_message = array();

    if (!empty($_POST)) {

        $user_username = isset($_POST['user_username']) ? $_POST['user_username'] : '';
        $user_password = isset($_POST['user_password']) ? $_POST['user_password'] : '';
        $token = isset($_POST['authenticity_token']) ? $_POST['authenticity_token'] : '';
        $recaptcha_token = isset($_POST['recaptcha_token']) ? $_POST['recaptcha_token'] : '';

        $user_username = helper::clearText($user_username);
        $user_password = helper::clearText($user_password);

        $user_username = helper::escapeText($user_username);
        $user_password = helper::escapeText($user_password);

        // Google Recaptcha

        $recaptcha = new \ReCaptcha\ReCaptcha(RECAPTCHA_SECRET_KEY);
        $resp = $recaptcha->verify($recaptcha_token, $_SERVER['REMOTE_ADDR']);

        if (!$resp->isSuccess()){

            $error = true;
            $error_message[] = "Google Recaptcha error";
        }

        if (auth::getAuthenticityToken() !== $token) {

            $error = true;
        }

        if (!$error) {

            $access_data = array();

            $account = new account($dbo);

            $access_data = $account->signin($user_username, $user_password);

            unset($account);

            if (!$access_data['error']) {

                $account = new account($dbo, $access_data['accountId']);
                $accountInfo = $account->get();

                $account_fullname = $accountInfo['fullname'];
                $account_photo_url = $accountInfo['lowPhotoUrl'];
                $account_balance = $accountInfo['balance'];
                $account_pro_mode = $accountInfo['pro'];
                $account_free_messages_count = $accountInfo['free_messages_count'];

                //print_r($accountInfo);

                switch ($accountInfo['state']) {

                    case ACCOUNT_STATE_BLOCKED: {

                        break;
                    }

                    default: {

                        $account->setState(ACCOUNT_STATE_ENABLED);

                        $clientId = 0; // Desktop version

                        $auth = new auth($dbo);
                        $access_data = $auth->create($accountInfo['id'], $clientId, APP_TYPE_WEB, "", $LANG['lang-code']);

                        if (!$access_data['error']) {

                            auth::setSession($access_data['accountId'], $user_username, $account_fullname, $account_photo_url, $account_balance, $account_pro_mode, $account_free_messages_count, $account->getAccessLevel($access_data['accountId']), $access_data['accessToken']);
                            auth::updateCookie($user_username, $access_data['accessToken']);

                            unset($_SESSION['oauth']);
                            unset($_SESSION['uid']);
                            unset($_SESSION['fullname']);
                            unset($_SESSION['email']);
                            unset($_SESSION['oauth_link']);

                            $account->setLastActive();

                            header("Location: /");
                        }
                    }
                }

            } else {

                $error = true;
            }
        }
    }

    auth::newAuthenticityToken();

    $page_id = "main";

    $css_files = array("landing.css", "my.css");
    $page_title = APP_TITLE;

    include_once("html/common/site_header.inc.php");

?>

<body class="home" id="main-page">

    <?php

        include_once("html/common/site_topbar.inc.php");
    ?>

    <div class="content-page">

        <div class="limiter">
            <div class="container-login100">
                <div class="wrap-login100">

                    <form accept-charset="UTF-8" action="/" class="custom-form login100-form" id="login-form" method="post">

                        <input autocomplete="off" type="hidden" name="authenticity_token" value="<?php echo helper::getAuthenticityToken(); ?>">

                        <span class="login100-form-title "><?php echo $LANG['page-login']; ?></span>

                        <?php

                        if (FACEBOOK_AUTHORIZATION) {

                            ?>

                            <p>
                                <a class="fb-icon-btn fb-btn-large btn-facebook" href="/facebook/login">
                                    <span class="icon-container">
                                        <i class="icon icon-facebook"></i>
                                    </span>
                                    <span><?php echo $LANG['action-login-with']." ".$LANG['label-facebook']; ?></span>
                                </a>
                            </p>
                            <?php
                        }

                        if (GOOGLE_AUTHORIZATION) {

                            ?>
                            <p>
                                <a class="gl-icon-btn gl-btn-large btn-google" href="<?php echo $google_client->createAuthUrl(); ?>">
                                        <span class="icon-container">
                                            <i class="icon icon-google"></i>
                                        </span>
                                    <span><?php echo $LANG['action-signup-with'] . " " . $LANG['label-google']; ?></span>
                                </a>
                            </p>
                            <?php
                        }

                        ?>

                        <div class="errors-container" style="<?php if (!$error) echo "display: none"; ?>">
                            <p class="title"><?php echo $LANG['label-errors-title']; ?></p>
                            <ul>
                                <li><?php echo $LANG['msg-error-authorize']; ?></li>
                            </ul>
                        </div>

                        <input id="username" name="user_username" placeholder="<?php echo $LANG['label-username']; ?>" required="required" size="30" type="text" value="<?php echo $user_username; ?>">
                        <input id="password" name="user_password" placeholder="<?php echo $LANG['label-password']; ?>" required="required" size="30" type="password" value="">

                        <div class="login-button">
                            <input style="margin-right: 10px" class="submit-button button red" name="commit" type="submit" value="<?php echo $LANG['action-login']; ?>">
                            <a href="/remind" class="help"><?php echo $LANG['action-forgot-password']; ?></a>
                        </div>
                    </form>

                    <div class="login100-more">
                        <div class="login100_content">
                            <h1 class="mb-10"><?php echo $LANG['main-page-prompt-login']; ?></h1>
                            <p><?php echo sprintf($LANG['main-page-sub-prompt-login'], "<strong>".APP_TITLE."</strong>"); ?></p>
                        </div>
                    </div>

                </div>

            </div>

            <?php

                if (strlen(GOOGLE_PLAY_LINK) != 0) {

                    ?>
                        <div class="wrap-landing-info-container">

                            <div class="wrap-landing-info">
                                <?php echo sprintf($LANG['label-prompt-app'], APP_TITLE, APP_TITLE) ?>
                                <a href="<?php echo GOOGLE_PLAY_LINK; ?>" target="_blank" rel="nofollow">
                                    <img class="mt-4" width="170" src="/assets/img/google_play.png">
                                </a>
                            </div>
                        </div>
                    <?php
                }

                $app = new app($dbo);
                $result = $app->getPreviewProfiles(6);
                unset($app);

                if (count($result['items']) > 2) {

                    ?>
                        <div class="wrap-landing-info-container mt-5">

                            <div class="wrap-landing-info">

                                <?php

                                    foreach ($result['items'] as $key => $value) {

                                        ?>
                                        <span class="avatar" style="background-image: url('<?php echo $value['photoUrl']; ?>')"></span>
                                        <?php
                                    }
                                ?>

                            </div>

                        </div>
                    <?php
                }
            ?>

            <?php

                include_once("html/common/site_footer.inc.php");
            ?>

            <script>

                $('#login-form').submit(function(event) {

                    event.preventDefault();

                    grecaptcha.ready(function() {
                        grecaptcha.execute('<?php echo RECAPTCHA_SITE_KEY; ?>', {action: 'submit'}).then(function(token) {

                            $('#login-form').prepend('<input type="hidden" name="recaptcha_token" value="'+ token + '">');
                            $('#login-form').unbind('submit').submit();
                        });
                    });
                });
            </script>

        </div>


    </div>



</body
</html>