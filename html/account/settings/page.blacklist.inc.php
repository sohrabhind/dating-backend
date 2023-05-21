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
        exit;
    }

    $profile = new profile($dbo, auth::getCurrentUserId());

    $blacklist = new blacklist($dbo);
    $blacklist->setRequestFrom(auth::getCurrentUserId());

    $items_all = $blacklist->myActiveItemsCount();
    $items_loaded = 0;

    if (!empty($_POST)) {

        $itemId = isset($_POST['itemId']) ? $_POST['itemId'] : '';
        $loaded = isset($_POST['loaded']) ? $_POST['loaded'] : '';

        $itemId = helper::clearInt($itemId);
        $loaded = helper::clearInt($loaded);

        $result = $blacklist->get($itemId);

        $items_loaded = count($result['items']);

        $result['items_loaded'] = $items_loaded + $loaded;
        $result['items_all'] = $items_all;

        if ($items_loaded != 0) {

            ob_start();

            foreach ($result['items'] as $key => $value) {

                draw::blackListItem($value, $LANG, $helper);
            }

            if ($result['items_loaded'] < $items_all) {

                ?>

                <header class="top-banner loading-banner">

                    <div class="prompt">
                        <button onclick="BlackList.moreItems('<?php echo $result['itemId']; ?>'); return false;" class="button green loading-button"><?php echo $LANG['action-more']; ?></button>
                    </div>

                </header>

            <?php
            }

            $result['html'] = ob_get_clean();
        }

        echo json_encode($result);
        exit;
    }

    $page_id = "blacklist";

    $css_files = array("main.css", "my.css");
    $page_title = $LANG['page-blacklist']." | ".APP_TITLE;

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

                    <div class="content-list-page">

                        <div class="standard-page" style="padding-bottom: 0">

                        <h1><?php echo $LANG['page-blacklist']; ?></h1>

                            <div class="tab-container">
                                <nav class="tabs">
                                    <a href="/account/settings"><span class="tab"><?php echo $LANG['page-profile-settings']; ?></span></a>
                                    <a href="/account/settings/privacy"><span class="tab"><?php echo $LANG['page-privacy-settings']; ?></span></a>
                                    <a href="/account/balance"><span class="tab"><?php echo $LANG['page-balance']; ?></span></a>
                                    <a href="/account/settings/services"><span class="tab"><?php echo $LANG['label-services']; ?></span></a>
                                    <a href="/account/settings/password"><span class="tab"><?php echo $LANG['label-password']; ?></span></a>
                                    <a href="/account/settings/blacklist"><span class="tab active"><?php echo $LANG['page-blacklist']; ?></span></a>
                                    <a href="/account/settings/otp"><span class="tab"><?php echo $LANG['page-otp']; ?></span></a>
                                    <a href="/account/settings/deactivation"><span class="tab"><?php echo $LANG['page-deactivate-account']; ?></span></a>
                                </nav>
                            </div>

                        </div>

                        <?php

                        $result = $blacklist->get(0);

                        $items_loaded = count($result['items']);

                        if ($items_loaded != 0) {

                            ?>

                                <ul class="cards-list content-list">

                                    <?php

                                        foreach ($result['items'] as $key => $value) {

                                            draw::blackListItem($value, $LANG, $helper);
                                        }
                                    ?>

                                </ul>

                            <?php

                        } else {

                            ?>

                            <header class="top-banner info-banner empty-list-banner">

                            </header>

                            <?php
                        }
                        ?>

                        <?php

                            if ($items_all > 20) {

                                ?>

                                <header class="top-banner loading-banner">

                                    <div class="prompt">
                                        <button onclick="BlackList.moreItems('<?php echo $result['itemId']; ?>'); return false;" class="button green loading-button"><?php echo $LANG['action-more']; ?></button>
                                    </div>

                                </header>

                                <?php
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

            window.BlackList || ( window.BlackList = {} );

            BlackList.moreItems = function (offset) {

                $.ajax({
                    type: 'POST',
                    url: '/account/settings/blacklist',
                    data: 'itemId=' + offset + "&loaded=" + items_loaded,
                    dataType: 'json',
                    timeout: 30000,
                    success: function(response){

                        $('header.loading-banner').remove();

                        if (response.hasOwnProperty('html')){

                            $("ul.content-list").append(response.html);
                        }

                        items_loaded = response.items_loaded;
                        items_all = response.items_all;
                    },
                    error: function(xhr, type){

                        $('a.more_link').show();
                        $('a.loading_link').hide();
                    }
                });
            };

            BlackList.remove = function(id, profile_id) {

                $.ajax({
                    type: 'POST',
                    url: "/api/" + options.api_version + "/method/blacklist.remove",
                    data: "accountId=" + account.id + "&accessToken=" + account.accessToken + "&profileId=" + profile_id,
                    dataType: 'json',
                    timeout: 30000,
                    success: function(response){

                        $('li.card-item[data-id=' + id + ']').remove();
                    },
                    error: function(xhr, type){

                    }
                });
            };

        </script>

</body>
</html>