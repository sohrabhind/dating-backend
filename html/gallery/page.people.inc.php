<?php

    /*!
    * ifsoft.co.uk
    *
    * http://ifsoft.com.ua, https://ifsoft.co.uk, https://hindbyte.com
    * hindbyte@gmail.com
    *
    * Copyright 2012-2020 Demyanchuk Dmitry (hindbyte@gmail.com)
    */

    if (!defined("APP_SIGNATURE")) {

        header("Location: /");
        exit;
    }

    if (!auth::isSession()) {

        header("Location: /");
        exit;
    }

    $profileId = $helper->getUserId($request[0]);

    $itemExists = true;

    $profile = new profile($dbo, $profileId);

    $profile->setRequestFrom(auth::getCurrentUserId());
    $profileInfo = $profile->get();

    if ($profileInfo['error']) {

        include_once("html/error.inc.php");
        exit;
    }

    if ($profileInfo['state'] != ACCOUNT_STATE_ENABLED) {

        include_once("html/error.inc.php");
        exit;
    }

    $gallery = new gallery($dbo);
    $gallery->setRequestFrom(auth::getCurrentUserId());

    $itemId = helper::clearInt($request[2]);

    $itemInfo = $gallery->info($itemId);

    if ($itemInfo['error']) {

        // Missing
        $itemExists = false;
    }

    if ($itemExists && $itemInfo['removeAt'] != 0) {

        // Missing
        $itemExists = false;
    }

    if ($itemExists && $profileInfo['id'] != $itemInfo['owner']['id']) {

        // Missing
        $itemExists = false;
    }

    if ($itemExists && auth::getCurrentUserId() != $itemInfo['owner']['id']) {

        $settings = new settings($dbo);
        $settings_arr = $settings->get();
        // Missing
        $itemExists = false;
        
    }

    $items_all = 0;

    if ($itemExists) {

        $items_all = $itemInfo['likesCount'];
    }

    $items_loaded = 0;

    if (!empty($_POST)) {

        $itemId = isset($_POST['itemId']) ? $_POST['itemId'] : 0;
        $loaded = isset($_POST['loaded']) ? $_POST['loaded'] : 0;

        $itemId = helper::clearInt($itemId);
        $loaded = helper::clearInt($loaded);

        $result = $gallery->getLikes($itemInfo['id'], $itemId);

        $items_loaded = count($result['items']);

        $result['items_loaded'] = $items_loaded + $loaded;
        $result['items_all'] = $items_all;

        if ($items_loaded != 0) {

            ob_start();

            foreach ($result['items'] as $key => $value) {

                draw::peopleCardviewItem($value, $LANG, true, $value['age'], $LANG['label-select-age'], "red");
            }

            $result['html'] = ob_get_clean();

            if ($result['items_loaded'] < $items_all) {

                ob_start();

                ?>

                <header class="top-banner loading-banner">

                    <div class="prompt">
                        <button onclick="Items.more('/<?php echo $profileInfo['username'];  ?>/gallery/<?php echo $itemInfo['id'];  ?>/people', '<?php echo $result['itemIndex']; ?>'); return false;" class="button more loading-button"><?php echo $LANG['action-more']; ?></button>
                    </div>

                </header>

                <?php

                $result['banner'] = ob_get_clean();
            }
        }

        echo json_encode($result);
        exit;
    }

    $access_denied = false;

    if ($profileInfo['id'] != auth::getCurrentUserId() && $profileInfo['allowShowMyGallery'] == 1 && $itemInfo['showInStream'] == 0) {

        $access_denied = true;
    }

    $page_id = "people";

    $css_files = array("main.css", "my.css");
    $page_title = $LANG['page-likes']." | ".APP_TITLE;

    include_once("html/common/site_header.inc.php");

?>

<body class="">


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
                            <?php echo $LANG['page-likes']; ?>
                        </div>
                    </div>

                    <div class="content-list-page empty-banner-container">

                        <?php

                            if ($itemExists && !$access_denied) {

                                $result = $gallery->getLikes($itemInfo['id'], 0);

                                $items_loaded = count($result['items']);

                                if ($items_loaded == 0) {

                                    ?>

                                        <header class="top-banner info-banner empty-list-banner">


                                        </header>

                                    <?php
                                }
                            }
                        ?>

                    </div>

                </div>

                <div class="main-content cardview-content">

                    <div class="standard-page cardview-container items-container">

                        <?php

                            if ($access_denied) {

                                ?>

                                <div class="card information-banner border-0">
                                    <div class="card-header">
                                        <div class="card-body">
                                            <h5 class="m-0"><?php echo $LANG['label-error-permission']; ?></h5>
                                        </div>
                                    </div>
                                </div>

                                <?php

                            } else {

                                if ($itemExists) {

                                    if ($items_loaded != 0) {

                                        ?>

                                        <div class="cardview items-view">

                                            <?php

                                            foreach ($result['items'] as $key => $value) {

                                                draw::peopleCardviewItem($value, $LANG, true, $value['age'], $LANG['label-select-age'], "red");
                                            }
                                            ?>

                                        </div>

                                        <?php

                                        if ($items_all > 20) {

                                            ?>

                                            <header class="top-banner loading-banner p-0 pt-3">

                                                <div class="prompt">
                                                    <button onclick="Items.more('/<?php echo $profileInfo['username'];  ?>/gallery/<?php echo $itemInfo['id'];  ?>/people', '<?php echo $result['itemIndex']; ?>'); return false;" class="button more loading-button"><?php echo $LANG['action-more']; ?></button>
                                                </div>

                                            </header>

                                            <?php
                                        }
                                    }

                                } else {

                                    ?>

                                    <div class="card information-banner">
                                        <div class="card-header">
                                            <div class="card-body">
                                                <h5 class="m-0"><?php echo $LANG['label-item-missing']; ?></h5>
                                            </div>
                                        </div>
                                    </div>

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

    </script>


</body
</html>