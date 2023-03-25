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

    $gender = 3; // any
    $distance = 100;
    $liked = 1; // on

    if (isset($_COOKIE['hotgame_liked'])) {

        $liked = isset($_COOKIE['hotgame_liked']) ? $_COOKIE['hotgame_liked'] : 1; // on

        $liked = helper::clearInt($liked);
    }


    if (isset($_COOKIE['hotgame_gender'])) {

        $gender = isset($_COOKIE['hotgame_gender']) ? $_COOKIE['hotgame_gender'] : 3; // any

        $gender = helper::clearInt($gender);
    }

    if (isset($_COOKIE['hotgame_distance'])) {

        $distance = isset($_COOKIE['hotgame_distance']) ? $_COOKIE['hotgame_distance'] : 30;

        $distance = helper::clearInt($distance);
    }

    $profile = new profile($dbo, auth::getCurrentUserId());
    $profileInfo = $profile->getVeryShort();
    unset($profile);

    $page_id = "hotgame";

    $css_files = array("my.css", "account.css");
    $page_title = $LANG['page-hotgame']." | ".APP_TITLE;

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
                            <?php echo $LANG['page-hotgame']; ?>
                        </div>
                        <div class="page-title-content-bottom-inner">
                            <?php echo $LANG['page-hotgame-desc']; ?>
                        </div>

                        <div id="hotgame-settings-button" class="hidden page-title-content-extra <?php if ($profileInfo['lat'] == 0 && $profileInfo['lng'] == 0) {echo "hidden";} ?>">

                            <div class="dropdown">

                                <a id="settings-button" class="extra-button button red" data-toggle="dropdown" href="javascript:void(0)" ><i class="iconfont icofont-settings"></i></a>

                                <div class="dropdown-menu dropdown-menu-right dropdown-menu-arrow">

                                    <div class="dropdown__content no_autoloader" style="min-width: 350px">
                                        <div class="encounters-filter">

                                            <fieldset class="encounters-filter__field">
                                                <div class="encounters-filter__control">
                                                    <div class="search-filter-form-line">
                                                        <h5><?php echo $LANG['label-sex']; ?></h5>
                                                        <label class="search-filter-radio-button" for="gender-radio-4">
                                                            <input type="radio" name="gender" id="gender-radio-4" value="3" <?php if ($gender == 3) echo "checked" ?>><?php echo $LANG['search-filters-any']; ?></label>
                                                        <label class="search-filter-radio-button" for="gender-radio-1">
                                                            <input type="radio" name="gender" id="gender-radio-1" value="0" <?php if ($gender == 0) echo "checked" ?>><?php echo $LANG['search-filters-male']; ?></label>
                                                        <label class="search-filter-radio-button" for="gender-radio-2">
                                                            <input type="radio" name="gender" id="gender-radio-2" value="1" <?php if ($gender == 1) echo "checked" ?>><?php echo $LANG['search-filters-female']; ?></label>
                                                        <label class="search-filter-radio-button" for="gender-radio-3">
                                                            <input type="radio" name="gender" id="gender-radio-3" value="2" <?php if ($gender == 2) echo "checked" ?>><?php echo $LANG['search-filters-secret']; ?></label>
                                                    </div>

                                                    <div class="search-filter-form-line">
                                                        <h5><?php echo $LANG['search-filters-addition']; ?></h5>

                                                        <div class="encounters-filter__control">
                                                            <div class="checkbox-field mt-2">

                                                                <div class="checkbox-field__item">
                                                                    <input type="checkbox" name="matches" id="matches" class="checkbox-field__input" <?php if ($matches == 1) echo "checked"; ?>>
                                                                    <label for="matches" class="checkbox-field__label">
                                                                        <div class="checkbox-field__icon"></div>
                                                                        <span class="checkbox-field__text"><?php echo $LANG['search-filters-matches']; ?></span>
                                                                    </label>
                                                                </div>

                                                                <div class="checkbox-field__item">
                                                                    <input type="checkbox" name="liked" id="liked" class="checkbox-field__input" <?php if ($liked == 1) echo "checked"; ?>>
                                                                    <label for="liked" class="checkbox-field__label">
                                                                        <div class="checkbox-field__icon"></div>
                                                                        <span class="checkbox-field__text"><?php echo $LANG['search-filters-liked']; ?></span>
                                                                    </label>
                                                                </div>

                                                            </div>
                                                        </div>

                                                    </div>

                                                    <div class="search-filter-form-line mt-3">
                                                        <h5><?php echo $LANG['label-distance']; ?> <span id="distance"><?php echo $distance; ?></span> km</h5>
                                                        <input id="distance-slider" type="text" name="distance" data-slider-min="30" data-slider-max="1500" data-slider-step="1" data-slider-value="<?php echo $distance; ?>"/>
                                                    </div>

                                                </div>
                                            </fieldset>

                                            <div class="encounters-filter__action">

                                                <div class="button-group button-group--horizontal">

                                                    <div class="button-group__item">
                                                        <button type="button" class="btn btn--sm btn--block btn-primary" id="apply-button">
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

                        </div>
                    </div>

                    <div class="content-list-page">

                        <div id="hotgame-card" class="card hotgame-card hidden">

                            <div class="card-header border-0">

                                <div class="row">

                                    <div class="col-12 col-md-8">

                                        <div class="d-block d-md-inline-block text-center text-md-left mt-2 mt-md-0">

                                            <button id="hotgame-like-button" class="btn btn-hotgame-like btn-hotgame mx-1">
                                                <i class="iconfont icofont-heart"></i>
                                            </button>

                                            <button id="hotgame-skip-button" class="btn btn-hotgame-skip btn-hotgame mx-1">
                                                <i class="iconfont icofont-close"></i>
                                            </button>

                                        </div>

                                        <div class="d-block d-md-inline-block text-center text-md-left my-4 mt-md-0">
                                            <a id="hotgame-profile-fullname" class="hotgame-link ml-md-3" href="/">Fullname</a>
                                        </div>

                                    </div>

                                    <div class="col-12 col-md-4 text-center text-md-right d-none d-md-block">

                                        <a id="hotgame-profile-button" href="/" class="btn btn-hotgame-action btn-hotgame mx-1">
                                            <i class="iconfont icofont-user-alt-4"></i>
                                        </a>

                                    </div>

                                </div>

                            </div>
                            
                            <div class="card-body p-0">
                                <div class="hotagame-container d-block">

                                    <span class="card-loader-container">
                                        <div class="loader"><i class="ic icon-spin icon-spin"></i></div>
                                    </span>

                                    <img id="hotgame-profile-photo" src="/photo/thumb_big_da5bffd.jpg">

                                    <img id="hotgame-status-image" class="hotgame-status-image hidden" src="/assets/img/ic_hotgame_match.png">

                                </div>
                            </div>

                        </div>

                        <header class="top-banner info-banner loading-banner px-0 py-5 bg-transparent position-relative hotgame-loading hidden">
                            <span class="card-loader-container">
                                <div class="loader"><i class="ic icon-spin icon-spin"></i></div>
                            </span>
                        </header>

                        <header class="top-banner info-banner-2 text-center d-block hotgame-error hidden">
                            <h5 class=""><?php echo $LANG['label-hotgame-empty-list']; ?></h5>
                        </header>

                        <header class="top-banner info-banner-2 text-center d-block hotgame-location hidden">
                            <h5 class=""><?php echo $LANG['label-location-request']; ?></h5>
                            <button class="btn blue mt-2 hidden" onclick="getLocation();"><?php echo $LANG['action-allow']; ?></button>
                        </header>

                    </div>

                </div>

                <div class="main-content cardview-content">

                    <div class="standard-page cardview-container items-container">

                        <?php


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

            var itemId = 0;
            var itemIndex = 0;
            var items = [];

            var lat = <?php echo $profileInfo['lat']; ?>;
            var lng = <?php echo $profileInfo['lng']; ?>;

            strings.sz_message_location_request = "<?php echo $LANG['label-location-request']; ?>";
            strings.sz_message_location_denied = "<?php echo $LANG['label-location-denied']; ?>";
            strings.sz_message_location_unsupported = "<?php echo $LANG['label-location-unsupported']; ?>";

            var $loading_container = $('header.hotgame-loading');
            var $error_container = $('header.hotgame-error');
            var $location_container = $('header.hotgame-location');
            var $hotgame_container = $('#hotgame-card');

            var $hotgame_like_btn = $('#hotgame-like-button');
            var $hotgame_skip_btn = $('#hotgame-skip-button');
            var $hotgame_profile_btn = $('#hotgame-profile-button');
            var $hotgame_profile_fullname = $('#hotgame-profile-fullname');
            var $hotgame_profile_photo = $('#hotgame-profile-photo');

            var $hotgame_status_image = $('#hotgame-status-image');

            var $hotgame_filters_btn = $('#hotgame-settings-button');

            $(document).ready(function() {

                $("#distance-slider").slider();

                $("#distance-slider").on("change", function(slideEvt) {

                    $("#distance").text(slideEvt.value.newValue);
                });

                if (lat == 0 && lng == 0) {

                    $loading_container.addClass('hidden');

                    $location_container.removeClass('hidden');

                    if (navigator.geolocation) {

                        $location_container.find('h5').text(strings.sz_message_location_request);
                        $location_container.find('button').removeClass("hidden");

                    } else {

                        $location_container.find('h5').text(strings.sz_message_location_unsupported);
                    }

                } else {

                    getItems();
                }

                $("#apply-button").click(function() {

                    $(this).parents('.dropdown').find('#settings-button').dropdown('toggle');

                    itemId = 0;

                    getItems();
                });

                $("#hotgame-skip-button").click(function() {

                    if (itemIndex < items.length - 1) {

                        itemIndex++;

                        showHotgameScreen(itemIndex);

                    } else {

                        if (itemIndex == items.length - 1) {

                            getItems();
                        }
                    }
                });

                $("#hotgame-like-button").click(function() {

                    $hotgame_like_btn.attr('disabled', 'disabled');
                    $hotgame_skip_btn.attr('disabled', 'disabled');

                    $.ajax({
                        type: 'POST',
                        url: '/api/' + options.api_version + '/method/profile.like',
                        data: 'accessToken=' + account.accessToken + "&accountId=" + account.id + "&profileId=" + items[itemIndex].id,
                        dataType: 'json',
                        timeout: 30000,
                        success: function(response) {

                            var result = response;

                            if (result.hasOwnProperty('error')) {

                                if (!result.error) {

                                    items[itemIndex].myLike = result.myLike;
                                    items[itemIndex].match = result.match;

                                    $hotgame_like_btn.removeAttr('disabled');
                                    $hotgame_skip_btn.removeAttr('disabled');

                                    showHotgameScreen(itemIndex);

                                } else {

                                    $hotgame_like_btn.removeAttr('disabled');
                                    $hotgame_skip_btn.removeAttr('disabled');
                                }

                            } else {

                                $hotgame_like_btn.removeAttr('disabled');
                                $hotgame_skip_btn.removeAttr('disabled');
                            }

                        },
                        error: function(xhr, type){

                            $hotgame_like_btn.removeAttr('disabled');
                            $hotgame_skip_btn.removeAttr('disabled');
                        }
                    });
                });

            });

            function getItems() {

                showLoadingScreen();

                itemIndex = 0;

                items.length = 0;

                var liked = 1;
                var matches = 1;

                var gender = $("input[name='gender']:checked").val();
                var distance = $("input[name='distance']").val();

                $.cookie("hotgame_gender", gender, { expires : 7, path: '/' });
                $.cookie("hotgame_distance", distance, { expires : 7, path: '/' });

                if ($('input[name=liked]').is(':checked')) {

                    $.cookie("hotgame_liked", 1, { expires : 7, path: '/' });

                } else {

                    liked = 0;
                    $.cookie("hotgame_liked", null, { path: '/' });
                }

                if ($('input[name=matches]').is(':checked')) {

                    $.cookie("hotgame_matches", 1, { expires : 7, path: '/' });

                } else {

                    matches = 0;
                    $.cookie("hotgame_matches", null, { path: '/' });
                }

                $.ajax({
                    type: 'POST',
                    url: '/api/' + options.api_version + '/method/hotgame.get',
                    data: 'accessToken=' + account.accessToken + "&accountId=" + account.id + "&itemId=" + itemId + "&distance=" + distance + "&sex=" + gender + "&liked=" + liked + "&matches=" + matches + "&lat=" + lat + "&lng=" + lng,
                    dataType: 'json',
                    timeout: 30000,
                    success: function(response) {

                        var result = response;

                        if (result.hasOwnProperty('error')) {

                            if (!result.error) {

                                if (result.hasOwnProperty('items')) {

                                    if (result.items.length > 0) {

                                        itemId = result.itemId;

                                        for (i = 0; i < result.items.length; i++) {

                                            items.push({
                                                id: result.items[i].id,
                                                fullname: result.items[i].fullname,
                                                username: result.items[i].username,
                                                age: result.items[i].age,
                                                match: result.items[i].match,
                                                myLike: result.items[i].myLike,
                                                online: result.items[i].online,
                                                lowPhotoUrl: result.items[i].lowPhotoUrl,
                                                normalPhotoUrl: result.items[i].normalPhotoUrl
                                            });
                                        }
                                    }
                                }
                            }
                        }

                        if (items.length != 0) {

                            showHotgameScreen(0);

                        } else {

                            showErrorScreen();
                        }

                    },
                    error: function(xhr, type){

                        showErrorScreen();
                    }
                });
            }

            function showHotgameScreen(i) {

                itemIndex = i;

                $hotgame_filters_btn.removeClass('hidden');

                $hotgame_container.removeClass('hidden');

                $loading_container.addClass('hidden');
                $location_container.addClass('hidden');
                $error_container.addClass('hidden');

                $hotgame_profile_fullname.text(items[i].fullname + ", " + items[i].age);
                $hotgame_profile_fullname.attr("href", "/" + items[i].username);

                $hotgame_profile_btn.attr("href", "/" + items[i].username);

                $hotgame_profile_photo.attr("src", items[i].lowPhotoUrl);

                $hotgame_status_image.addClass('hidden');

                $hotgame_like_btn.addClass('hidden');

                $hotgame_skip_btn.addClass('hidden');
                $hotgame_skip_btn.find('i').removeClass('icofont-close');
                $hotgame_skip_btn.find('i').removeClass('icofont-arrow-right');

                if (items[i].myLike) {

                    $hotgame_skip_btn.find('i').removeClass('icofont-close');
                    $hotgame_skip_btn.find('i').addClass('icofont-arrow-right');

                    $hotgame_skip_btn.removeClass('hidden');

                } else {

                    $hotgame_like_btn.removeClass('hidden');

                    $hotgame_skip_btn.find('i').removeClass('icofont-arrow-right');
                    $hotgame_skip_btn.find('i').addClass('icofont-close');

                    $hotgame_skip_btn.removeClass('hidden');
                }

                if (items[i].match) {

                    $hotgame_status_image.attr("src", '/assets/img/ic_hotgame_match.png');
                    $hotgame_status_image.removeClass('hidden');

                } else {

                    if (items[i].myLike) {

                        $hotgame_status_image.attr("src", '/assets/img/ic_hotgame_liked.png');
                        $hotgame_status_image.removeClass('hidden');
                    }
                }
            }

            function showErrorScreen() {

                $loading_container.addClass('hidden');
                $location_container.addClass('hidden');
                $hotgame_container.addClass('hidden');

                $error_container.removeClass('hidden');
                $hotgame_filters_btn.removeClass('hidden');
            }

            function showLoadingScreen() {

                $loading_container.removeClass('hidden');

                $hotgame_filters_btn.addClass('hidden');

                $location_container.addClass('hidden');
                $hotgame_container.addClass('hidden');
                $error_container.addClass('hidden');
            }

            function getLocation() {

                var watchId = navigator.geolocation.watchPosition(function(position) {

                    lat = position.coords.latitude;
                    lng = position.coords.longitude;

                    console.log("Lat: " + position.coords.latitude);
                    console.log("Lng: " + position.coords.longitude);

                    if (lat != 0 && lng != 0) {

                        $location_container.addClass('hidden');

                        navigator.geolocation.clearWatch(watchId);

                        setLocation(lat, lng);
                    }

                }, function(error) {

                    if (error.code == error.PERMISSION_DENIED) {

                        $location_container.find('h5').text(strings.sz_message_location_denied);
                        $location_container.find('button').addClass("hidden");
                    }

                }, {

                    maximumAge: Infinity,
                    timeout: Infinity
                });

            }

            function setLocation(lat, lng) {

                showLoadingScreen();

                $.ajax({
                    type: 'POST',
                    url: '/api/' + options.api_version + '/method/account.setGeoLocation',
                    data: 'accountId=' + account.id + "&accessToken=" + account.accessToken + "&lat=" + lat + "&lng=" + lng,
                    dataType: 'json',
                    timeout: 30000,
                    success: function(response){

                        if (response.hasOwnProperty('error')) {

                            if (!response.error) {

                                getItems();
                            }
                        }
                    },
                    error: function(xhr, type){

                        showErrorScreen();
                    }
                });
            }

        </script>

</body>
</html>
