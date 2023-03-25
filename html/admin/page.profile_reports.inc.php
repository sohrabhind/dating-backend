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

    // Administrator info

    $admin = new admin($dbo);
    $admin->setId(admin::getCurrentAdminId());

    $admin_info = $admin->get();

    //

    $stats = new stats($dbo);
    $reports = new reports($dbo);

    $pageId = 1;

    if (isset($_GET['act'])) {

        $accessToken = isset($_GET['access_token']) ? $_GET['access_token'] : 0;
        $act = isset($_GET['act']) ? $_GET['act'] : '';

        if ($accessToken === admin::getAccessToken() && $admin_info['access_level'] < ADMIN_ACCESS_LEVEL_MODERATOR_RIGHTS) {

            switch($act) {

                case "delete": {

                    $reports->clear(REPORT_TYPE_PROFILE);

                    header("Location: /admin/profile_reports");
                    exit;

                    break;
                }

                default: {

                    header("Location: /admin/profile_reports");
                    exit;

                    break;
                }
            }
        }
    }

    $page_id = "reports";

    $css_files = array("mytheme.css");
    $page_title = "Profile Reports | Admin Panel";

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
                            <li class="breadcrumb-item active">Profile Reports</li>
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

                <?php

                    $result = $reports->getItems(0, REPORT_TYPE_PROFILE);

                    $inbox_loaded = count($result['items']);

                    if ($inbox_loaded != 0) {

                        ?>

                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card">
                                    <div class="card-body">
                                        <a href="/admin/profile_reports?act=delete&access_token=<?php echo admin::getAccessToken(); ?>" style="float: right">
                                            <button type="button" class="btn waves-effect waves-light btn-info">Delete all reports</button>
                                        </a>

                                        <div class="d-flex no-block">
                                            <h4 class="card-title">Profile Reports (Latest reports)</h4>
                                        </div>

                                        <div class="table-responsive m-t-20">

                                            <table class="table stylish-table">

                                                <thead>
                                                <tr>
                                                    <th colspan="2">From User</th>
                                                    <th colspan="2">To User</th>
                                                    <th>Reason</th>
                                                    <th>Date</th>
                                                </tr>
                                                </thead>

                                                <tbody>
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

    function draw($user)
    {
        ?>

            <tr>
                <td style="width:50px;">

                    <?php

                        if ($user['fromUserId'] != 0 && strlen($user['owner']['lowPhotoUrl']) != 0) {

                            ?>
                                <span class="round" style="background-size: cover; background-image: url(<?php echo $user['owner']['lowPhotoUrl']; ?>)"></span>
                            <?php

                        } else {

                            ?>
                                <span class="round" style="background-size: cover; background-image: url(/assets/img/profile_default_photo.png)"></span>
                            <?php
                        }
                    ?>
                </td>
                <td>

                    <?php

                        if ($user['fromUserId'] != 0) {

                            ?>
                                <h6><a href="/admin/profile?id=<?php echo $user['fromUserId']; ?>"><?php echo $user['owner']['fullname']; ?></a></h6>
                                <small class="text-muted">@<?php echo $user['owner']['username']; ?></small>
                            <?php

                        } else {

                            ?>
                                <h6>Unknown user</h6>
                            <?php
                        }
                    ?>
                </td>
                <td style="width:50px;">

                    <?php

                        if ($user['toUserId'] != 0 && strlen($user['suspect']['lowPhotoUrl']) > 0) {

                            ?>
                                <span class="round" style="background-size: cover; background-image: url(<?php echo $user['suspect']['lowPhotoUrl']; ?>)"></span>
                            <?php

                        } else {

                            ?>
                                <span class="round" style="background-size: cover; background-image: url(/assets/img/profile_default_photo.png)"></span>
                            <?php
                        }
                    ?>
                </td>
                <td>
                    <h6><a href="/admin/profile?id=<?php echo $user['toUserId']; ?>"><?php echo $user['suspect']['fullname']; ?></a></h6>
                    <small class="text-muted">@<?php echo $user['suspect']['username']; ?></small>
                </td>
                <td>
                    <?php

                        switch ($user['abuseId']) {

                            case 0: {

                                echo "<span class=\"label label-success\">This is spam.</span>";

                                break;
                            }

                            case 1: {

                                echo "<span class=\"label label-info\">Hate Speech or violence.</span>";

                                break;
                            }

                            case 2: {

                                echo "<span class=\"label label-danger\">Nudity or Pornography.</span>";

                                break;
                            }

                            default: {

                                echo "<span class=\"label label-warning\">Fake profile.</span>";

                                break;
                            }
                        }
                    ?>
                </td>
                <td><?php echo $user['date']; ?></td>
            </tr>

        <?php
    }