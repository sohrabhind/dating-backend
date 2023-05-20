<?php

    /*!
     * https://hindbyte.com
     * hindbyte@gmail.com
     *
     * Copyright 2012-2021 Demyanchuk Dmitry (hindbyte@gmail.com)
     */;

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
    $comments = new comments($dbo);

    $inbox_all = $comments->getAllCommentsCount();
    $inbox_loaded = 0;

    if (!empty($_POST)) {

        $itemId = isset($_POST['itemId']) ? $_POST['itemId'] : 0;
        $loaded = isset($_POST['loaded']) ? $_POST['loaded'] : 0;

        $itemId = helper::clearInt($itemId);
        $loaded = helper::clearInt($loaded);

        $result = $comments->stream($itemId);

        $inbox_loaded = count($result['items']);

        $result['inbox_loaded'] = $inbox_loaded + $loaded;
        $result['inbox_all'] = $inbox_all;

        if ($inbox_loaded != 0) {

            ob_start();

            foreach ($result['items'] as $key => $value) {

                draw($value);
            }

            $result['html'] = ob_get_clean();

            if ($result['inbox_loaded'] < $inbox_all) {

                ob_start();

                ?>

                    <a href="javascript:void(0)" onclick="Comments.moreItems('<?php echo $result['itemId']; ?>'); return false;">
                        <button type="button" class="btn  btn-info footable-show">View More</button>
                    </a>

                <?php

                $result['html2'] = ob_get_clean();
            }
        }

        echo json_encode($result);
        exit;
    }

    $page_id = "stream_comments";

    $css_files = array("mytheme.css");
    $page_title = "Comments Stream | Admin Panel";

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
                            <li class="breadcrumb-item active">Comments Stream</li>
                        </ol>
                    </div>
                </div>

                <?php

                    if (!$admin_info['error'] && $admin_info['access_level'] == ADMIN_ACCESS_LEVEL_READ_ONLY_RIGHTS) {

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

                    $result = $comments->stream(0);

                    $inbox_loaded = count($result['items']);

                    if ($inbox_loaded != 0) {

                        ?>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h4 class="card-title m-b-0">Comments Stream</h4>
                                    </div>
                                    <div class="card-body collapse show">
                                        <div class="table-responsive">
                                            <table class="table product-overview">
                                                <thead>
                                                <tr>
                                                    <th colspan="2">From User</th>
                                                    <th>Text</th>
                                                    <th>Date</th>
                                                    <th>Actions</th>
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

                                    <?php

                                        if ($inbox_all > 20) {

                                            ?>

                                            <div class="card-body more-items loading-more-container">
                                                <a href="javascript:void(0)" onclick="Comments.moreItems('<?php echo $result['itemId']; ?>'); return false;">
                                                    <button type="button" class="btn  btn-info footable-show">View More</button>
                                                </a>
                                            </div>

                                            <?php
                                        }
                                    ?>

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

            var inbox_all = <?php echo $inbox_all; ?>;
            var inbox_loaded = <?php echo $inbox_loaded; ?>;

            window.Comments || ( window.Comments = {} );

            Comments.remove = function (accountId, accessToken, itemId) {

                $.ajax({
                    type: 'POST',
                    url: '/api/v2/method/manager/app.removeCommentItem',
                    data: 'itemId=' + itemId + "&accountId=" + accountId + "&accessToken=" + accessToken,
                    timeout: 30000,
                    success: function(response) {

                        $('tr.data-item[data-id=' + itemId + ']').remove();
                    },
                    error: function(xhr, type){

                    }
                });
            };

            Comments.moreItems = function (offset) {

                $('div.loading-more-container').hide();

                $.ajax({
                    type: 'POST',
                    url: '/admin/stream_comments',
                    data: 'itemId=' + offset + "&loaded=" + inbox_loaded,
                    dataType: 'json',
                    timeout: 30000,
                    success: function(response){

                        if (response.hasOwnProperty('html2')){

                            $("div.loading-more-container").html("").append(response.html2).show();
                        }

                        if (response.hasOwnProperty('html')){

                            $("tbody.data-table").append(response.html);
                        }

                        inbox_loaded = response.inbox_loaded;
                        inbox_all = response.inbox_all;
                    },
                    error: function(xhr, type){

                        $('div.loading-more-container').show();
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
        ?>

            <tr class="data-item" data-id="<?php echo $item['id']; ?>">

                <td style="width:50px;">

                    <?php

                        if (strlen($item['owner']['bigPhotoUrl']) != 0) {

                            ?>
                                <span class="round" style="background-size: cover; background-image: url(<?php echo $item['owner']['bigPhotoUrl']; ?>)"></span>
                            <?php

                        } else {

                            ?>
                                <span class="round" style="background-size: cover; background-image: url(/assets/img/profile_default_photo.png)"></span>
                            <?php
                        }
                    ?>
                </td>
                <td>
                    <h6><a href="/admin/profile?id=<?php echo $item['owner']['id']; ?>"><?php echo $item['owner']['fullname']; ?></a></h6>
                    <small class="text-muted">@<?php echo $item['owner']['username']; ?></small>
                </td>
                <td>
                    <?php

                        if (strlen($item['comment']) != 0) {

                            ?>
                                <h6><?php echo $item['comment']; ?></h6>
                            <?php

                        } else {

                            ?>
                                <h6>-</h6>
                            <?php
                        }
                    ?>
                </td>
                <td>
                    <h6><?php echo $item['timeAgo']; ?></h6>
                </td>
                <td>
                    <a href="javascript:void(0)" onclick="Comments.remove('<?php echo admin::getCurrentAdminId(); ?>', '<?php echo admin::getAccessToken(); ?>', '<?php echo $item['id']; ?>'); return false;" class="text-inverse" title="" data-toggle="tooltip" data-original-title="Delete"><i class="ti-trash"></i></a>
                </td>
            </tr>

        <?php
    }