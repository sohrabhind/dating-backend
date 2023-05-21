<?php

    /*!
     * ifsoft.co.uk
     *
     * http://ifsoft.com.ua, https://ifsoft.co.uk, https://hindbyte.com
     * hindbyte@gmail.com
     *
     * Copyright 2012-2020 Demyanchuk Dmitry (hindbyte@gmail.com)
     */

    if (!$auth->authorize(auth::getCurrentUserId(), auth::getAccessToken())) {

        header('Location: /');
    }

    $feed = new feed($dbo);
    $feed->setRequestFrom(auth::getCurrentUserId());

    $items_all = $feed->count();
    $items_loaded = 0;

    if (!empty($_POST)) {

        $itemId = isset($_POST['itemId']) ? $_POST['itemId'] : 0;
        $loaded = isset($_POST['loaded']) ? $_POST['loaded'] : '';

        $itemId = helper::clearInt($itemId);
        $loaded = helper::clearInt($loaded);

        $result = $feed->get($itemId);

        $items_loaded = count($result['items']);

        $result['items_loaded'] = $items_loaded + $loaded;
        $result['items_all'] = $items_all;

        if ($items_loaded != 0) {

            ob_start();

            foreach ($result['items'] as $key => $value) {

                draw::galleryItem($value, $LANG, $helper, false, true);
            }

            $result['html'] = ob_get_clean();


            if ($result['items_loaded'] < $items_all) {

                ob_start();

                ?>

                <header class="top-banner loading-banner p-0 pt-3">

                    <div class="prompt">
                        <button onclick="Items.more('/account/feed', '<?php echo $result['itemId']; ?>'); return false;" class="button more loading-button"><?php echo $LANG['action-more']; ?></button>
                    </div>

                </header>

                <?php

                $result['html2'] = ob_get_clean();
            }
        }

        echo json_encode($result);
        exit;
    }

    auth::newAuthenticityToken();

    $page_id = "feed";

    $css_files = array();
    $page_title = $LANG['page-media-feed']." | ".APP_TITLE;

include_once("html/common/site_header.inc.php");

?>

<body class="width-page">

    <?php
        include_once("html/common/site_topbar.inc.php");
    ?>

    <div class="wrap content-page">

        <div class="main-column row">

            <?php

                include_once("html/common/site_sidenav.inc.php");
            ?>

            <div class="col-lg-9 col-md-12" id="content">

                <div class="main-content">

                    <div class="standard-page page-title-content">

                        <div class="page-title-content-inner">
                            <?php echo $LANG['page-media-feed']; ?>
                        </div>
                        <div class="page-title-content-bottom-inner">
                            <?php echo $LANG['page-media-feed-sub-title']; ?>
                        </div>
                    </div>

                    <div class="content-list-page empty-banner-container">

                        <?php

                        $result = $feed->get(0);

                        $items_loaded = count($result['items']);

                        if ($items_loaded == 0) {

                            ?>

                            <header class="top-banner info-banner empty-list-banner">

                            </header>
                            <?php
                        }
                        ?>


                    </div>

                </div>

                <div class="main-content cardview-content">

                    <div class="standard-page cardview-container p-0 items-container">

                        <?php

                        if ($items_loaded != 0) {

                            ?>
                            <div class="cardview items-view">
                                <?php

                                foreach ($result['items'] as $key => $value) {

                                    draw::galleryItem($value, $LANG, $helper, false, true);
                                }

                                ?>
                            </div>
                            <?php

                            if ($items_all > 20) {

                                ?>

                                <header class="top-banner loading-banner p-0 pt-3">

                                    <div class="prompt">
                                        <button onclick="Items.more('/account/feed', '<?php echo $result['itemId']; ?>'); return false;" class="button more loading-button"><?php echo $LANG['action-more']; ?></button>
                                    </div>

                                </header>

                                <?php
                            }
                        }
                        ?>

                    </div>


                </div>
            </div>
        </div>

    </div>

    <?php

        include_once("html/common/site_footer.inc.php");
    ?>

        <script type="text/javascript">

            var items_all = <?php echo $items_all; ?>;
            var items_loaded = <?php echo $items_loaded; ?>;

            var auth_token = "<?php echo auth::getAuthenticityToken(); ?>";
            var username = "<?php echo auth::getCurrentUserLogin(); ?>";

        </script>


</body
</html>
