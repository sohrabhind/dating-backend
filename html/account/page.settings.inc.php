<?php

    /*!
     * ifsoft.co.uk
     *
     * http://ifsoft.com.ua, http://ifsoft.co.uk, https://hindbyte.com
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

        $gender = isset($_POST['gender']) ? $_POST['gender'] : 0;

        $u_age = isset($_POST['u_age']) ? $_POST['u_age'] : 0;

        $u_height = isset($_POST['u_height']) ? $_POST['u_height'] : 0;

        $fullname = isset($_POST['fullname']) ? $_POST['fullname'] : '';
        $bio = isset($_POST['bio']) ? $_POST['bio'] : '';
        $location = isset($_POST['location']) ? $_POST['location'] : '';
        $facebook_page = isset($_POST['facebook_page']) ? $_POST['facebook_page'] : '';
        $interests = isset($_POST['interests']) ? $_POST['interests'] : '';

        $religiousViews = isset($_POST['religiousViews']) ? $_POST['religiousViews'] : 0;
        $smokingViews = isset($_POST['smokingViews']) ? $_POST['smokingViews'] : 0;
        $alcoholViews = isset($_POST['alcoholViews']) ? $_POST['alcoholViews'] : 0;
        $lookingViews = isset($_POST['lookingViews']) ? $_POST['lookingViews'] : 0;
        $interestedViews = isset($_POST['interestedViews']) ? $_POST['interestedViews'] : 0;

        $gender = helper::clearInt($gender);

        $u_age = helper::clearInt($u_age);
        $u_height = helper::clearInt($u_height);

        $fullname = helper::clearText($fullname);
        $fullname = helper::escapeText($fullname);

        $bio = helper::clearText($bio);
        $bio = helper::escapeText($bio);

        $location = helper::clearText($location);
        $location = helper::escapeText($location);

        $facebook_page = helper::clearText($facebook_page);
        $facebook_page = helper::escapeText($facebook_page);

        $interests = helper::clearText($interests);
        $interests = helper::escapeText($interests);

        $religiousViews = helper::clearInt($religiousViews);
        $smokingViews = helper::clearInt($smokingViews);
        $alcoholViews = helper::clearInt($alcoholViews);
        $lookingViews = helper::clearInt($lookingViews);
        $interestedViews = helper::clearInt($interestedViews);

        if (auth::getAuthenticityToken() !== $token) {

            $error = true;
        }

        if (!$error) {

            if (helper::isCorrectFullname($fullname)) {

                $account->edit($fullname);
            }

            if ($u_age > 17 && $u_age < 111) {

                $account->setAge($u_age);
            }

            if ($u_height > -1 && $u_height < 300) {

                $account->setHeight($u_height);
            }

            $account->setGender($gender);
            $account->setBio($bio);
            $account->setLocation($location);

            $account->set_iReligiousView($religiousViews);
            $account->set_iSmokingViews($smokingViews);
            $account->set_iAlcoholViews($alcoholViews);
            $account->set_iLooking($lookingViews);
            $account->set_iInterested($interestedViews);


            if (helper::isValidURL($interests)) {

                $account->setInterests($interests);

            } else {

                $account->setInterests("");
            }

            header("Location: /account/settings?error=false");
            exit;
        }

        header("Location: /account/settings?error=true");
        exit;
    }

    $account->setLastActive();

    $accountInfo = $account->get();

    auth::newAuthenticityToken();

    $page_id = "settings_profile";

    $css_files = array("main.css", "my.css");
    $page_title = $LANG['page-settings']." | ".APP_TITLE;

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

                        <h1><?php echo $LANG['page-profile-settings']; ?></h1>

                        <div class="tab-container">
                            <nav class="tabs">
                                <a href="/account/settings"><span class="tab active"><?php echo $LANG['page-profile-settings']; ?></span></a>
                                <a href="/account/settings/privacy"><span class="tab"><?php echo $LANG['page-privacy-settings']; ?></span></a>
                                <a href="/account/balance"><span class="tab"><?php echo $LANG['page-balance']; ?></span></a>
                                <a href="/account/settings/services"><span class="tab"><?php echo $LANG['label-services']; ?></span></a>
                                <a href="/account/settings/password"><span class="tab"><?php echo $LANG['label-password']; ?></span></a>
                                <a href="/account/settings/blacklist"><span class="tab"><?php echo $LANG['page-blacklist']; ?></span></a>
                                <a href="/account/settings/otp"><span class="tab"><?php echo $LANG['page-otp']; ?></span></a>
                                <a href="/account/settings/deactivation"><span class="tab"><?php echo $LANG['page-deactivate-account']; ?></span></a>
                            </nav>
                        </div>

                        <form accept-charset="UTF-8" action="/account/settings" autocomplete="off" class="edit_user" id="settings-form" method="post">

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

                                    <div class="profile-basics form-row">
                                        <div class="form-cell left">
                                            <h2><?php echo $LANG['label-settings-main-section-title']; ?></h2>
                                            <p class="info"><?php echo $LANG['label-settings-main-section-sub-title']; ?></p>
                                        </div>

                                        <div class="form-cell">
                                            <input id="fullname" name="fullname" placeholder="<?php echo $LANG['label-fullname']; ?>" maxlength="64" type="text" value="<?php echo $accountInfo['fullname']; ?>">
                                            <input id="location" name="location" placeholder="<?php echo $LANG['label-location']; ?>" maxlength="64" type="text" value="<?php echo $accountInfo['location']; ?>">
                                            <input id="interests" name="interests" placeholder="<?php echo $LANG['label-interests']; ?>" maxlength="255" type="text" value="<?php echo $accountInfo['interests']; ?>">
                                            <textarea placeholder="<?php echo $LANG['label-bio']; ?>" id="bio" name="bio" maxlength="400"><?php echo $accountInfo['status']; ?></textarea>

                                        </div>
                                    </div>

                                    <div class="profile-basics form-row">
                                        <div class="form-cell left">
                                            <h2><?php echo $LANG['label-height']." (".$LANG['label-cm'].")"; ?></h2>
                                        </div>

                                        <div class="form-cell">
                                            <input id="u_height" type="number" size="3" name="u_height" value="<?php echo $accountInfo['height']; ?>">
                                        </div>
                                    </div>

                                    <div class="link-preference form-row">
                                        <div class="form-cell left">
                                            <h2><?php echo $LANG['label-age']; ?></h2>
                                        </div>

                                        <div class="form-cell">
                                            <div class="opt-in">
                                                <select id="u_age" name="u_age" class="selectBox">
                                                    <option disabled value="0" <?php if ($accountInfo['age'] < 18) echo "selected=\"selected\""; ?>><?php echo $LANG['label-select-age']; ?></option>

                                                    <?php

                                                        for ($i = 18; $i <= 110; $i++) {

                                                            if ($i == $accountInfo['age']) {

                                                                echo "<option value=\"$i\" selected=\"selected\">$i</option>";

                                                            } else {

                                                                echo "<option value=\"$i\">$i</option>";
                                                            }
                                                        }
                                                    ?>

                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="link-preference form-row">
                                        <div class="form-cell left">
                                            <h2><?php echo $LANG['label-gender']; ?></h2>
                                        </div>

                                        <div class="form-cell">
                                            <div class="opt-in">
                                                <select id="gender" name="gender" class="selectBox">
                                                    <option value="2" <?php if ($accountInfo['gender'] != GENDER_FEMALE && $accountInfo['gender'] != GENDER_MALE) echo "selected=\"selected\""; ?>><?php echo $LANG['gender-secret']; ?></option>
                                                    <option value="0" <?php if ($accountInfo['gender'] == GENDER_MALE) echo "selected=\"selected\""; ?>><?php echo $LANG['gender-male']; ?></option>
                                                    <option value="1" <?php if ($accountInfo['gender'] == GENDER_FEMALE) echo "selected=\"selected\""; ?>><?php echo $LANG['gender-female']; ?></option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>


                                    <div class="link-preference form-row">
                                        <div class="form-cell left">
                                            <h2><?php echo $LANG['label-religious-view']; ?></h2>
                                        </div>

                                        <div class="form-cell">
                                            <div class="opt-in">
                                                <select id="religiousViews" name="religiousViews" class="selectBox">
                                                    <option value="0" <?php if ($accountInfo['iReligiousView'] == 0) echo "selected=\"selected\""; ?>><?php echo $LANG['label-religious-view-0']; ?></option>
                                                    <option value="1" <?php if ($accountInfo['iReligiousView'] == 1) echo "selected=\"selected\""; ?>><?php echo $LANG['label-religious-view-1']; ?></option>
                                                    <option value="2" <?php if ($accountInfo['iReligiousView'] == 2) echo "selected=\"selected\""; ?>><?php echo $LANG['label-religious-view-2']; ?></option>
                                                    <option value="3" <?php if ($accountInfo['iReligiousView'] == 3) echo "selected=\"selected\""; ?>><?php echo $LANG['label-religious-view-3']; ?></option>
                                                    <option value="4" <?php if ($accountInfo['iReligiousView'] == 4) echo "selected=\"selected\""; ?>><?php echo $LANG['label-religious-view-4']; ?></option>
                                                    <option value="5" <?php if ($accountInfo['iReligiousView'] == 5) echo "selected=\"selected\""; ?>><?php echo $LANG['label-religious-view-5']; ?></option>
                                                    <option value="6" <?php if ($accountInfo['iReligiousView'] == 6) echo "selected=\"selected\""; ?>><?php echo $LANG['label-religious-view-6']; ?></option>
                                                    <option value="7" <?php if ($accountInfo['iReligiousView'] == 7) echo "selected=\"selected\""; ?>><?php echo $LANG['label-religious-view-7']; ?></option>
                                                    <option value="8" <?php if ($accountInfo['iReligiousView'] == 8) echo "selected=\"selected\""; ?>><?php echo $LANG['label-religious-view-8']; ?></option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>


                                    <div class="link-preference form-row">
                                        <div class="form-cell left">
                                            <h2><?php echo $LANG['label-smoking-views']; ?></h2>
                                        </div>

                                        <div class="form-cell">
                                            <div class="opt-in">
                                                <select id="smokingViews" name="smokingViews" class="selectBox">
                                                    <option value="0" <?php if ($accountInfo['iSmokingViews'] == 0) echo "selected=\"selected\""; ?>><?php echo $LANG['label-smoking-views-0']; ?></option>
                                                    <option value="1" <?php if ($accountInfo['iSmokingViews'] == 1) echo "selected=\"selected\""; ?>><?php echo $LANG['label-smoking-views-1']; ?></option>
                                                    <option value="2" <?php if ($accountInfo['iSmokingViews'] == 2) echo "selected=\"selected\""; ?>><?php echo $LANG['label-smoking-views-2']; ?></option>
                                                    <option value="3" <?php if ($accountInfo['iSmokingViews'] == 3) echo "selected=\"selected\""; ?>><?php echo $LANG['label-smoking-views-3']; ?></option>
                                                    <option value="4" <?php if ($accountInfo['iSmokingViews'] == 4) echo "selected=\"selected\""; ?>><?php echo $LANG['label-smoking-views-4']; ?></option>
                                                    <option value="5" <?php if ($accountInfo['iSmokingViews'] == 5) echo "selected=\"selected\""; ?>><?php echo $LANG['label-smoking-views-5']; ?></option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="link-preference form-row">
                                        <div class="form-cell left">
                                            <h2><?php echo $LANG['label-alcohol-views']; ?></h2>
                                        </div>

                                        <div class="form-cell">
                                            <div class="opt-in">
                                                <select id="alcoholViews" name="alcoholViews" class="selectBox">
                                                    <option value="0" <?php if ($accountInfo['iAlcoholViews'] == 0) echo "selected=\"selected\""; ?>><?php echo $LANG['label-alcohol-views-0']; ?></option>
                                                    <option value="1" <?php if ($accountInfo['iAlcoholViews'] == 1) echo "selected=\"selected\""; ?>><?php echo $LANG['label-alcohol-views-1']; ?></option>
                                                    <option value="2" <?php if ($accountInfo['iAlcoholViews'] == 2) echo "selected=\"selected\""; ?>><?php echo $LANG['label-alcohol-views-2']; ?></option>
                                                    <option value="3" <?php if ($accountInfo['iAlcoholViews'] == 3) echo "selected=\"selected\""; ?>><?php echo $LANG['label-alcohol-views-3']; ?></option>
                                                    <option value="4" <?php if ($accountInfo['iAlcoholViews'] == 4) echo "selected=\"selected\""; ?>><?php echo $LANG['label-alcohol-views-4']; ?></option>
                                                    <option value="5" <?php if ($accountInfo['iAlcoholViews'] == 5) echo "selected=\"selected\""; ?>><?php echo $LANG['label-alcohol-views-5']; ?></option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="link-preference form-row">
                                        <div class="form-cell left">
                                            <h2><?php echo $LANG['label-you-looking']; ?></h2>
                                        </div>

                                        <div class="form-cell">
                                            <div class="opt-in">
                                                <select id="lookingViews" name="lookingViews" class="selectBox">
                                                    <option value="0" <?php if ($accountInfo['iLooking'] == 0) echo "selected=\"selected\""; ?>><?php echo $LANG['label-you-looking-0']; ?></option>
                                                    <option value="1" <?php if ($accountInfo['iLooking'] == 1) echo "selected=\"selected\""; ?>><?php echo $LANG['label-you-looking-1']; ?></option>
                                                    <option value="2" <?php if ($accountInfo['iLooking'] == 2) echo "selected=\"selected\""; ?>><?php echo $LANG['label-you-looking-2']; ?></option>
                                                    <option value="3" <?php if ($accountInfo['iLooking'] == 3) echo "selected=\"selected\""; ?>><?php echo $LANG['label-you-looking-3']; ?></option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="link-preference form-row">
                                        <div class="form-cell left">
                                            <h2><?php echo $LANG['label-you-like']; ?></h2>
                                        </div>

                                        <div class="form-cell">
                                            <div class="opt-in">
                                                <select id="interestedViews" name="interestedViews" class="selectBox">
                                                    <option value="0" <?php if ($accountInfo['iInterested'] == 0) echo "selected=\"selected\""; ?>><?php echo $LANG['label-you-like-0']; ?></option>
                                                    <option value="1" <?php if ($accountInfo['iInterested'] == 1) echo "selected=\"selected\""; ?>><?php echo $LANG['label-you-like-1']; ?></option>
                                                    <option value="2" <?php if ($accountInfo['iInterested'] == 2) echo "selected=\"selected\""; ?>><?php echo $LANG['label-you-like-2']; ?></option>
                                                </select>
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