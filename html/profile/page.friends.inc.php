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

    if (!$auth->authorize(auth::getCurrentUserId(), auth::getAccessToken())) {

        header('Location: /');
        exit;
    }

    $profileId = $helper->getUserId($request[0]);

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

    $access_denied = false;

    if ($profileInfo['id'] != auth::getCurrentUserId() && !$profileInfo['friend'] && $profileInfo['allowShowMyFriends'] == 1) {

        $access_denied = true;
    }

    if ($profileInfo['id'] == auth::getCurrentUserId()) {

        $profile = new account($dbo, auth::getCurrentUserId());

        $profile->setLastActive();
        $profile->setLastFriendsView();
    }

    $friends = new friends($dbo, auth::getCurrentUserId());
    $friends->setRequestFrom(auth::getCurrentUserId());

    $items_all = $profileInfo['friendsCount'];
    $items_loaded = 0;

    if (!empty($_POST) && !$access_denied) {

        $itemId = isset($_POST['itemId']) ? $_POST['itemId'] : 0;
        $loaded = isset($_POST['loaded']) ? $_POST['loaded'] : '';

        $itemId = helper::clearInt($itemId);
        $loaded = helper::clearInt($loaded);

        $result = $friends->get($itemId);

        $items_loaded = count($result['items']);

        $result['items_loaded'] = $items_loaded + $loaded;
        $result['items_all'] = $items_all;

        if ($items_loaded != 0) {

            ob_start();

            foreach ($result['items'] as $key => $value) {

                draw::friendItem($value, $LANG, $helper);
            }

            $result['html'] = ob_get_clean();

            if ($result['items_loaded'] < $items_all) {

                ob_start();

                ?>

                    <header class="top-banner loading-banner">

                        <div class="prompt">
                            <button onclick="Items.more('/<?php echo $profileInfo['username']; ?>/friends', '<?php echo $result['itemId']; ?>'); return false;" class="button more loading-button"><?php echo $LANG['action-more']; ?></button>
                        </div>

                    </header>

                <?php

                $result['html2'] = ob_get_clean();
            }
        }

        echo json_encode($result);
        exit;
    }

    $page_id = "friends";

    $css_files = array("my.css", "account.css");
    $page_title = $LANG['page-friends']." | ".APP_TITLE;

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
                            <?php echo $LANG['page-friends']; ?>
                        </div>
                        <div class="page-title-content-bottom-inner">
                            <?php
                                if ($profileInfo['id'] == auth::getCurrentUserId()) {

                                    echo $LANG['label-friends-sub-title'];

                                } else {

                                    echo sprintf($LANG['label-friends-sub-title-2'], '<strong>'.$profileInfo['fullname'].'</strong>'); ;
                                }
                            ?>
                        </div>
                    </div>

                    <div class="content-list-page">

                        <?php

                            $result = $friends->get(0);

                            $items_loaded = count($result['items']);

                            if ($items_loaded == 0 && !$access_denied) {

                                ?>

                                <header class="top-banner info-banner empty-list-banner">


                                </header>

                                <?php

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

                            if ($items_loaded != 0) {

                                ?>

                                <div class="cardview items-view">

                                    <?php

                                    foreach ($result['items'] as $key => $value) {

                                        draw::friendItem($value, $LANG, $helper);
                                    }
                                    ?>
                                </div>

                                <?php

                                if ($items_all > 20) {

                                    ?>

                                    <header class="top-banner loading-banner">

                                        <div class="prompt">
                                            <button onclick="Items.more('/<?php echo $profileInfo['username']; ?>/friends', '<?php echo $result['itemId']; ?>'); return false;" class="button more loading-button"><?php echo $LANG['action-more']; ?></button>
                                        </div>

                                    </header>

                                    <?php
                                }
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

</body>
</html>