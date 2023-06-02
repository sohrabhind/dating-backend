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

    $page_id = "main";

    $css_files = array("mytheme.css");
    $page_title = "Dashboard";

    include_once("html/common/admin_header.inc.php");
?>

<body class="fix-header fix-sidebar card-no-border">
    <!-- ============================================================== -->
    <!-- Preloader - style you can find in spinners.css -->
    <!-- ============================================================== -->

    <!-- ============================================================== -->
    <!-- Main wrapper - style you can find in pages.scss -->
    <!-- ============================================================== -->
    <div id="main-wrapper">

        <?php

            include_once("html/common/admin_topbar.inc.php");
        ?>

        <?php

            include_once("html/common/admin_sidebar.inc.php");
        ?>

        <div class="page-wrapper"> <!-- Page wrapper  -->

            <div class="container-fluid"> <!-- Container fluid  -->

                <div class="row page-titles">
                    <div class="col-md-5 col-8 align-self-center">
                        <h3 class="text-themecolor">Dashboard</h3>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0)">Home</a></li>
                            <li class="breadcrumb-item active">Dashboard</li>
                        </ol>
                    </div>
                </div>

                <div class="row">
                    <!-- Column -->
                    <div class="col-lg-3 col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex flex-row">
                                    <div class="round round-lg align-self-center round-info">
                                        <i class="ti-user"></i>
                                    </div>
                                    <div class="m-l-10 align-self-center">
                                        <h3 class="m-b-0 font-light"><?php echo $stats->getUsersCount(); ?></h3>
                                        <h5 class="text-muted m-b-0">Total Users</h5></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Column -->
                    <!-- Column -->
                    <div class="col-lg-3 col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex flex-row">
                                    <div class="round round-lg align-self-center round-warning">
                                        <i class="ti-image"></i>
                                    </div>
                                    <div class="m-l-10 align-self-center">
                                        <h3 class="m-b-0 font-lgiht"><?php echo $stats->getImagesCount(); ?></h3>
                                        <h5 class="text-muted m-b-0">Total images</h5></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Column -->
                    <!-- Column -->
                    <div class="col-lg-3 col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex flex-row">
                                    <div class="round round-lg align-self-center round-primary"><i class="ti-comment-alt"></i></div>
                                    <div class="m-l-10 align-self-center">
                                        <h3 class="m-b-0 font-lgiht"><?php echo $stats->getMessagesTotal(); ?></h3>
                                        <h5 class="text-muted m-b-0">Total Messages</h5></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Column -->
                    <!-- Column -->
                    <div class="col-lg-3 col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex flex-row">
                                    <div class="round round-lg align-self-center round-danger"><i class="ti-comments"></i></div>
                                    <div class="m-l-10 align-self-center">
                                        <h3 class="m-b-0 font-lgiht"><?php echo $stats->getChatsTotal(); ?></h3>
                                        <h5 class="text-muted m-b-0">Total chats</h5></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Column -->
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title m-b-0">Full Statistics</h4>
                            </div>
                            <div class="card-body collapse show">
                                <div class="table-responsive">
                                    <table class="table product-overview">
                                        <thead>
                                        <tr>
                                            <th class="text-left">Name</th>
                                            <th>Count</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td class="text-left">Accounts</td>
                                                <td><?php echo $stats->getUsersCount(); ?></td>
                                            </tr>
                                            <tr>
                                                <td class="text-left">Active accounts</td>
                                                <td><?php echo $stats->getUsersCountByState(ACCOUNT_STATE_ENABLED); ?></td>
                                            </tr>
                                            <tr>
                                                <td class="text-left">Blocked accounts</td>
                                                <td><?php echo $stats->getUsersCountByState(ACCOUNT_STATE_BLOCKED); ?></td>
                                            </tr>


                                            <tr>
                                                <td class="text-left">Total images</td>
                                                <td><?php echo $stats->getImagesCount(); ?></td>
                                            </tr>
                                            <tr>
                                                <td class="text-left">Total active images (not removed)</td>
                                                <td><?php echo $stats->getActiveImagesCount(); ?></td>
                                            </tr>

                                            <tr>
                                                <td class="text-left">Total chats</td>
                                                <td><?php echo $stats->getChatsTotal(); ?></td>
                                            </tr>
                                            <tr>
                                                <td class="text-left">Total active chats (not removed)</td>
                                                <td><?php echo $stats->getChatsCount(); ?></td>
                                            </tr>
                                            <tr>
                                                <td class="text-left">Total messages</td>
                                                <td><?php echo $stats->getMessagesTotal(); ?></td>
                                            </tr>
                                            <tr>
                                                <td class="text-left">Total active messages (not removed)</td>
                                                <td><?php echo $stats->getMessagesCount(); ?></td>
                                            </tr>
                                        </tbody>

                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <?php

                    $result = $stats->getAccounts(0);

                    $inbox_loaded = count($result['users']);

                    if ($inbox_loaded != 0) {

                        ?>

                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="d-flex no-block">
                                            <h4 class="card-title">The recently registered users</h4>
                                        </div>
                                        <div class="table-responsive m-t-20">
                                            <table class="table stylish-table">
                                                <thead>
                                                <tr>
                                                    <th colspan="2">User</th>
                                                    <th>Account state</th>
                                                    <th>Email</th>
                                                    <th>Sign up date</th>
                                                    <th>Ip address</th>
                                                    <th>Action</th>
                                                </tr>
                                                </thead>
                                                <tbody>

                                                <?php

                                                    foreach ($result['users'] as $key => $value) {

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

                    }
                ?>


            </div> <!-- End Container fluid  -->

            <?php

                include_once("html/common/admin_footer.inc.php");
            ?>

        </div> <!-- End Page wrapper  -->

    </div> <!-- End Main Wrapper -->

</body>

</html>

<?php

    function draw($user)
    {
        ?>

            <tr>
                <td style="width:50px;">

                    <a href="/admin/profile?id=<?php echo $user['id']; ?>">
                        <?php

                            if (strlen($user['bigPhotoUrl']) != 0) {

                                ?>
                                    <span class="round" style="background-size: cover; background-image: url(<?php echo $user['bigPhotoUrl']; ?>)"></span>
                                <?php

                            } else {

                                ?>
                                    <span class="round" style="background-size: cover; background-image: url(/assets/img/profile_default_photo.png)"></span>
                                <?php
                            }
                        ?>
                    </a>
                </td>
                <td>
                    <h6><a href="/admin/profile?id=<?php echo $user['id']; ?>"><?php echo $user['fullname']; ?></a></h6>
                    <small class="text-muted">@<?php echo $user['username']; ?></small>
                </td>
                <td>
                    <h6><?php if ($user['state'] == 0) {echo "Enabled";} else {echo "Blocked";} ?></h6>
                </td>
                <td>
                    <h6><?php echo $user['email']; ?></h6>
                </td>
                <td><?php echo date("Y-m-d H:i:s", $user['regtime']); ?></td>
                <td><?php if (admin::getAccessLevel() != ADMIN_ACCESS_LEVEL_READ_ONLY_RIGHTS) {echo $user['ip_addr'];} else {echo "It is not available in the demo version";} ?></td>
                <td><a href="/admin/profile?id=<?php echo $user['id']; ?>">Go to account</a></td>
            </tr>

        <?php
    }