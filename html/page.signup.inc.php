<?php

    /*!
     * https://racconsquare.com
     * racconsquare@gmail.com
     *
     * Copyright 2012-2022 Demyanchuk Dmitry (racconsquare@gmail.com)
     */

    if (!defined("APP_SIGNATURE")) {

        header("Location: /");
        exit;
    }

    if (auth::isSession()) {

        header("Location: /");
        exit;
    }

    include_once('sys/config/gconfig.inc.php');

    require_once 'html/recaptcha/autoload.php';

    $user_username = '';
    $user_email = '';
    $user_fullname = '';
    $gender = 2;
    $age = 0;

    $error = false;
    $error_message = array();

    if (!empty($_POST)) {

        $error = false;

        $user_username = isset($_POST['username']) ? $_POST['username'] : '';
        $user_fullname = isset($_POST['fullname']) ? $_POST['fullname'] : '';
        $user_password = isset($_POST['password']) ? $_POST['password'] : '';
        $user_email = isset($_POST['email']) ? $_POST['email'] : '';
        $token = isset($_POST['authenticity_token']) ? $_POST['authenticity_token'] : '';
        $recaptcha_token = isset($_POST['recaptcha_token']) ? $_POST['recaptcha_token'] : '';

        $gender = isset($_POST['gender']) ? $_POST['gender'] : 2;
        $age = isset($_POST['age']) ? $_POST['age'] : 0;

        $user_username = helper::clearText($user_username);
        $user_fullname = helper::clearText($user_fullname);
        $user_password = helper::clearText($user_password);
        $user_email = helper::clearText($user_email);

        $user_username = helper::escapeText($user_username);
        $user_fullname = helper::escapeText($user_fullname);
        $user_password = helper::escapeText($user_password);
        $user_email = helper::escapeText($user_email);

        $gender = helper::clearInt($gender);
        $age = helper::clearInt($age);

        // Google Recaptcha

        $recaptcha = new \ReCaptcha\ReCaptcha(RECAPTCHA_SECRET_KEY);
        $resp = $recaptcha->verify($recaptcha_token, $_SERVER['REMOTE_ADDR']);

        if (!$resp->isSuccess()){

            $error = true;
            $error_message[] = "Google Recaptcha error";
        }

        if (auth::getAuthenticityToken() !== $token) {

            $error = true;
            $error_token = true;
            $error_message[] = $LANG['msg-error-unknown'];
        }

        if (!helper::isCorrectLogin($user_username)) {

            $error = true;
            $error_username = true;
            $error_message[] = $LANG['msg-login-incorrect'];
        }

        if ($helper->isUserExists($user_username)) {

            $error = true;
            $error_username = true;
            $error_message[] = $LANG['msg-login-taken'];
        }

        if (!helper::isCorrectFullname($user_fullname)) {

            $error = true;
            $error_fullname = true;
            $error_message[] = $LANG['msg-fullname-incorrect'];
        }

        if (!helper::isCorrectPassword($user_password)) {

            $error = true;
            $error_password = true;
            $error_message[] = $LANG['msg-password-incorrect'];
        }

        if (!helper::isCorrectEmail($user_email)) {

            $error = true;
            $error_email = true;
            $error_message[] = $LANG['msg-email-incorrect'];
        }

        if ($helper->isEmailExists($user_email)) {

            $error = true;
            $error_email = true;
            $error_message[] = $LANG['msg-email-taken'];
        }

        if ($age > 110 || $age < 18) {

            $error = true;
            $error_email = true;
            $error_message[] = $LANG['msg-age-incorrect'];
        }

        if ($gender > 2 || $gender < 0) {

            // 0 = male
            // 1 = female
            // 2 = secret

            $error = true;
            $error_email = true;
            $error_message[] = $LANG['msg-gender-incorrect'];
        }

        if (!$error) {

            $account = new account($dbo);

            $result = array();
            $result = $account->signup($user_username, $user_fullname, $user_password, $user_email, $gender, 2000, 1, 1, $age, $LANG['lang-code']);

            if (!$result['error']) {

                $clientId = 0; // Desktop version

                $auth = new auth($dbo);
                $access_data = $auth->create($result['accountId'], $clientId, APP_TYPE_WEB, "", $LANG['lang-code']);

                if (!$access_data['error']) {

                    auth::setSession($result['accountId'], $result['username'], $result['fullname'], "", $result['balance'], 0, $result['free_messages_count'], 0, $access_data['accessToken']);
                    auth::updateCookie($user_username, $access_data['accessToken']);

                    $language = $account->getLanguage();

                    $account->setLastActive();

                    //Facebook connect

                    if (isset($_SESSION['oauth'])) {

                        switch ($_SESSION['oauth']) {

                            case 'google': {

                                if ($helper->getUserIdByGoogle($_SESSION['uid']) == 0) {

                                    $account->setGoogleFirebaseId($_SESSION['uid']);
                                }

                                break;
                            }

                            default: {

                                // google signin

                                if ($helper->getUserIdByGoogle($_SESSION['uid']) == 0) {

                                    $account->setGoogleFirebaseId($_SESSION['uid']);
                                }
                            }
                        }
                    }

                    unset($_SESSION['oauth']);
                    unset($_SESSION['uid']);
                    unset($_SESSION['fullname']);
                    unset($_SESSION['email']);
                    unset($_SESSION['oauth_link']);

                    $_SESSION['welcome_block'] = true;

                    auth::closeAuthenticityToken();

                    header("Location: /".$user_username);
                    exit;

                } else {

                    auth::closeAuthenticityToken();
                }

            } else {

                $error = true;
                $error_message[] = "You can not create multi-accounts!";

                auth::closeAuthenticityToken();
            }
        }
    }

    if (isset($_SESSION['oauth']) && empty($user_username) && empty($user_email)) {

        $user_fullname = $_SESSION['fullname'];
        $user_email = $_SESSION['email'];
    }

    auth::newAuthenticityToken();

    $page_id = "signup";

    $css_files = array("my.css", "landing.css");
    $page_title = $LANG['page-signup']." | ".APP_TITLE;

    include_once("html/common/site_header.inc.php");
?>

<body class="home" id="signup-page">

    <?php

        include_once("html/common/site_topbar.inc.php");
    ?>

    <div class="content-page">

        <div class="limiter">

            <div class="container-login100 mb-5">

                <div class="wrap-login100">

                    <form accept-charset="UTF-8" action="/signup" class="custom-form login100-form" id="signup-form" method="post">

                        <input autocomplete="off" type="hidden" name="authenticity_token" value="<?php echo helper::getAuthenticityToken(); ?>">

                        <span class="login100-form-title "><?php echo $LANG['page-signup']; ?></span>

                        <?php

                        if (isset($_SESSION['oauth'])) {

                            ?>

                            <div class="opt-in mb-3">
                                    <?php

                                    if ($_SESSION['oauth'] === 'facebook') {

                                        $headers = get_headers('https://graph.facebook.com/'.$_SESSION['uid'].'/picture',1);
                                    }

                                    if (isset($headers) && isset($headers['Location'])) {

                                        $url = $headers['Location']; // string

                                        ?>

                                        <img src="<?php echo $url; ?>" alt="" style="width: 50px; float: left">

                                        <?php

                                    } else {

                                        if (isset($_SESSION['oauth_img_link']) && strlen($_SESSION['oauth_img_link']) != 0) {

                                            ?>
                                                <img src="<?php echo $_SESSION['oauth_img_link']; ?>" alt="" style="width: 50px; float: left">
                                            <?php

                                        } else {

                                            ?>
                                                <img src="\assets\img\profile_default_photo.png" alt="" style="width: 50px; float: left">
                                            <?php

                                        }
                                    }

                                    if ($_SESSION['oauth'] === 'facebook') {

                                        ?>
                                            <div style="padding-left: 60px;">
                                                <b><a target="_blank" href="https://www.facebook.com/app_scoped_user_id/<?php echo $_SESSION['uid']; ?>"><?php echo $_SESSION['fullname']; ?></a></b>
                                                <span><?php echo $LANG['label-authorization-with-facebook']; ?></span>
                                                <br>
                                                <a href="/facebook"><?php echo $LANG['action-back-to-default-signup']; ?></a>
                                            </div>

                                        <?php

                                    } else {

                                        ?>
                                            <div style="padding-left: 60px;">
                                                <b><?php echo $_SESSION['fullname']; ?></b><br>
                                                <span><?php echo $LANG['label-authorization-with-google']; ?></span>
                                                <br>
                                                <a href="/google"><?php echo $LANG['action-back-to-default-signup']; ?></a>
                                            </div>
                                        <?php
                                    }

                                    ?>


                                </label>
                            </div>

                            <?php

                        } else {

                            if (FACEBOOK_AUTHORIZATION) {

                                ?>

                                <p>
                                    <a class="fb-icon-btn fb-btn-large btn-facebook" href="/facebook/signup">
                                        <span class="icon-container">
                                            <i class="icon icon-facebook"></i>
                                        </span>
                                        <span><?php echo $LANG['action-signup-with'] . " " . $LANG['label-facebook']; ?></span>
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
                        }

                        ?>

                        <div class="errors-container" style="<?php if (!$error) echo "display: none"; ?>">
                            <p class="title"><?php echo $LANG['label-errors-title']; ?></p>
                            <ul>
                                <?php

                                foreach ($error_message as $key => $value) {

                                    echo "<li>{$value}</li>";
                                }
                                ?>
                            </ul>
                        </div>

                        <input id="username" name="username" placeholder="<?php echo $LANG['label-username']; ?>" required="required" size="30" type="text" value="<?php echo $user_username; ?>">
                        <input id="fullname" name="fullname" placeholder="<?php echo $LANG['label-fullname']; ?>" required="required" size="30" type="text" value="<?php echo $user_fullname; ?>">
                        <input id="password" name="password" placeholder="<?php echo $LANG['label-password']; ?>" required="required" size="30" type="password" value="">
                        <input id="email" name="email" placeholder="<?php echo $LANG['label-email']; ?>" required="required" size="48" type="text" value="<?php echo $user_email; ?>">

                        <div class="opt-in">
                            <select name="age" id="age" style="margin-bottom: 15px; width: 100%">

                                <option disabled value="0" <?php if ($age < 18) echo "selected=\"selected\""; ?>><?php echo $LANG['label-select-age']; ?></option>

                                <?php

                                for ($i = 18; $i <= 110; $i++) {

                                    if ($i == $age) {

                                        echo "<option value=\"$i\" selected=\"selected\">$i</option>";

                                    } else {

                                        echo "<option value=\"$i\">$i</option>";
                                    }
                                }
                                ?>

                            </select>
                        </div>

                        <div class="opt-in">
                            <select name="gender" id="gender" style="margin-bottom: 15px; width: 100%">
                                <option value="2" <?php if ($gender != SEX_FEMALE && $gender != SEX_MALE) echo "selected=\"selected\""; ?>><?php echo $LANG['gender-secret']; ?></option>
                                <option value="0" <?php if ($gender == SEX_MALE) echo "selected=\"selected\""; ?>><?php echo $LANG['gender-male']; ?></option>
                                <option value="1" <?php if ($gender == SEX_FEMALE) echo "selected=\"selected\""; ?>><?php echo $LANG['gender-female']; ?></option>
                            </select>
                        </div>


                        <div class="opt-in">
                            <label for="user_receive_digest">
                                <b><?php echo $LANG['label-signup-confirm']; ?></b>
                                <a style="font-size: 0.8rem;" href="/terms"><?php echo $LANG['page-terms']; ?></a>
                            </label>
                        </div>

                        <input class="submit-button red" type="submit" value="<?php echo $LANG['action-signup']; ?>">
                    </form>

                    <div class="login100-more">
                        <div class="login100_content">
                            <h1 class="mb-10"><?php echo sprintf($LANG['main-page-prompt-signup'], "<strong>".APP_TITLE."</strong>"); ?></h1>
                        </div>
                    </div>

                </div>
            </div>

            <?php

                include_once("html/common/site_footer.inc.php");
            ?>

            <script>

                $('#signup-form').submit(function(event) {

                    event.preventDefault();

                    grecaptcha.ready(function() {
                        grecaptcha.execute('<?php echo RECAPTCHA_SITE_KEY; ?>', {action: 'submit'}).then(function(token) {

                            $('#signup-form').prepend('<input type="hidden" name="recaptcha_token" value="'+ token + '">');
                            $('#signup-form').unbind('submit').submit();
                        });
                    });
                });
            </script>

        </div>
    </div>

</body>
</html>