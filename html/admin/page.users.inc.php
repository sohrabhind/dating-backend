<?php

    

    if (!admin::isSession()) {

        header("Location: /admin/login");
        exit;
    }

    $error = false;
    $error_message = '';
    $query = '';
    $result = array();
    $result['users'] = array();

    $stats = new stats($dbo);
    $settings = new settings($dbo);
    $admin = new admin($dbo);

    if (isset($_GET['query'])) {

        $query = isset($_GET['query']) ? $_GET['query'] : '';

        $query = helper::clearText($query);
        $query = helper::escapeText($query);

        if (strlen($query) > 2) {

            $result = $stats->searchAccounts(0, $query);
        }
    }

    helper::newAuthenticityToken();

    $page_id = "users";

    $css_files = array("mytheme.css");
    $page_title = "Search Users| Admin Panel";

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
                            <li class="breadcrumb-item active">Search Users</li>
                        </ol>
                    </div>
                </div>

                <div class="row">
                    <div class="col-sm-12">
                        <div class="card card-body">
                            <h4 class="card-title">Search Users</h4>
                            <h6 class="card-subtitle">Find users by username, full name, email. Minimum of 3 characters.</h6>
                            <div class="row">


                                <div class="col-sm-12 col-xs-12">
                                    <form class="input-form" method="get" action="/admin/users">
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <div class="input-group">
                                                    <input type="text" class="form-control" id="query" name="query" value="<?php echo stripslashes($query); ?>" placeholder="Find users by username, full name, email. Minimum of 3 characters.">
                                                    <div class="input-group-append">
                                                        <button type="submit" class="btn btn-info" type="button">Search</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <br>

                                        <!-- form-group -->
                                    </form>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

                <?php

                    if (count($result['users']) > 0) {

                        ?>

                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card">
                                    <div class="card-body">

                                        <div class="d-flex no-block">
                                            <h4 class="card-title">Search Users</h4>
                                        </div>

                                        <div class="table-responsive m-t-20">

                                            <table class="table stylish-table">

                                                <thead>
                                                <tr>
                                                    <th colspan="2">User</th>
                                                    <th>State</th>
                                                    <th>Facebook</th>
                                                    <th>Email</th>
                                                    <th>SignUp Date</th>
                                                    <th>Ip address</th>
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

                    } else {

                        if (strlen($query) < 3) {

                            ?>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="card text-center">
                                        <div class="card-body">
                                            <h4 class="card-title">List is empty.</h4>
                                            <p class="card-text">Enter in the search box username, communities, full name or email. Minimum of 3 characters.</p>
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
                                            <h4 class="card-title">Matches found: 0</h4>
                                            <p class="card-text">This means that there is no data to display :)</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <?php
                        }
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

                    <a href="/admin/profile?id=<?php echo $user['id']; ?>">

                        <?php

                            if (strlen($user['bigPhotoUrl']) != 0) {

                                ?>
                                    <span class="round" style="background-size: cover; background-image: url(<?php echo $user['bigPhotoUrl']; ?>)"></span>
                                <?php

                            } else {

                                ?>
                                    <span class="round" style="background-size: cover; background-image: url(/assets/icons/profile_default_photo.png)"></span>
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
                <td><?php echo $user['email']; ?></td>
                <td><?php echo date("Y-m-d H:i:s", $user['regtime']); ?></td>
                <td><?php if (admin::getAccessLevel() != ADMIN_ACCESS_LEVEL_READ_ONLY_RIGHTS) {echo $user['ip_addr'];} else {echo "It is not available in the demo version";} ?></td>
            </tr>

        <?php
    }