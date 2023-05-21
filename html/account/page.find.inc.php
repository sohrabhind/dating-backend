<?php

    /*!
     * https://hindbyte.com
     * hindbyte@gmail.com
     *
     * Copyright 2012-2021 Demyanchuk Dmitry (hindbyte@gmail.com)
     */

    if (!$auth->authorize(auth::getCurrentUserId(), auth::getAccessToken())) {

        header('Location: /');
        exit;
    }

    $query = '';

    $u_online = 0;
    $u_gender = 3; // 3 = any
    $u_photo = 0; //
    $u_level_mode = 0; //
    $u_age_from = 18; //
    $u_age_to = 80; //
    $u_distance = 1000;

    $u_age = $u_age_from.",".$u_age_to;

    $search = new find($dbo);
    $search->setRequestFrom(auth::getCurrentUserId());

    $profile = new profile($dbo, auth::getCurrentUserId());
    $profileInfo = $profile->getVeryShort();
    unset($profile);

    $items_loaded = 0;

    if (isset($_GET['query'])) {

        $query = isset($_GET['query']) ? $_GET['query'] : '';

        $u_online = isset($_GET['online']) ? $_GET['online'] : 0;
        $u_gender = isset($_GET['gender']) ? $_GET['gender'] : 3;
        $u_photo = isset($_GET['photo']) ? $_GET['photo'] : 0;
        $u_level_mode = isset($_GET['level_mode']) ? $_GET['level_mode'] : 0;
        $u_age = isset($_GET['age']) ? $_GET['age'] : '18,80';
        $u_distance = isset($_GET['distance']) ? $_GET['distance'] : 30;

        $u_online = helper::clearInt($u_online);
        $u_gender = helper::clearInt($u_gender);
        $u_photo = helper::clearInt($u_photo);
        $u_level_mode = helper::clearInt($u_level_mode);
        $u_distance = helper::clearInt($u_distance);

        $query = helper::clearText($query);
        $query = helper::escapeText($query);

        $u_age = helper::clearText($u_age);
        $u_age = helper::escapeText($u_age);

        $u_age_arr = explode(",", $u_age);

        if (count($u_age_arr) > 1) {

            $u_age_from = helper::clearInt($u_age_arr[0]);
            $u_age_to = helper::clearInt($u_age_arr[1]);

            if ($u_age_from == 0 || $u_age_to == 0) {

                $u_age_from = 18;
                $u_age_to = 105;
            }

            if ($u_age_to > 130) {

                $u_age_to = 105;
            }

            if ($u_age_from >= $u_age_to) {

                $u_age_from = $u_age_to - 1; //
            }
        }
    }

    if (!empty($_POST)) {

        $itemId = isset($_POST['itemId']) ? $_POST['itemId'] : 0;
        $loaded = isset($_POST['loaded']) ? $_POST['loaded'] : 0;

        $query = isset($_POST['query']) ? $_POST['query'] : '';

        $u_online = isset($_POST['online']) ? $_POST['online'] : 0;
        $u_gender = isset($_POST['gender']) ? $_POST['gender'] : 3;
        $u_photo = isset($_POST['photo']) ? $_POST['photo'] : 0;
        $u_level_mode = isset($_POST['level_mode']) ? $_POST['level_mode'] : 0;
        $u_age_from = isset($_POST['age_from']) ? $_POST['age_from'] : 18;
        $u_age_to = isset($_POST['age_to']) ? $_POST['age_to'] : 105;
        $u_distance = isset($_POST['distance']) ? $_POST['distance'] : 30;

        $u_online = helper::clearInt($u_online);
        $u_photo = helper::clearInt($u_photo);
        $u_gender = helper::clearInt($u_gender);
        $u_level_mode = helper::clearInt($u_level_mode);
        $u_distance = helper::clearInt($u_distance);
        $u_age_from = helper::clearInt($u_age_from);
        $u_age_to = helper::clearInt($u_age_to);

        if ($u_age_from == 0 || $u_age_to == 0) {

            $u_age_from = 18;
            $u_age_to = 105;
        }

        if ($u_age_to > 130) {

            $u_age_to = 105;
        }

        if ($u_age_from >= $u_age_to) {

            $u_age_from = $u_age_to - 1; //
        }

        $itemId = helper::clearInt($itemId);
        $loaded = helper::clearInt($loaded);

        $query = helper::clearText($query);
        $query = helper::escapeText($query);

        $result = $search->start($query, $itemId, $u_gender, $u_online, $u_photo, $u_level_mode, $u_age_from, $u_age_to, $u_distance, $profileInfo['lat'], $profileInfo['lng']);

        $items_loaded = count($result['items']);

        $result['items_loaded'] = $items_loaded + $loaded;

        if ($items_loaded != 0 ) {

            ob_start();

            foreach ($result['items'] as $key => $value) {

                draw::peopleCardviewItem($value, $LANG, true, $value['age'], $LANG['label-select-age'], "red");
            }

            $result['html'] = ob_get_clean();


            if ($result['items_loaded'] >= 20) {

                ob_start();

                ?>

                    <header class="top-banner loading-banner p-0 pt-3">

                        <div class="prompt">
                            <button onclick="Search.moreItems('<?php echo $result['itemId']; ?>', '<?php  echo $query; ?>', '<?php echo $u_gender; ?>', '<?php echo $u_online; ?>', '<?php echo $u_photo; ?>', '<?php echo $u_level_mode; ?>', '<?php echo $u_age_from; ?>', '<?php echo $u_age_to; ?>', '<?php echo $u_distance; ?>'); return false;" class="button more loading-button"><?php echo $LANG['action-more']; ?></button>
                        </div>

                    </header>

                <?php

                $result['html2'] = ob_get_clean();
            }
        }

        echo json_encode($result);
        exit;
    }

    $account = new account($dbo, auth::getCurrentUserId());
    $account->setLastActive();
    unset($account);

    $page_id = "find";

    $css_files = array("my.css", "account.css");
    $page_title = $LANG['page-search']." | ".APP_TITLE;

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

                <div class="main-content search-page-content">

                    <div class="standard-page page-title-content">
                        <div class="page-title-content-inner">
                            <?php echo $LANG['page-search']; ?>
                        </div>
                        <div class="page-title-content-bottom-inner">
                            <?php echo $LANG['page-search-description']; ?>
                        </div>
                    </div>

                    <div class="standard-page <?php if ($profileInfo['lat'] == 0 || $profileInfo['lng'] == 0) {echo 'hidden';} ?>">

                        <div class="search-editbox-line">

                            <form id="search-form" class="search-container" method="get" action="/account/find">

                                <input class="search-field" name="query" id="query" autocomplete="off" placeholder="<?php echo $LANG['search-box-placeholder']; ?>" type="text" autocorrect="off" autocapitalize="off" style="outline: none;" value="<?php echo $query; ?>">

                                <button type="submit" class="btn btn-main red search-submit"><i class="iconfont icofont-search-1"></i></button>

                                <div class="dropdown">

                                    <button id="settings-button" type="" class="btn btn-main red search-settings" data-toggle="dropdown"><i class="iconfont icofont-settings"></i></button>

                                    <div class="dropdown-menu dropdown-menu-right dropdown-menu-arrow">

                                        <div class="dropdown__content no_autoloader">
                                            <div class="encounters-filter">
                                                <fieldset class="encounters-filter__field">
                                                    <div class="encounters-filter__control">
                                                        <div class="search-filter-form-line">
                                                            <h5 style="margin-top: 0px;"><?php echo $LANG['search-filters-active']; ?></h5>
                                                            <label class="search-filter-radio-button" for="online-radio-1">
                                                                <input type="radio" name="online" id="online-radio-1" value="0" <?php if ($u_online == 0) echo "checked" ?>><?php echo $LANG['search-filters-all']; ?></label>
                                                            <label class="search-filter-radio-button" for="online-radio-2">
                                                                <input type="radio" name="online" id="online-radio-2" value="1" <?php if ($u_online != 0) echo "checked" ?>><?php echo $LANG['search-filters-online']; ?></label>
                                                        </div>

                                                        <div class="search-filter-form-line">
                                                            <h5><?php echo $LANG['search-filters-gender']; ?></h5>
                                                            <label class="search-filter-radio-button" for="gender-radio-1">
                                                                <input type="radio" name="gender" id="gender-radio-1" value="3" <?php if ($u_gender == 3) echo "checked" ?>><?php echo $LANG['search-filters-all']; ?></label>
                                                            <label class="search-filter-radio-button" for="gender-radio-2">
                                                                <input type="radio" name="gender" id="gender-radio-2" value="0" <?php if ($u_gender == 0) echo "checked" ?>><?php echo $LANG['search-filters-male']; ?></label>
                                                            <label class="search-filter-radio-button" for="gender-radio-3">
                                                                <input type="radio" name="gender" id="gender-radio-3" value="1" <?php if ($u_gender == 1) echo "checked" ?>><?php echo $LANG['search-filters-female']; ?></label>
                                                            <label class="search-filter-radio-button" for="gender-radio-4">
                                                                <input type="radio" name="gender" id="gender-radio-4" value="2" <?php if ($u_gender == 2) echo "checked" ?>><?php echo $LANG['search-filters-secret']; ?></label>
                                                        </div>

                                                        <div class="search-filter-form-line">
                                                            <h5><?php echo $LANG['search-filters-photo']; ?></h5>
                                                            <label class="search-filter-radio-button" for="photo-radio-1">
                                                                <input type="radio" name="photo" id="photo-radio-1" value="0" <?php if ($u_photo == 0) echo "checked" ?>><?php echo $LANG['search-filters-all']; ?></label>
                                                            <label class="search-filter-radio-button" for="photo-radio-2">
                                                                <input type="radio" name="photo" id="photo-radio-2" value="1" <?php if ($u_photo != 0) echo "checked" ?>><?php echo $LANG['search-filters-photo-filter']; ?></label>
                                                        </div>

                                                        <div class="search-filter-form-line">
                                                            <h5><?php echo $LANG['search-filters-level-mode']; ?></h5>
                                                            <label class="search-filter-radio-button" for="level-mode-radio-1">
                                                                <input type="radio" name="level_mode" id="level-mode-radio-1" value="0" <?php if ($u_level_mode == 0) echo "checked" ?>><?php echo $LANG['search-filters-all']; ?></label>
                                                            <label class="search-filter-radio-button" for="level-mode-radio-2">
                                                                <input type="radio" name="level_mode" id="level-mode-radio-2" value="1" <?php if ($u_level_mode != 0) echo "checked" ?>><?php echo $LANG['search-filters-level-mode-on']; ?></label>
                                                        </div>

                                                        <div class="search-filter-form-line mt-3">
                                                            <h5><?php echo sprintf($LANG['search-filters-age'], "<span id=\"label-age-from\">{$u_age_from}</span>", "<span id=\"label-age-to\">{$u_age_to}</span>"); ?></h5>
                                                            <input id="age-slider" name="age" type="text" value="" data-slider-min="18" data-slider-max="105" data-slider-step="1" data-slider-value="[<?php echo $u_age_from; ?>,<?php echo $u_age_to; ?>]"/>
                                                        </div>

                                                        <div id="search-filter-distance" class="search-filter-form-line mt-3 <?php if ($profileInfo['lat'] == 0 || $profileInfo['lng'] == 0) {echo 'hidden';} ?> ">
                                                            <h5><?php echo $LANG['label-distance']; ?> <span id="distance"><?php echo $u_distance; ?></span> km</h5>
                                                            <input id="distance-slider" type="text" name="distance" data-slider-min="30" data-slider-max="2000" data-slider-step="1" data-slider-value="<?php echo $u_distance; ?>"/>
                                                        </div>

                                                    </div>
                                                </fieldset>

                                                <div class="encounters-filter__action">
                                                    <div class="button-group button-group--horizontal">
                                                        <div class="button-group__item">
                                                            <button type="submit" class="btn btn--sm btn--block btn-primary">
                                                        <span class="btn__content">
                                                            <span class="btn__text"><?php echo $LANG['action-apply']; ?></span>
                                                        </span>
                                                            </button>
                                                        </div>
                                                        <div class="button-group__item">
                                                            <button type="button" class="btn btn--sm flat  btn--block js-toggle" id="close-button">
                                                                <span class="btn__content">
                                                                    <span class="btn__text"><?php echo $LANG['action-cancel']; ?></span>
                                                                </span>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </form>

                        </div>

                    </div>

                    <div class="content-list-page">

                        <?php

                            if ($profileInfo['lat'] == 0 || $profileInfo['lng'] == 0) {

                                ?>
                                    <header class="top-banner info-banner-2 text-center d-block">
                                        <h5 class=""><?php echo $LANG['label-location-request']; ?></h5>
                                        <button class="btn blue mt-2 hidden" onclick="getLocation();"><?php echo $LANG['action-allow']; ?></button>
                                    </header>
                                <?php

                            } else {

                                $result = $search->start($query, 0, $u_gender, $u_online, $u_photo, $u_level_mode, $u_age_from, $u_age_to, $u_distance, $profileInfo['lat'], $profileInfo['lng']);

                                $items_loaded = count($result['items']);

                                if ($items_loaded == 0) {

                                    ?>

                                    <header class="top-banner info-banner">

                                        <div class="info">
                                            <?php echo $LANG['label-find-empty']; ?>
                                        </div>

                                    </header>

                                    <?php
                                }
                            }
                        ?>


                    </div>

                </div>

                <?php

                    if ($items_loaded != 0) {

                        ?>
                            <div class="main-content cardview-content">

                                <div class="standard-page cardview-container items-container">

                                    <div class="cardview items-view">

                                        <?php

                                            foreach ($result['items'] as $key => $value) {

                                                draw::peopleCardviewItem($value, $LANG, true, $value['age'], $LANG['label-select-age'], "red");
                                            }
                                        ?>
                                    </div>

                                    <?php

                                        if ($items_loaded >= 20) {

                                            ?>

                                                <header class="top-banner loading-banner p-0 pt-3">

                                                    <div class="prompt">
                                                        <button onclick="Search.moreItems('<?php echo $result['itemId']; ?>', '<?php  echo $query; ?>', '<?php echo $u_gender; ?>', '<?php echo $u_online; ?>', '<?php echo $u_photo; ?>', '<?php echo $u_level_mode; ?>', '<?php echo $u_age_from; ?>', '<?php echo $u_age_to; ?>', '<?php echo $u_distance; ?>'); return false;" class="button more loading-button"><?php echo $LANG['action-more']; ?></button>
                                                    </div>

                                                </header>

                                            <?php
                                        }
                                    ?>

                                </div>
                            </div>
                        <?php
                    }
                ?>

            </div>

        </div>

    </div>

        <?php

            include_once("html/common/site_footer.inc.php");
        ?>

        <script type="text/javascript">

            var items_loaded = <?php echo $items_loaded; ?>;

            var lat = <?php echo $profileInfo['lat']; ?>;
            var lng = <?php echo $profileInfo['lng']; ?>;

            var szShowFilters = "<?php echo $LANG['search-filters-show']; ?>";
            var szHideFilters = "<?php echo $LANG['search-filters-hide']; ?>";

            strings.sz_message_location_request = "<?php echo $LANG['label-location-request']; ?>";
            strings.sz_message_location_denied = "<?php echo $LANG['label-location-denied']; ?>";
            strings.sz_message_location_unsupported = "<?php echo $LANG['label-location-unsupported']; ?>";

            window.Search || ( window.Search = {} );

            Search.moreItems = function (offset, query, gender, online, photo, level_mode, age_from, age_to, distance) {

                $('button.loading-button').attr("disabled", "disabled");

                $.ajax({
                    type: 'POST',
                    url: '/account/find',
                    data: 'itemId=' + offset + "&loaded=" + items_loaded + "&query=" + query + "&gender=" + gender + "&online=" + online + "&photo=" + photo + "&level_mode=" + level_mode + "&age_from=" + age_from + "&age_to=" + age_to  + "&distance=" + distance,
                    dataType: 'json',
                    timeout: 30000,
                    success: function(response) {

                        $('.loading-banner').remove();

                        if (response.hasOwnProperty('html')){

                            $("div.items-view").append(response.html);
                        }

                        if (response.hasOwnProperty('html2')) {

                            $("div.items-container").append(response.html2);
                        }

                        items_loaded = response.items_loaded;
                    },
                    error: function(xhr, type){

                        $('button.loading-button').removeAttr("disabled");
                    }
                });
            };

            function filtersToggle() {

                if ($("div.search-filters").hasClass('hide')) {

                    $("div.search-filters").removeClass('hide')
                    $("span.search-filters-toggle").text(szHideFilters);

                } else {

                    $("div.search-filters").addClass('hide')
                    $("span.search-filters-toggle").text(szShowFilters);
                }
            }

            $(document).ready(function() {

                $("#age-slider").slider();

                $("#age-slider").on("change", function(slideEvt) {

                    var str = slideEvt.value.newValue + "";
                    var arr = str.split(',');

                    $("#label-age-from").text(arr[0]);
                    $("#label-age-to").text(arr[1]);
                });

                $("#distance-slider").slider();

                $("#distance-slider").on("change", function(slideEvt) {

                    $("#distance").text(slideEvt.value.newValue);
                });

                if (lat == 0 && lng == 0) {

                    if (navigator.geolocation) {

                        $('.info-banner-2').find('h5').text(strings.sz_message_location_request);
                        $('.info-banner-2').find('button').removeClass("hidden");

                    } else {

                        $('.info-banner-2').find('h5').text(strings.sz_message_location_unsupported);
                    }

                }
            });

            function getLocation() {

                var watchId = navigator.geolocation.watchPosition(function(position) {

                    lat = position.coords.latitude;
                    lng = position.coords.longitude;

                    console.log("Lat: " + position.coords.latitude);
                    console.log("Lng: " + position.coords.longitude);

                    if (lat != 0 && lng != 0) {

                        $('.info-banner-2').addClass("hidden");

                        navigator.geolocation.clearWatch(watchId);

                        $('div#search-filter-distance').removeClass('hidden');

                        setLocation(lat, lng);
                    }

                }, function(error) {

                    if (error.code == error.PERMISSION_DENIED) {

                        $('.info-banner-2').find('h5').text(strings.sz_message_location_denied);
                        $('.info-banner-2').find('button').addClass("hidden");
                    }

                }, {

                    maximumAge: Infinity,
                    timeout: Infinity
                });

            }

            function setLocation(lat, lng) {

                $.ajax({
                    type: 'POST',
                    url: '/api/' + options.api_version + '/method/account.setGeoLocation',
                    data: 'accountId=' + account.id + "&accessToken=" + account.accessToken + "&lat=" + lat + "&lng=" + lng,
                    dataType: 'json',
                    timeout: 30000,
                    success: function(response){

                        if (response.hasOwnProperty('error')) {

                            if (response.error === false) {

                                document.location.reload();
                            }
                        }
                    },
                    error: function(xhr, type){

                        //
                    }
                });
            }

        </script>

</body>
</html>
