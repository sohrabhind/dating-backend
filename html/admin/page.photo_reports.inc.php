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

    if (isset($_GET['act'])) {

        $act = isset($_GET['act']) ? $_GET['act'] : '';
        $token = isset($_GET['access_token']) ? $_GET['access_token'] : '';

        if (admin::getAccessToken() === $token && $admin_info['access_level'] < ADMIN_ACCESS_LEVEL_MODERATOR_RIGHTS) {

            switch ($act) {

                case "clear" : {

                    $reports->clear(REPORT_TYPE_GALLERY_ITEM);

                    header("Location: /admin/photo_reports");
                    break;
                }

                default: {

                    header("Location: /admin/photo_reports");
                    exit;
                }
            }
        }

        header("Location: /admin/photo_reports");
        exit;
    }

    $page_id = "photo_reports";

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
                            <li class="breadcrumb-item active">Photo Reports</li>
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

                    $result = $reports->getItems(0, REPORT_TYPE_GALLERY_ITEM);

                    $inbox_loaded = count($result['items']);

                    if ($inbox_loaded != 0) {

                        ?>

                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card">
                                    <div class="card-body">
                                        <a href="/admin/photo_reports?act=clear&access_token=<?php echo admin::getAccessToken(); ?>" style="float: right">
                                            <button type="button" class="btn waves-effect waves-light btn-info">Delete all reports</button>
                                        </a>

                                        <div class="d-flex no-block">
                                            <h4 class="card-title">Gallery Reports (Latest reports)</h4>
                                        </div>

                                        <div class="table-responsive m-t-20">

                                            <table class="table stylish-table">

                                                <thead>
                                                <tr>
                                                    <th colspan="2">Report From User</th>
                                                    <th colspan="2">Item Author</th>
                                                    <th>Image</th>
                                                    <th>Comment</th>
                                                    <th>Reason</th>
                                                    <th>Date</th>
                                                    <th>Action</th>
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

                    <script type="text/javascript">

            window.Photo || (window.Photo = {});

            Photo.remove = function (offset, fromUserId, accessToken) {

                $.ajax({
                    type: 'GET',
                    url: '/admin/photo_remove?id=' + offset + '&fromUserId=' + fromUserId + '&access_token=' + accessToken,
                    data: 'itemId=' + offset + '&fromUserId=' + fromUserId + "&access_token=" + accessToken,
                    timeout: 30000,
                    success: function(response) {

                        $('tr[data-id=' + offset + ']').remove();
                    },
                    error: function(xhr, type){

                    }
                });
            };

        </script>

        </div> <!-- End Page wrapper  -->
    </div> <!-- End Wrapper -->

</body>

</html>

<?php

    function draw($item)
    {
        $gallery = new gallery(null);
        $itemInfo = $gallery->info($item['itemId']);
        unset($gallery);
        ?>

            <tr data-id="<?php echo $item['itemId']; ?>">
                <td style="width:50px;">

                    <?php

                        if ($item['fromUserId'] != 0 && strlen($item['owner']['lowPhotoUrl']) != 0) {

                            ?>
                                <span class="round" style="background-size: cover; background-image: url(<?php echo $item['owner']['lowPhotoUrl']; ?>)"></span>
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

                        if ($item['fromUserId'] != 0) {

                            ?>
                                <h6><a href="/admin/profile?id=<?php echo $item['fromUserId']; ?>"><?php echo $item['owner']['fullname']; ?></a></h6>
                                <small class="text-muted">@<?php echo $item['owner']['username']; ?></small>
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

                        if ($item['toUserId'] != 0 && strlen($item['suspect']['lowPhotoUrl']) != 0) {

                            ?>
                                <span class="round" style="background-size: cover; background-image: url(<?php echo $item['suspect']['lowPhotoUrl']; ?>)"></span>
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

                        if ($item['toUserId'] != 0) {

                            ?>
                                <h6><a href="/admin/profile?id=<?php echo $item['toUserId']; ?>"><?php echo $item['suspect']['fullname']; ?></a></h6>
                                <small class="text-muted">@<?php echo $item['suspect']['username']; ?></small>
                            <?php

                        } else {

                            ?>
                                <h6>Unknown user</h6>
                            <?php
                        }
                    ?>
                </td>

                <td>
                </td>
                <td>
                    <?php

                        if (strlen($itemInfo['comment']) != 0) {

                            ?>
                                <h6><?php echo $itemInfo['comment']; ?></h6>
                            <?php

                        } else {

                            ?>
                                <h6>-</h6>
                            <?php
                        }
                    ?>
                </td>

                <td>
                    <?php

                        switch ($item['abuseId']) {

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
                <td><?php echo $item['date']; ?></td>
                <td>
                    <a href="javascript:void(0)" onclick="Photo.remove('<?php echo $item['itemId']; ?>', '<?php echo $item['toUserId']; ?>', '<?php echo admin::getAccessToken(); ?>'); return false;" class="text-inverse" title="" data-toggle="tooltip" data-original-title="Delete Photo and all reports to photo"><i class="ti-trash"></i></a>
                </td>
            </tr>

        <?php
    }