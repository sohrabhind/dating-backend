<div class="col-3 d-lg-block d-none pr-0 sidebar-menu">

    <div class="sidebar-container">
        <div class="main-menu">

            <div class="item-list transparent mb-2">

                <ul>

                    <li class="item-li item-li-main <?php if (isset($page_id) && $page_id === 'my-profile') echo 'item-selected'; ?>">
                        <div class="d-flex">
                            <div class="" style="flex: 1; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                <a href="/<?php echo auth::getCurrentUserLogin(); ?>" class="custom-item-link" target="">
                                    <span class="item-icon">
                                        <img class="img profile-photo-avatar" src="<?php echo auth::getCurrentUserPhotoUrl(); ?>" alt="" draggable="false" style="border-radius: 50%;">
                                    </span>
                                    <div class="link-container">
                                        <div class="item-title"><?php echo auth::getCurrentUserFullname(); ?></div>
                                        <div class="item-sub-title">@<?php echo auth::getCurrentUserLogin(); ?></div>
                                    </div>
                                </a>
                            </div>

                            <div class="">
                                <a href="/account/settings" class="main-menu-action-button"><i class="icon icofont icofont-gear-alt"></i></a>
                            </div>
                        </div>
                    </li>

                </ul>

            </div>

            <div class="item-list transparent mb-2">

                <ul>

                    <li class="item-li <?php if (isset($page_id) && $page_id === 'my-gallery') echo 'item-selected'; ?>">
                        <a href="/account/gallery" class="custom-item-link" target="">
                            <div class="item-counter">
                                <span class="counter"></span>
                            </div>
                            <span class="item-icon iconfont"><i class="icon icofont icofont-camera"></i></span>
                            <div class="item-title"><?php echo $LANG['page-my-gallery']; ?></div>
                        </a>
                    </li>

                    <li class="item-li <?php if (isset($page_id) && $page_id === 'feed') echo 'item-selected'; ?>">
                        <a href="/account/feed" class="custom-item-link" target="">
                            <div class="item-counter">
                                <span class="counter"></span>
                            </div>
                            <span class="item-icon iconfont"><i class="icon icofont icofont-newspaper"></i></span>
                            <div class="item-title"><?php echo $LANG['page-media-feed']; ?></div>
                        </a>
                    </li>

                    <li class="item-li <?php if (isset($page_id) && $page_id === 'stream') echo 'item-selected'; ?>">
                        <a href="/account/stream" class="custom-item-link" target="">
                            <div class="item-counter">
                                <span class="counter"></span>
                            </div>
                            <span class="item-icon iconfont"><i class="icon icofont icofont-news"></i></span>
                            <div class="item-title"><?php echo $LANG['page-media-stream']; ?></div>
                        </a>
                    </li>
                </ul>
            </div>

            <div class="item-list transparent">

                <ul>

                    <li class="item-li <?php if (isset($page_id) && $page_id === 'hotgame') echo 'item-selected'; ?>">
                        <a href="/account/hotgame" class="custom-item-link" target="">
                            <div class="item-counter hidden hotgame-badge">
                                <span class="counter hotgame-count"></span>
                            </div>
                            <span class="item-icon iconfont"><i class="icon icofont icofont-tinder"></i></span>
                            <div class="item-title"><?php echo $LANG['page-hotgame']; ?></div>
                        </a>
                    </li>

                    <li class="item-li <?php if (isset($page_id) && $page_id === 'matches') echo 'item-selected'; ?>">
                        <a href="/account/matches" class="custom-item-link" target="">
                            <div class="item-counter hidden matches-badge">
                                <span class="counter matches-count"></span>
                            </div>
                            <span class="item-icon iconfont"><i class="icon icofont icofont-check-circled"></i></span>
                            <div class="item-title"><?php echo $LANG['page-matches']; ?></div>
                        </a>
                    </li>

                    <li class="item-li <?php if (isset($page_id) && $page_id === 'my-likes') echo 'item-selected'; ?>">
                        <a href="/<?php echo auth::getCurrentUserLogin(); ?>/likes" class="custom-item-link" target="">
                            <div class="item-counter hidden likes-badge">
                                <span class="counter likes-count"></span>
                            </div>
                            <span class="item-icon iconfont"><i class="icon icofont icofont-heart"></i></span>
                            <div class="item-title"><?php echo $LANG['page-likes']; ?></div>
                        </a>
                    </li>

                    <li class="item-li <?php if (isset($page_id) && $page_id === 'liked') echo 'item-selected'; ?>">
                        <a href="/account/liked" class="custom-item-link" target="">
                            <div class="item-counter hidden liked-badge">
                                <span class="counter liked-count"></span>
                            </div>
                            <span class="item-icon iconfont"><i class="icon icofont icofont-like"></i></span>
                            <div class="item-title"><?php echo $LANG['page-liked']; ?></div>
                        </a>
                    </li>


                    <li class="item-li <?php if (isset($page_id) && $page_id === 'guests') echo 'item-selected'; ?>">
                        <a href="/account/guests" class="custom-item-link" target="">
                            <div class="item-counter hidden guests-badge">
                                <span class="counter guests-count"></span>
                            </div>
                            <span class="item-icon iconfont"><i class="icon icofont icofont-foot-print"></i></span>
                            <div class="item-title"><?php echo $LANG['page-guests']; ?></div>
                        </a>
                    </li>

                    <li class="item-li <?php if (isset($page_id) && $page_id === 'upgrades') echo 'item-selected'; ?>">
                        <a href="/account/upgrades" class="custom-item-link" target="">
                            <div class="item-counter hidden upgrades-badge">
                                <span class="counter upgrades-count"></span>
                            </div>
                            <span class="item-icon iconfont"><i class="icon icofont icofont-star"></i></span>
                            <div class="item-title"><?php echo $LANG['page-upgrades']; ?></div>
                        </a>
                    </li>

                    <li class="item-li <?php if (isset($page_id) && $page_id === 'find') echo 'item-selected'; ?>">
                        <a href="/account/find" class="custom-item-link" target="">
                            <div class="item-counter hidden search-badge">
                                <span class="counter search-count"></span>
                            </div>
                            <span class="item-icon iconfont"><i class="icon icofont icofont-search-1"></i></span>
                            <div class="item-title"><?php echo $LANG['page-search']; ?></div>
                        </a>
                    </li>

                </ul>
            </div>

            <?php

                if (isset($page_id) && $page_id !== 'spotlight') {

                    ?>
                    <div class="item-list spotlight">

                        <div class="ml-2 spotlight-header">
                            <span class="spotlight-title"><?php echo $LANG['page-spotlight']; ?></span>
                            <span class="spotlight-link mr-2 float-right"><a href="/account/spotlight"><?php echo $LANG['action-show-all']; ?></a></span>
                        </div>

                        <div class="cardview-container cardview mt-3">

                            <?php

                            $sn_spotlight = new spotlight($dbo);
                            $sn_spotlight->setRequestFrom(auth::getCurrentUserId());

                            $sn_result = $sn_spotlight->get(0, 12);

                            $sn_items_loaded = count($sn_result['items']);

                            $sn_add_me = true;

                            if ($sn_items_loaded > 0) {

                                foreach ($sn_result['items'] as $key => $value) {

                                    if ($value['id'] == auth::getCurrentUserId()) {

                                        $sn_add_me = false;
                                    }
                                }
                            }

                            if ($sn_add_me) {
                                $bigPhotoUrl = "";
                                if (auth::getCurrentUserPhotoUrl() != '') {
                                    $bigPhotoUrl = APP_URL . "/" . PROFILE_PHOTO_PATH . auth::getCurrentUserPhotoUrl();
                                }

                                $sn_profile_info = array(
                                    "id" => auth::getCurrentUserId(),
                                    "username" => auth::getCurrentUserLogin(),
                                    "fullname" => auth::getCurrentUserFullname(),
                                    "bigPhotoUrl" => $bigPhotoUrl,
                                    "online" => true
                                );

                                array_unshift($sn_result['items'], $sn_profile_info);
                            }

                            foreach ($sn_result['items'] as $key => $value) {
                                draw::spotlightSideNavItem($value, $LANG, $sn_add_me);
                                if ($sn_add_me) {
                                    $sn_add_me = false;
                                }
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