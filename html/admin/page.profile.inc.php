<?php

    /*!
     * https://hindbyte.com
     * hindbyte@gmail.com
     *
     * Copyright 2012-2022 Demyanchuk Dmitry (hindbyte@gmail.com)
     */

    if (!admin::isSession()) {

        header("Location: /admin/login");
        exit;
    }

    // Administrator info

    $admin = new admin($dbo);
    $admin->setId(admin::getCurrentAdminId());

    $admin_info = $admin->get();

    //

    $accountInfo = array();

    if (isset($_GET['id'])) {

        $accountId = isset($_GET['id']) ? $_GET['id'] : 0;
        $accessToken = isset($_GET['access_token']) ? $_GET['access_token'] : 0;
        $act = isset($_GET['act']) ? $_GET['act'] : '';

        $accountId = helper::clearInt($accountId);

        $account = new account($dbo, $accountId);
        $accountInfo = $account->get();

        $messages = new msg($dbo);
        $messages->setRequestFrom($accountId);

        if ($accessToken === admin::getAccessToken() && $admin_info['access_level'] < ADMIN_ACCESS_LEVEL_MODERATOR_RIGHTS) {

            switch ($act) {

                case "disconnect": {

                    $account->setFacebookId('');

                    header("Location: /admin/profile?id=".$accountInfo['id']);
                    break;
                }

                case "close": {

                    $auth->removeAll($accountId);

                    header("Location: /admin/profile?id=".$accountInfo['id']);
                    break;
                }

                case "block": {

                    $account->setState(ACCOUNT_STATE_BLOCKED);

                    $gallery = new gallery($dbo);
                    $gallery->setRequestFrom($accountInfo['id']);
                    $gallery->removeAll();
                    unset($gallery);

                    $auth->removeAll($accountInfo['id']);

                    header("Location: /admin/profile?id=".$accountInfo['id']);
                    break;
                }

                case "unblock": {

                    $account->setState(ACCOUNT_STATE_ENABLED);

                    header("Location: /admin/profile?id=".$accountInfo['id']);
                    break;
                }


                case "promode_set": {

                    $account->setPro(1);

                    header("Location: /admin/profile?id=".$accountInfo['id']);
                    break;
                }

                case "promode_unset": {

                    $account->setPro(0);

                    header("Location: /admin/profile?id=".$accountInfo['id']);
                    break;
                }

                case "delete-photo": {

                    $data = array("originPhotoUrl" => '',
                                  "normalPhotoUrl" => '',
                                  "lowPhotoUrl" => '');

                    $account->setPhoto($data);

                    header("Location: /admin/profile?id=".$accountInfo['id']);
                    break;
                }

                default: {

                    if (!empty($_POST)) {

                        $authToken = isset($_POST['authenticity_token']) ? $_POST['authenticity_token'] : '';
                        $username = isset($_POST['username']) ? $_POST['username'] : '';
                        $fullname = isset($_POST['fullname']) ? $_POST['fullname'] : '';
                        $location = isset($_POST['location']) ? $_POST['location'] : '';
                        $balance = isset($_POST['balance']) ? $_POST['balance'] : 0;
                        $free_messages_count = isset($_POST['free_messages_count']) ? $_POST['free_messages_count'] : 0;
                        $instagram_page = isset($_POST['instagram_page']) ? $_POST['instagram_page'] : '';
                        $email = isset($_POST['email']) ? $_POST['email'] : '';
                        $phoneNumber = isset($_POST['phone_number']) ? $_POST['phone_number'] : '';

                        $username = helper::clearText($username);
                        $username = helper::escapeText($username);

                        $fullname = helper::clearText($fullname);
                        $fullname = helper::escapeText($fullname);

                        $location = helper::clearText($location);
                        $location = helper::escapeText($location);

                        $balance = helper::clearInt($balance);
                        $free_messages_count = helper::clearInt($free_messages_count);

                    
                        $instagram_page = helper::clearText($instagram_page);
                        $instagram_page = helper::escapeText($instagram_page);

                        $email = helper::clearText($email);
                        $email = helper::escapeText($email);

                        $phoneNumber = helper::clearText($phoneNumber);
                        $phoneNumber = helper::escapeText($phoneNumber);

                         if ($authToken === helper::getAuthenticityToken() && $admin_info['access_level'] < ADMIN_ACCESS_LEVEL_MODERATOR_RIGHTS) {

                            $account->setUsername($username);
                            $account->setFullname($fullname);
                            $account->setLocation($location);
                            $account->setBalance($balance);
                            $account->setFreeMessagesCount($free_messages_count);
                            $account->setInstagramPage($instagram_page);
                            $account->setEmail($email);
                         }
                    }

                    header("Location: /admin/profile?id=".$accountInfo['id']);
                    exit;
                }
            }
        }

    } else {

        header("Location: /admin/main");
        exit;
    }

    if ($accountInfo['error']) {

        header("Location: /admin/main");
        exit;
    }

    $stats = new stats($dbo);

    $page_id = "account";

    $error = false;
    $error_message = '';

    helper::newAuthenticityToken();

    $css_files = array("mytheme.css");
    $page_title = "Account Info | Admin Panel";

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
                            <li class="breadcrumb-item active">Account Info</li>
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

                    <div class="col-lg-8">

                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title">Account Info</h4>
                                <h6 class="card-subtitle">
                                    <a href="/admin/personal_gcm?id=<?php echo $accountInfo['id']; ?>">
                                        <button class="btn waves-effect waves-light btn-info">Send Personal FCM Message</button>
                                    </a>
                                </h6>
                                <div class="table-responsive">

                                    <table class="table color-table info-table">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Value/Count</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td class="text-left">Username:</td>
                                                <td><?php echo $accountInfo['username']; ?></td>
                                                <td></td>
                                            </tr>
                                            <tr>
                                                <td class="text-left">Fullname:</td>
                                                <td><?php echo $accountInfo['fullname']; ?></td>
                                                <td></td>
                                            </tr>
                                            <tr>
                                                <td class="text-left">Email:</td>
                                                <td><?php echo $accountInfo['email']; ?></td>
                                                <td></td>
                                            </tr>
                                            <tr>
                                                <td class="text-left">SignUp Ip address:</td>
                                                <td><?php if (admin::getAccessLevel() != ADMIN_ACCESS_LEVEL_READ_ONLY_RIGHTS) {echo $accountInfo['ip_addr'];} else {echo "It is not available in the demo version";} ?></td>
                                                <td></td>
                                            </tr>
                                            <tr>
                                                <td class="text-left">SignUp Date:</td>
                                                <td><?php echo date("Y-m-d H:i:s", $accountInfo['regtime']); ?></td>
                                                <td></td>
                                            </tr>
                                            <tr>
                                                <td class="text-left">Account state:</td>
                                                <td>
                                                    <?php

                                                        if ($accountInfo['state'] == ACCOUNT_STATE_ENABLED) {

                                                            echo "<span>Account is active</span>";

                                                        } else {

                                                            echo "<span>Account is blocked</span>";
                                                        }
                                                    ?>
                                                </td>
                                                <td>
                                                    <?php

                                                        if ($accountInfo['state'] == ACCOUNT_STATE_ENABLED) {

                                                            ?>
                                                                <a class="" href="/admin/profile?id=<?php echo $accountInfo['id']; ?>&access_token=<?php echo admin::getAccessToken(); ?>&act=block">Block account</a>
                                                            <?php

                                                        } else {

                                                            ?>
                                                                <a class="" href="/admin/profile?id=<?php echo $accountInfo['id']; ?>&access_token=<?php echo admin::getAccessToken(); ?>&act=unblock">Unblock account</a>
                                                            <?php
                                                        }
                                                    ?>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td class="text-left">Pro Mode:</td>
                                                <td>
                                                    <?php

                                                        if ($accountInfo['level'] == 1) {

                                                            echo "<span>Pro Mode Activated.</span>";

                                                        } else {

                                                            echo "<span>Pro Mode Not Active.</span>";
                                                        }
                                                    ?>
                                                </td>
                                                <td>
                                                    <?php

                                                        if ($accountInfo['level'] == 1) {

                                                            ?>
                                                                <a class="" href="/admin/profile?id=<?php echo $accountInfo['id']; ?>&access_token=<?php echo admin::getAccessToken(); ?>&act=promode_unset">Off Pro Mode</a>
                                                            <?php

                                                        } else {

                                                            ?>
                                                                <a class="" href="/admin/profile?id=<?php echo $accountInfo['id']; ?>&access_token=<?php echo admin::getAccessToken(); ?>&act=promode_set">On Pro Mode</a>
                                                            <?php
                                                        }
                                                    ?>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td class="text-left">User active chats (not removed):</td>
                                                <td>
                                                    <?php
                                                        $active_chats = $messages->myActiveChatsCount();

                                                        echo $active_chats;
                                                    ?>
                                                </td>
                                                <td>
                                                    <?php
                                                        if ($active_chats > 0) {

                                                            ?>
                                                                <a href="/admin/profile_chats?id=<?php echo $accountInfo['id']; ?>" >View</a>
                                                            <?php
                                                        }
                                                    ?>
                                                </td>
                                            </tr>

                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title">Edit Profile</h4>

                                <form class="form-material m-t-40" method="post" action="/admin/profile?id=<?php echo $accountInfo['id']; ?>&access_token=<?php echo admin::getAccessToken(); ?>">

                                    <input type="hidden" name="authenticity_token" value="<?php echo helper::getAuthenticityToken(); ?>">

                                    <div class="form-group">
                                        <label class="col-md-12">Username</label>
                                        <div class="col-md-12">
                                            <input placeholder="Username" id="username" type="text" name="username" maxlength="255" value="<?php echo $accountInfo['username']; ?>" class="form-control form-control-line">
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="col-md-12">Fullname</label>
                                        <div class="col-md-12">
                                            <input placeholder="Fullname" id="fullname" type="text" name="fullname" maxlength="255" value="<?php echo $accountInfo['fullname']; ?>" class="form-control form-control-line">
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="col-md-12">Location</label>
                                        <div class="col-md-12">
                                            <input placeholder="Location" id="location" type="text" name="location" maxlength="255" value="<?php echo $accountInfo['location']; ?>" class="form-control form-control-line">
                                        </div>
                                    </div>


                                    <div class="form-group">
                                        <label class="col-md-12">Instagram page</label>
                                        <div class="col-md-12">
                                            <input placeholder="Instagram page" id="instagram_page" type="text" name="instagram_page" maxlength="255" value="<?php echo $accountInfo['instagram_page']; ?>" class="form-control form-control-line">
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="col-md-12">Email</label>
                                        <div class="col-md-12">
                                            <input placeholder="Email" id="email" type="text" name="email" maxlength="255" value="<?php echo $accountInfo['email']; ?>" class="form-control form-control-line">
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="col-md-12">Balance</label>
                                        <div class="col-md-12">
                                            <input placeholder="Balance" id="balance" type="text" name="balance" maxlength="255" value="<?php echo $accountInfo['balance']; ?>" class="form-control form-control-line">
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="col-md-12">Free messages count</label>
                                        <div class="col-md-12">
                                            <input placeholder="Free messages count" id="free_messages_count" type="text" name="free_messages_count" maxlength="255" value="<?php echo $accountInfo['free_messages_count']; ?>" class="form-control form-control-line">
                                        </div>
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

                    <div class="col-lg-4">
                        <!-- Column -->
                        <div class="card">

                            <div class="card-block little-profile text-center">

                                <div class="level-img">

                                    <?php

                                        if (strlen($accountInfo['lowPhotoUrl']) != 0) {

                                            ?>
                                                <img src="<?php echo $accountInfo['normalPhotoUrl'] ?>" alt="user" />
                                            <?php

                                        } else {

                                            ?>
                                                <img src="/assets/img/profile_default_photo.png" alt="user" />
                                            <?php
                                        }
                                    ?>

                                </div>

                                <?php

                                    if (strlen($accountInfo['lowPhotoUrl']) != 0) {

                                        ?>
                                            <p><a href="/admin/profile?id=<?php echo $accountInfo['id']; ?>&access_token=<?php echo admin::getAccessToken(); ?>&act=delete-photo">Delete Photo</a></p>
                                        <?php

                                    }
                                ?>

                                <h3 class="m-b-0"><?php echo $accountInfo['fullname']; ?></h3>
                                <p>@<?php echo $accountInfo['username']; ?></p>
                            </div>
                        </div>
                        <!-- Column -->
                    </div>

                </div>

                <?php
                    $result = $stats->getAuthData($accountInfo['id'], 0);

                    $inbox_loaded = count($result['data']);

                    if ($inbox_loaded != 0) {

                        ?>

                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card">
                                    <div class="card-body">
                                        <h4 class="card-title">Authorizations</h4>
                                        <h6 class="card-subtitle">
                                            <a href="/admin/profile?id=<?php echo $accountInfo['id']; ?>&access_token=<?php echo admin::getAccessToken(); ?>&act=close">
                                                <button class="btn waves-effect waves-light btn-info">Close all authorizations</button>
                                            </a>
                                        </h6>
                                        <div class="table-responsive">

                                            <table class="table color-table info-table">

                                                <thead>
                                                    <tr>
                                                        <th class="text-left">Id</th>
                                                        <th>Access token</th>
                                                        <th>Client Id</th>
                                                        <th>Create At</th>
                                                        <th>Close At</th>
                                                        <th>User agent</th>
                                                        <th>Ip address</th>
                                                    </tr>
                                                </thead>

                                                <tbody>
                                                    <?php

                                                        foreach ($result['data'] as $key => $value) {

                                                            draw($value);
                                                        }

                                                    ?>
                                                </tbody>

                                            </table>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <?php

                    } else {

                        ?>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="card text-center">
                                        <div class="card-body">
                                            <h4 class="card-title">List is empty.</h4>
                                            <p class="card-text">This means that there is no data to display :)</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php
                    }
                ?>


            </div> <!-- End Container fluid  -->

            <?php

                include_once("html/common/admin_footer.inc.php");
            ?>

        </div> <!-- End Page wrapper  -->
    </div> <!-- End Wrapper -->

</body>

</html>

<?php

    function draw($authObj)
    {
        ?>

        <tr>
            <td class="text-left"><?php echo $authObj['id']; ?></td>
            <td><?php echo $authObj['accessToken']; ?></td>
            <td><?php echo $authObj['clientId']; ?></td>
            <td><?php echo date("Y-m-d H:i:s", $authObj['createAt']); ?></td>
            <td><?php if ($authObj['removeAt'] == 0) {echo "-";} else {echo date("Y-m-d H:i:s", $authObj['removeAt']);} ?></td>
            <td><?php echo $authObj['u_agent']; ?></td>
            <td><?php if (admin::getAccessLevel() != ADMIN_ACCESS_LEVEL_READ_ONLY_RIGHTS) {echo $authObj['ip_addr'];} else {echo "It is not available in the demo version";} ?></td>
        </tr>

        <?php
    }
