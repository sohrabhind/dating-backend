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
    }

    $profileId = $helper->getUserId($request[0]);

    $profile = new profile($dbo, $profileId);

    $profile->setRequestFrom(auth::getCurrentUserId());
    $profileInfo = $profile->get();

    $gifts = new gift($dbo);
    $gifts->setRequestFrom(auth::getCurrentUserId());

    $access_denied = false;

    if ($profileInfo['id'] != auth::getCurrentUserId() && !$profileInfo['friend'] && $profileInfo['allowShowMyGifts'] == 1) {

        $access_denied = true;
    }

    $items_all = $gifts->count();
    $items_loaded = 0;

    if (!empty($_POST)) {

        $itemId = isset($_POST['itemId']) ? $_POST['itemId'] : 0;
        $loaded = isset($_POST['loaded']) ? $_POST['loaded'] : '';

        $itemId = helper::clearInt($itemId);
        $loaded = helper::clearInt($loaded);

        $result = $gifts->get($profileId, $itemId);

        $items_loaded = count($result['items']);

        $result['items_loaded'] = $items_loaded + $loaded;
        $result['items_all'] = $items_all;

        if ($items_loaded != 0) {

            ob_start();

            foreach ($result['items'] as $key => $value) {

                draw::giftItem($value, $LANG, $helper, false, true);
            }

            $result['html'] = ob_get_clean();


            if ($result['items_loaded'] < $items_all) {

                ob_start();

                ?>

                <header class="top-banner loading-banner p-0 pt-3">

                    <div class="prompt">
                        <button onclick="Items.more('/<?php echo $profileInfo['username']; ?>/gifts', '<?php echo $result['itemId']; ?>'); return false;" class="button more loading-button"><?php echo $LANG['action-more']; ?></button>
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

    $page_id = "gifts";

    $css_files = array();
    $page_title = $LANG['page-gifts']." | ".APP_TITLE;

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
                            <?php echo $LANG['page-gifts']; ?>
                        </div>
                        <div class="page-title-content-bottom-inner">
                            <?php echo $LANG['page-gifts-sub-title']; ?>
                        </div>
                    </div>

                    <div class="content-list-page empty-banner-container">

                        <?php

                        $result = $gifts->get($profileId,0);

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

                    <div class="standard-page cardview-container p-0 items-container">

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

                                        draw::giftItem($value, $LANG, $helper, false, true);
                                    }

                                    ?>
                                </div>
                                <?php

                                if ($items_all > 20) {

                                    ?>

                                    <header class="top-banner loading-banner p-0 pt-3">

                                        <div class="prompt">
                                            <button onclick="Items.more('<?php echo $profileInfo['username']; ?>/gifts', '<?php echo $result['itemId']; ?>'); return false;" class="button more loading-button"><?php echo $LANG['action-more']; ?></button>
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

            var auth_token = "<?php echo auth::getAuthenticityToken(); ?>";
            var username = "<?php echo auth::getCurrentUserLogin(); ?>";

            window.Gifts || (window.Gifts = {});

            Gifts.more = function (username, offset) {

                $('button.loading-button').attr("disabled", "disabled");

                $.ajax({
                    type: 'POST',
                    url: '/' + username + '/gifts',
                    data: 'itemId=' + offset + "&loaded=" + items_loaded,
                    dataType: 'json',
                    timeout: 30000,
                    success: function(response){

                        $('header.loading-banner').remove();

                        if (response.hasOwnProperty('html')){

                            $("div.items-view").append(response.html);
                        }

                        if (response.hasOwnProperty('banner')){

                            $("div.items-container").append(response.banner);
                        }

                        items_loaded = response.items_loaded;
                        items_all = response.items_all;
                    },
                    error: function(xhr, type){

                        $('button.loading-button').removeAttr("disabled");
                    }
                });
            };

            Gifts.remove = function (itemId) {

                $('div.gallery-item[data-id=' + itemId + ']').hide();

                $.ajax({
                    type: 'POST',
                    url: '/api/' + options.api_version + "/method/gifts.remove",
                    data: 'accountId=' + account.id + '&accessToken=' + account.accessToken + '&itemId=' + itemId,
                    dataType: 'json',
                    timeout: 30000,
                    success: function(response){

                        $('div.gallery-item[data-id=' + itemId + ']').remove();

                        if (options.pageId === "gifts" && response.hasOwnProperty('html')) {

                            //
                        }
                    },
                    error: function(xhr, type){

                        $('div.gallery-item[data-id=' + itemId + ']').show();
                    }
                });
            };

        </script>


</body
</html>
