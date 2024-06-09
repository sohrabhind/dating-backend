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

    $postId = 0;
    $chatInfo = array();

    if (isset($_GET['chatId'])) {

        $chatId = isset($_GET['chatId']) ? $_GET['chatId'] : 0;
        $accessToken = isset($_GET['access_token']) ? $_GET['access_token'] : 0;
        $act = isset($_GET['act']) ? $_GET['act'] : '';

        $chatId = helper::clearInt($chatId);

        $messages = new messages($dbo);
        $chatInfo = $messages->getFull($chatId);

        if ($chatInfo['error']) {

            header("Location: /admin/main");
            exit;
        }

    } else {
        header("Location: /admin/main");
        exit;
    }

    $page_id = "chat";

    $css_files = array("mytheme.css");
    $page_title = "Messages | Admin Panel";

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
                            <li class="breadcrumb-item"><a href="/admin/chats">Chats</a></li>
                            <li class="breadcrumb-item active">Messages</li>
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

                        <div class="row">
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h4 class="card-title m-b-0">Messages</h4>
                                    </div>
                                    <div class="card-body collapse show">
                                        <div class="table-responsive">
                                            <table class="table product-overview">
                                                <thead>
                                                <tr>
                                                    <th colspan="2">From User</th>
                                                    <th>Image</th>
                                                    <th>Text</th>
                                                    <th>Date</th>
                                                    <th>Actions</th>
                                                </tr>
                                                </thead>
                                                <tbody class="data-table">
                                                    <?php

                                                        foreach ($chatInfo['messages'] as $key => $value) {
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

            </div> <!-- End Container fluid  -->

            <?php

                include_once("html/common/admin_footer.inc.php");
            ?>

        <script type="text/javascript">

            window.Messages || ( window.Messages = {} );

            Messages.remove = function (accountId, accessToken, itemId) {

                $.ajax({
                    type: 'POST',
                    url: '/api/v2/method/manager/app.removeMsgItem',
                    data: 'itemId=' + itemId + "&accountId=" + accountId + "&accessToken=" + accessToken,
                    timeout: 30000,
                    success: function(response) {

                        $('tr.data-item[data-id=' + itemId + ']').remove();
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
        ?>

            <tr class="data-item" data-id="<?php echo $item['id']; ?>">

                <td style="width:50px;">

                    <?php

                        if (strlen($item['fromUserPhotoUrl']) != 0) {

                            ?>
                                <span class="round" style="background-size: cover; background-image: url(<?php echo $item['fromUserPhotoUrl']; ?>)"></span>
                            <?php

                        } else {

                            ?>
                                <span class="round" style="background-size: cover; background-image: url(/assets/icons/profile_default_photo.png)"></span>
                            <?php
                        }
                    ?>
                </td>
                <td>
                    <h6><a href="/admin/profile?id=<?php echo $item['fromUserId']; ?>"><?php echo $item['fromUserFullname']; ?></a></h6>
                    <small class="text-muted">@<?php echo $item['fromUserUsername']; ?></small>
                </td>
                <td>

                    <?php

                        if (strlen($item['imageUrl']) != 0) {

                            ?>
                                <img src="<?php echo $item['imageUrl']; ?>" alt="iMac" width="80">
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

                        if (strlen($item['message']) != 0) {

                            ?>
                                <h6><?php echo $item['message']; ?></h6>
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
                    <a href="javascript:void(0)" onclick="Messages.remove('<?php echo admin::getCurrentAdminId(); ?>', '<?php echo admin::getAccessToken(); ?>', '<?php echo $item['id']; ?>'); return false;" class="text-inverse" title="" data-toggle="tooltip" data-original-title="Delete"><i class="ti-trash"></i></a>
                </td>
            </tr>

        <?php
    }