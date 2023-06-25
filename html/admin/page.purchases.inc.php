<?php

    

    if (!admin::isSession()) {

        header("Location: /admin/login");
        exit;
    }

    $stats = new stats($dbo);
    $admin = new admin($dbo);

    $page_id = "purchases";

    $css_files = array("mytheme.css");
    $page_title = "Last Purchases";

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
                        <h3 class="text-themecolor">Last Purchases</h3>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="/admin/main">Home</a></li>
                            <li class="breadcrumb-item active">Last Purchases</li>
                        </ol>
                    </div>
                </div>

                <?php

                    $payments = new payments($dbo);

                    $result = $payments->stream(0, 50);

                    $inbox_loaded = count($result['items']);

                    if ($inbox_loaded != 0) {

                        ?>

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
                                                            <th class="text-left">Id</th>
                                                            <th>Account</th>
                                                            <th>Payment Type</th>
                                                            <th>Credits</th>
                                                            <th>Amount</th>
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

    </div> <!-- End Main Wrapper -->

</body>

</html>

<?php

    function draw($item)
    {
        ?>

        <tr>
            <td class="text-left"><?php echo $item['id']; ?></td>
            <td><?php echo "<a href=\"/admin/profile?id={$item['owner']['id']}\">{$item['owner']['fullname']}</a>"; ?></td>
            <td>
                <?php

                switch ($item['paymentType']) {

                    case PT_CARD: {

                        echo "Stripe";

                        break;
                    }

                    case PT_GOOGLE_PURCHASE: {

                        echo "Google in-app purchase";

                        break;
                    }

                    case PT_APPLE_PURCHASE: {

                        echo "Apple in-app purchase";

                        break;
                    }

                    default: {

                        echo "-";

                        break;
                    }
                }
                ?>
            </td>
            <td><?php echo $item['credits']; ?></td>
            <td>
                <?php

                    if ($item['amount'] != 0) {

                        echo "USD ".$item['amount'] / 100;

                    } else {

                        echo "-";
                    }
                ?>
            </td>
            <td><?php echo date("Y-m-d H:i:s", $item['createAt']); ?></td>
        </tr>

        <?php
    }