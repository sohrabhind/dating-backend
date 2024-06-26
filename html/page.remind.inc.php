<?php

   

    if (auth::isSession()) {

        header("Location: /");
        exit;
    }

    $email = '';

    $error = false;
    $error_message = array();
    $sent = false;

    if ( isset($_GET['sent']) ) {

        $sent = isset($_GET['sent']) ? $_GET['sent'] : 'false';

        if ($sent === 'success') {

            $sent = true;

        } else {

            $sent = false;
        }
    }

    if (!empty($_POST)) {

        $email = isset($_POST['email']) ? $_POST['email'] : '';
        $token = isset($_POST['authenticity_token']) ? $_POST['authenticity_token'] : '';
        $recaptcha_token = isset($_POST['recaptcha_token']) ? $_POST['recaptcha_token'] : '';

        $email = helper::clearText($email);
        $email = helper::escapeText($email);

        // Google Recaptcha

        $recaptcha = new \ReCaptcha\ReCaptcha(RECAPTCHA_SECRET_KEY);
        $resp = $recaptcha->verify($recaptcha_token, $_SERVER['REMOTE_ADDR']);

        if (!$resp->isSuccess()){

            $error = true;
            $error_message[] = "Google Recaptcha error";
        }

        if (auth::getAuthenticityToken() !== $token) {

            $error = true;
            $error_message[] = $LANG['msg-error-unknown'];
        }

        if (!helper::isCorrectEmail($email)) {

            $error = true;
            $error_message[] = $LANG['msg-email-incorrect'];
        }

        if ( !$error && !$helper->isEmailExists($email) ) {

            $error = true;
            $error_message[] = $LANG['msg-email-not-found'];
        }

        if (!$error) {

            $accountId = $helper->getUserIdByEmail($email);

            if ($accountId != 0) {

                $account = new account($dbo, $accountId);

                $accountInfo = $account->get();

                if ($accountInfo['error'] === false && $accountInfo['state'] != ACCOUNT_STATE_BLOCKED) {

                    

                    $restorePointInfo = $account->restorePointCreate($email);

                    ob_start();

                    ?>

                    <html>
                    <body>
                    This is link <a href="<?php echo APP_URL;  ?>/restore?hash=<?php echo $restorePointInfo['hash']; ?>"><?php echo APP_URL;  ?>/restore/?hash=<?php echo $restorePointInfo['hash']; ?></a> to reset your password.
                    </body>
                    </html>

                    <?php

                    $from = SMTP_EMAIL;

                    $to = $email;

                    $html_text = ob_get_clean();

                    $subject = APP_TITLE." | Password reset";

                    $mail = new phpmailer();

                    $mail->isSMTP();                                      // Set mailer to use SMTP
                    $mail->Host = SMTP_HOST;                               // Specify main and backup SMTP servers
                    $mail->SMTPAuth = SMTP_AUTH;                               // Enable SMTP authentication
                    $mail->Username = SMTP_USERNAME;                      // SMTP username
                    $mail->Password = SMTP_PASSWORD;                      // SMTP password
                    $mail->SMTPSecure = SMTP_SECURE;                            // Enable TLS encryption, `ssl` also accepted
                    $mail->Port = SMTP_PORT;                                    // TCP port to connect to

                    $mail->From = $from;
                    $mail->FromName = APP_TITLE;
                    $mail->addAddress($to);                               // Name is optional

                    $mail->isHTML(true);                                  // Set email format to HTML

                    $mail->Subject = $subject;
                    $mail->Body    = $html_text;

                    $mail->send();
                }
            }

            $sent = true;
            header("Location: /remind?sent=success");
        }
    }

    auth::newAuthenticityToken();

    $page_id = "remind";

    $css_files = array("my.css", "landing.css");
    $page_title = $LANG['page-restore']." | ".APP_TITLE;

    include_once("html/common/site_header.inc.php");
?>

<body class="home">

    <?php

        include_once("html/common/site_topbar.inc.php");
    ?>

    <div class="content-page">

        <div class="limiter">

            <div class="container-login100">

                <div class="wrap-login100">

                    <div class="standard-page">

                        <?php

                        if ($sent) {

                            ?>

                            <h1><?php echo $LANG['page-restore']; ?></h1>

                            <div class="opt-in">
                                <label for="user_receive_digest">
                                    <b><?php echo $LANG['msg-reset-password-sent']; ?></b>
                                </label>
                            </div>

                            <?php

                        } else {

                            ?>

                            <h1><?php echo $LANG['page-restore']; ?></h1>

                            <form accept-charset="UTF-8" action="/remind" class="custom-form" id="remind-form" method="post">

                                <input autocomplete="off" type="hidden" name="authenticity_token" value="<?php echo helper::getAuthenticityToken(); ?>">

                                <input id="email" name="email" placeholder="<?php echo $LANG['label-email']; ?>" required="required" size="30" type="text" value="<?php echo $email; ?>">

                                <div class="login-button">
                                    <input name="commit" type="submit" class="blue button" value="<?php echo $LANG['action-next']; ?>">
                                </div>

                            </form>

                            <?php

                        }
                        ?>
                    </div>

                </div>

            </div>

            <?php

                include_once("html/common/site_footer.inc.php");
            ?>

            <script>

                $('#remind-form').submit(function(event) {

                    event.preventDefault();

                    grecaptcha.ready(function() {
                        grecaptcha.execute('<?php echo RECAPTCHA_SITE_KEY; ?>', {action: 'submit'}).then(function(token) {

                            $('#remind-form').prepend('<input type="hidden" name="recaptcha_token" value="'+ token + '">');
                            $('#remind-form').unbind('submit').submit();
                        });
                    });
                });
            </script>

        </div>


    </div>


</body>
</html>