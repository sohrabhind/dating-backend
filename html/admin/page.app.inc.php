<?php

    

    if (!admin::isSession()) {

        header("Location: /admin/login");
        exit;
    }

    // Administrator info

    $admin = new admin($dbo);
    $admin->setId(admin::getCurrentAdminId());

    $admin_info = $admin->get();

    //

    $stats = new stats($dbo);
    $settings = new settings($dbo);

    $allowSeenTyping = 1;

    $allowMultiAccountsFunction = 1;

    $defaultLevelMessagesCount = 0;
    $defaultBalance = 10;

    $defaultAllowMessages = 1;

    if (!empty($_POST)) {

        $authToken = isset($_POST['authenticity_token']) ? $_POST['authenticity_token'] : '';

        $allowSeenTyping_checkbox = isset($_POST['allowSeenTyping']) ? $_POST['allowSeenTyping'] : '';

        $allowMultiAccountsFunction_checkbox = isset($_POST['allowMultiAccountsFunction']) ? $_POST['allowMultiAccountsFunction'] : '';

        $defaultLevelMessagesCount = isset($_POST['defaultLevelMessagesCount']) ? $_POST['defaultLevelMessagesCount'] : 0;
        $defaultBalance = isset($_POST['defaultBalance']) ? $_POST['defaultBalance'] : 10;

        $defaultAllowMessages_checkbox = isset($_POST['defaultAllowMessages']) ? $_POST['defaultAllowMessages'] : '';

    
        if ($authToken === helper::getAuthenticityToken() && $admin_info['access_level'] < ADMIN_ACCESS_LEVEL_MODERATOR_RIGHTS) {


            if ($allowSeenTyping_checkbox === "on") {

                $allowSeenTyping = 1;

            } else {

                $allowSeenTyping = 0;
            }

            if ($allowMultiAccountsFunction_checkbox === "on") {

                $allowMultiAccountsFunction = 1;

            } else {

                $allowMultiAccountsFunction = 0;
            }

            if ($defaultAllowMessages_checkbox === "on") {

                $defaultAllowMessages = 1;

            } else {

                $defaultAllowMessages = 0;
            }

            $defaultBalance = helper::clearInt($defaultBalance);
            $defaultLevelMessagesCount = helper::clearInt($defaultLevelMessagesCount);

            $settings->setValue("allowSeenTyping", $allowSeenTyping);

            $settings->setValue("allowMultiAccountsFunction", $allowMultiAccountsFunction);

            $settings->setValue("defaultBalance", $defaultBalance);
            $settings->setValue("defaultLevelMessagesCount", $defaultLevelMessagesCount);

            $settings->setValue("defaultAllowMessages", $defaultAllowMessages);

        }
    }

    $config = $settings->get();

    $arr = array();

    $arr = $config['allowSeenTyping'];
    $allowSeenTyping = $arr['intValue'];

    $arr = $config['allowMultiAccountsFunction'];
    $allowMultiAccountsFunction = $arr['intValue'];

    $arr = $config['defaultBalance'];
    $defaultBalance = $arr['intValue'];

    $arr = $config['defaultLevelMessagesCount'];
    $defaultLevelMessagesCount = $arr['intValue'];

    $arr = $config['defaultAllowMessages'];
    $defaultAllowMessages = $arr['intValue'];

    $page_id = "app";

    $error = false;
    $error_message = '';

    helper::newAuthenticityToken();

    $css_files = array("mytheme.css");
    $page_title = "App Settings";

    include_once("html/common/admin_header.inc.php");
?>

<body class="fix-header fix-sidebar card-no-border">

    <div id="main-wrapper">

        <?php

            include_once("html/common/admin_topbar.inc.php");
        ?>

        <?php

            include_once("html/common/admin_sidebar.inc.php");
        ?>

        <div class="page-wrapper">

            <div class="container-fluid">

                <div class="row page-titles">
                    <div class="col-md-5 col-8 align-self-center">
                        <h3 class="text-themecolor">Dashboard</h3>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="/admin/main">Home</a></li>
                            <li class="breadcrumb-item active">App Settings</li>
                        </ol>
                    </div>
                </div>

                <?php

                    if (!$admin_info['error'] && $admin_info['access_level'] > ADMIN_ACCESS_LEVEL_READ_WRITE_RIGHTS) {

                        ?>
                        <div class="card">
                            <div class="card-body collapse show">
                                <h4 class="card-title">Warning!</h4>
                                <p class="card-text">Your account does not have rights to make changes in this section! The changes you've made will not be saved.</p>
                            </div>
                        </div>
                        <?php
                    }
                ?>

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title">App Settings</h4>
                                <h6 class="card-subtitle">Change application settings</h6>

                                <form action="/admin/app" method="post">

                                    <input type="hidden" name="authenticity_token" value="<?php echo helper::getAuthenticityToken(); ?>">

                                    <div class="form-group">

                                        <p>
                                            <input type="checkbox" name="allowSeenTyping" id="allowSeenTyping" <?php if ($allowSeenTyping == 1) echo "checked=\"checked\"";  ?> />
                                            <label for="allowSeenTyping">Allow Seen&Typing functions in chat</label>
                                        </p>

                                        <p>
                                            <input type="checkbox" name="allowMultiAccountsFunction" id="allowMultiAccountsFunction" <?php if ($allowMultiAccountsFunction == 1) echo "checked=\"checked\"";  ?> />
                                            <label for="allowMultiAccountsFunction">Enable creation of multi-accounts</label>
                                        </p>

                                        <p>
                                            <input type="checkbox" name="defaultAllowMessages" id="defaultAllowMessages" <?php if ($defaultAllowMessages == 1) echo "checked=\"checked\"";  ?> />
                                            <label for="defaultAllowMessages">Allow private messages from all users by default (activating this option can increase the flow of spam in messages, each user can change this option in the settings of his account)</label>
                                        </p>

                                    </div>

                                    <div class="form-group">
                                        <label for="defaultBalance" class="active">Balance of the user after registration (credits)</label>
                                        <input class="form-control" id="defaultBalance" type="number" size="4" name="defaultBalance" value="<?php echo $defaultBalance; ?>">
                                    </div>

                                    <div class="form-group">
                                        <label for="defaultLevelMessagesCount" class="active">Number of free messages for the user</label>
                                        <input class="form-control" id="defaultLevelMessagesCount" type="number" size="4" name="defaultLevelMessagesCount" value="<?php echo $defaultLevelMessagesCount; ?>">
                                    </div>
                                    

                                    <div class="form-group">
                                        <div class="col-xs-12">
                                            <button class="btn btn-info text-uppercase waves-effect waves-light" type="submit">Save</button>
                                        </div>
                                    </div>
                                </form>

                            </div>
                        </div>
                    </div>
                </div>



            </div> <!-- End Container fluid  -->

            <?php

                include_once("html/common/admin_footer.inc.php");
            ?>

        </div> <!-- End Page wrapper  -->
    </div> <!-- End Wrapper -->

</body>

</html>