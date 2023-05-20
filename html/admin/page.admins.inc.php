<?php

    /*!
     * https://hindbyte.com
     * hindbyte@gmail.com
     *
     * Copyright 2012-2021 Demyanchuk Dmitry (hindbyte@gmail.com)
     */

    if (!admin::isSession()) {

        header("Location: /admin/login");
        exit;
    }

    $stats = new stats($dbo);

    $error = false;
    $error_message = '';

    if (isset($_GET['action'])) {

        $access_token = isset($_GET['access_token']) ? $_GET['access_token'] : '';
        $action = isset($_GET['action']) ? $_GET['action'] : '';
        $id = isset($_GET['id']) ? $_GET['id'] : 0;

        $action = helper::clearText($action);
        $action = helper::escapeText($action);

        $id = helper::clearInt($id);

        switch($action) {

            case 'remove': {

                if ($access_token === admin::getAccessToken() && admin::getAccessLevel() == ADMIN_ACCESS_LEVEL_ALL_RIGHTS) {

                    $admins = new admin($dbo);
                    $admins->setId($id);
                    $admins->removeAllAuthorizations($id);
                    $admins->remove();
                    unset($admins);
                }

                header("Location: /admin/admins");
                exit;

                break;
            }

            default: {

                header("Location: /admin/admins");
                exit;

                break;
            }
        }
    }

    if (!empty($_POST)) {

        $authToken = isset($_POST['authenticity_token']) ? $_POST['authenticity_token'] : '';
        $fullname = isset($_POST['fullname']) ? $_POST['fullname'] : '';
        $username = isset($_POST['username']) ? $_POST['username'] : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';
        $access_level = isset($_POST['access_level']) ? $_POST['access_level'] : 0;

        $fullname = helper::clearText($fullname);
        $username = helper::clearText($username);
        $password = helper::clearText($password);
        $access_level = helper::clearInt($access_level);

        if ($authToken === helper::getAuthenticityToken() && admin::getAccessLevel() == ADMIN_ACCESS_LEVEL_ALL_RIGHTS) {

            $admins = new admin($dbo);
            print_r($admins->signup($username, $password, $fullname, $access_level));
            unset($admins);
        }

        header("Location: /admin/admins");
        exit;
    }

    $page_id = "admins";

    helper::newAuthenticityToken();

    $css_files = array("mytheme.css");
    $page_title = "Administrators | Admin Panel";

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
                            <li class="breadcrumb-item active">Admins</li>
                        </ol>
                    </div>
                </div>

                <?php

                    if (admin::getAccessLevel() != ADMIN_ACCESS_LEVEL_ALL_RIGHTS) {

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

                    <div class="col-lg-12">

                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title">Add New Administrator</h4>

                                <form class="form-material m-t-40"  method="post" action="/admin/admins" enctype="multipart/form-data">

                                    <input type="hidden" name="authenticity_token" value="<?php echo helper::getAuthenticityToken(); ?>">

                                    <div class="form-group">
                                        <label>Administrator fullname</label>
                                        <input placeholder="For example: John Doe" id="fullname" type="text" name="fullname" maxlength="100" value="" class="form-control form-control-line">
                                    </div>

                                    <div class="form-group">
                                        <label>Administrator username (login)</label>
                                        <input placeholder="For example: johndoe" id="username" type="text" name="username" maxlength="100" value="" class="form-control form-control-line">
                                    </div>

                                    <div class="form-group">
                                        <label >Administrator password</label>
                                        <input placeholder="For example: john123doe_passw" id="password" type="text" name="password" maxlength="100" value="" class="form-control form-control-line">
                                    </div>

                                    <div class="form-group">
                                        <label>Access Level</label>
                                        <select class="form-control" name="access_level">
                                            <option selected="selected" value="<?php echo ADMIN_ACCESS_LEVEL_READ_WRITE_RIGHTS; ?>">All rights except - cannot create and delete admins</option>
                                            <option value="<?php echo ADMIN_ACCESS_LEVEL_MODERATOR_RIGHTS; ?>">Moderator. Cannot make app changes</option>
                                            <option value="<?php echo ADMIN_ACCESS_LEVEL_READ_ONLY_RIGHTS; ?>">View only. No rights to make changes</option>
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <div class="col-xs-12">
                                            <button class="btn btn-info text-uppercase waves-effect waves-light" type="submit">Add</button>
                                        </div>
                                    </div>
                                </form>

                            </div>
                        </div>

                    </div>


                </div>

                <?php

                    $admins = new admin($dbo);
                    $result = $admins->getAdminsList();

                    $inbox_loaded = count($result['items']);

                    if ($inbox_loaded != 0) {

                        ?>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h4 class="card-title m-b-0">Administrators</h4>
                                    </div>
                                    <div class="card-body collapse show">
                                        <div class="table-responsive">
                                            <table class="table product-overview">
                                                <thead>
                                                <tr>
                                                    <th class="text-left">Id</th>
                                                    <th>Fullname</th>
                                                    <th>Username</th>
                                                    <th>Access Rights</th>
                                                    <th>Create Date</th>
                                                    <th>Status</th>
                                                    <th>Action</th>
                                                </tr>
                                                </thead>
                                                <tbody class="data-table">
                                                    <?php

                                                        foreach ($result['items'] as $key => $value) {

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

    function draw($itemObj)
    {
        ?>

        <tr data-id="<?php echo $itemObj['id']; ?>">
            <td class="text-left"><?php echo $itemObj['id']; ?></td>
            <td><?php echo $itemObj['fullname'] ?></td>
            <td><?php echo $itemObj['username'] ?></td>
            <td>
                <?php

                    switch ($itemObj['access_level']) {

                        case ADMIN_ACCESS_LEVEL_ALL_RIGHTS: {

                            echo "All rights.";

                            break;
                        }

                        case ADMIN_ACCESS_LEVEL_READ_WRITE_RIGHTS: {

                            echo "Can moderate. Can change settings.";

                            break;
                        }

                        case ADMIN_ACCESS_LEVEL_MODERATOR_RIGHTS: {

                            echo "Can moderate. Cannot change settings.";

                            break;
                        }

                        default: {

                            echo "Read only. Cannot change settings and moderate.";

                            break;
                        }
                    }
                ?>
            </td>
            <td><?php echo $itemObj['createDate']; ?></td>
            <td>
                <?php
                    if ($itemObj['removeAt'] == 0) {

                        echo "Active";

                    } else {

                        echo "Deactivated";
                    }

                ?>
            </td>
            <td>
                <?php

                    if ($itemObj['removeAt'] != 0) {

                        echo "Account has been disabled.";

                    } else {

                        if ($itemObj['access_level'] == ADMIN_ACCESS_LEVEL_ALL_RIGHTS) {

                            echo "This account cannot be deleted";

                        } else {

                            if (admin::getAccessLevel() == ADMIN_ACCESS_LEVEL_ALL_RIGHTS) {

                                ?>
                                <a href="/admin/admins?id=<?php echo $itemObj['id']; ?>&action=remove&access_token=<?php echo admin::getAccessToken(); ?>">Remove</a>
                                <?php

                            } else {

                                echo "You do not have access rights";
                            }
                        }
                    }
                ?>

            </td>
        </tr>

        <?php
    }
