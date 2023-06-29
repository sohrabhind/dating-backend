<?php





class draw extends db_connect
{
	public function __construct($dbo = NULL)
    {
		parent::__construct($dbo);
	}


    static function guestItem($profile, $LANG, $helper = null)
    {
        $profilePhotoUrl = "/assets/img/profile_default_photo.png";

        if (strlen($profile['guestUserPhoto']) != 0) {

            $profilePhotoUrl = $profile['guestUserPhoto'];
        }

        ?>

        <div class="cardview-item">
            <div class="card-body">

                <a class="user-photo" href="/<?php echo $profile['guestUserUsername']; ?>">
                    <div class="cardview-img cardview-img-container">
                        <span class="card-loader-container">
                            <div class="loader"><i class="ic icon-spin icon-spin"></i></div>
                        </span>
                        <span class="cardview-img" style="background-image: url('<?php echo $profilePhotoUrl; ?>')"></span>
                    </div>
                </a>


                <span class="card-counter black noselect cardview-item-badge" original-title="<?php echo $LANG['label-last-visit']; ?>"><?php echo $profile['timeAgo']; ?></span>

                <?php

                    if ($profile['guestUserOnline']) {

                        ?>
                            <i class="online-label"></i>
                        <?php
                    }
                ?>

                <div class="cardview-item-footer" style="position: relative;">
                    <h4 class="cardview-item-title-header">
                        <a class="cardview-item-title" href="/<?php echo $profile['guestUserUsername']; ?>">
                            <?php echo $profile['guestUserFullname']; ?>
                        </a>
                    </h4>
                    <?php
                    if (strlen($profile['guestUserLocation']) > 0) {

                        ?>
                        <div class="gray-text"><?php echo $profile['guestUserLocation']; ?></div>
                        <?php
                    }
                    ?>

                </div>

            </div>
        </div>

        <?php
    }

    static function peopleItem($profile, $LANG, $helper = null)
    {
        $profilePhotoUrl = "/assets/img/profile_default_photo.png";

        if (strlen($profile['bigPhotoUrl']) != 0) {

            $profilePhotoUrl = $profile['bigPhotoUrl'];
        }

        ?>

        <li class="card-item classic-item">
            <a href="/<?php echo $profile['username']; ?>" class="card-body">
                    <span class="card-header">
                        <img class="card-icon" src="<?php echo $profilePhotoUrl; ?>"/>
                        <?php if ($profile['online']) echo "<span title=\"Online\" class=\"card-online-icon\"></span>"; ?>
                        <div class="card-content">
                            <span class="card-title"><?php echo $profile['fullname']; ?>
                            </span>
                            <span class="card-username">@<?php echo $profile['username']; ?></span>

                            <?php

                            if (strlen($profile['location']) > 0) {

                                ?>
                                <span class="card-location"><?php echo $profile['location']; ?></span>
                                <?php
                            }

                            if ($profile['online']) {

                                ?>
                                <span class="card-counter green">Online</span>
                                <?php

                            } else {

                                ?>
                                <span title="<?php echo $LANG['label-last-seen']; ?>" class="card-counter black"><?php echo $profile['lastAuthorizeTimeAgo']; ?></span>
                                <?php
                            }
                            ?>
                        </div>
                    </span>
            </a>
        </li>

        <?php
    }

    static function blackListItem($profile, $LANG, $helper = null)
    {
        ?>

        <li class="card-item classic-item" data-id="<?php echo $profile['id']; ?>">
            <a href="/<?php echo $profile['blockedUserUsername']; ?>" class="card-body">
                <span class="card-header">
                    <img class="card-icon" src="<?php echo $profile['blockedUserPhotoUrl']; ?>"/>
                    <div class="card-content">
                        <span class="card-title"><?php echo $profile['blockedUserFullname']; ?>
                        </span>
                        <span class="card-username">@<?php echo $profile['blockedUserUsername']; ?></span>

                        <?php

                        if ($profile['blockedUserOnline']) {

                            ?>
                            <span class="card-date">Online</span>
                            <?php
                        }
                        ?>

                        <span class="card-action">
                            <span class="card-act negative" onclick="BlackList.remove('<?php echo $profile['id']; ?>', '<?php echo $profile['blockedUserId']; ?>'); return false;"><?php echo $LANG['action-unblock']; ?></span>
                        </span>

                        <span class="card-counter blue"><?php echo $profile['timeAgo']; ?></span>
                    </div>
                </span>
            </a>
        </li>

        <?php
    }

    static function messageItem($message, $userInfo, $LANG, $helper = null)
    {
        $profileInfo = array("username" => "", "fullname" => "", "photoUrl" => "", "online" => false);

        if ($message['fromUserId'] == auth::getCurrentUserId()) {

            $profileInfo['username'] = auth::getCurrentUserLogin();
            $profileInfo['fullname'] = auth::getCurrentUserFullname();
            $profileInfo['photoUrl'] = auth::getCurrentUserPhotoUrl();
            $profileInfo['online'] = true;

        } else {

            $profileInfo['username'] = $userInfo['username'];
            $profileInfo['fullname'] = $userInfo['fullname'];
            $profileInfo['photoUrl'] = $userInfo['bigPhotoUrl'];
            $profileInfo['online'] = $userInfo['online'];
        }

        if (strlen($profileInfo['photoUrl']) == 0) {

            $profileInfo['photoUrl'] = "/assets/img/profile_default_photo.png";
        }

        $time = new language(NULL, $LANG['lang-code']);

        $seen = false;

        if ($message['fromUserId'] == auth::getCurrentUserId() && $message['seenAt'] != 0 ) {

            $seen = true;
        }

        ?>

        <li class="card-item default-item message-item <?php if ($message['fromUserId'] == auth::getCurrentUserId()) echo "message-item-right"; ?>" data-id="<?php echo $message['id']; ?>">
            <div class="card-body">
                <span class="card-header">
                    <a href="/<?php echo $profileInfo['username']; ?>"><img class="card-icon" src="<?php echo $profileInfo['photoUrl']; ?>"/></a>
                    <?php if ($profileInfo['online'] && $message['fromUserId'] != auth::getCurrentUserId()) echo "<span title=\"Online\" class=\"card-online-icon\"></span>"; ?>
                    <div class="card-content">

                            <span class="card-status-text">

                                    <?php

                                    if (strlen($message['message']) > 0) {

                                        ?>
                                            <span class="card-status-text-message">
                                                <?php echo $message['message']; ?>
                                            </span>
                                        <?php
                                    }

                                    if (strlen($message['imgUrl']) > 0) {

                                        ?>
                                            <img class="post-img" data-href="<?php echo $message['imgUrl']; ?>" onclick="blueimp.Gallery($(this)); return false" alt="post-img" src="<?php echo $message['imgUrl']; ?>">
                                        <?php
                                    }

                                    ?>

                                    </span>
                        

                        <span class="card-date">
                            <?php echo $time->timeAgo($message['createAt']); ?>
                            <span class="time green" style="<?php if (!$seen) echo 'display: none'; ?>" data-my-id="<?php echo $LANG['label-seen']; ?>"><?php echo $LANG['label-seen']; ?></span>
                        </span>

                    </div>
                </span>
            </div>
        </li>

        <?php
    }

    static function peopleCardviewItem($profile, $LANG, $counter = false, $counter_text = "", $counter_hint = "", $counter_color = "")
    {
        $profilePhotoUrl = "/assets/img/profile_default_photo.png";

        if (strlen($profile['bigPhotoUrl']) != 0) {

            $profilePhotoUrl = $profile['bigPhotoUrl'];
        }

        ?>

        <div class="cardview-item">
            <div class="card-body">

                <a class="user-photo" href="/<?php echo $profile['username']; ?>">
                    <div class="cardview-img cardview-img-container">
                        <span class="card-loader-container">
                            <div class="loader"><i class="ic icon-spin icon-spin"></i></div>
                        </span>
                        <span class="cardview-img" style="background-image: url('<?php echo $profilePhotoUrl; ?>')"></span>
                    </div>
                </a>

                <?php

                    if ($counter) {

                        ?>
                            <span class="card-counter <?php echo $counter_color; ?> noselect cardview-item-badge" original-title="<?php echo $counter_hint; ?>"><?php echo $counter_text; ?></span>
                        <?php
                    }
                ?>

                <?php if ($profile['online']) echo "<i class=\"online-label\"></i>"; ?>

                <div class="cardview-item-footer" style="position: relative;">
                    <h4 class="cardview-item-title-header">
                        <a class="cardview-item-title" href="/<?php echo $profile['username']; ?>">
                            <?php echo $profile['fullname']; ?>
                        </a>
                    </h4>
                    <?php
                        if (strlen($profile['location']) > 0) {

                            ?>
                                <div class="gray-text"><?php echo $profile['location']; ?></div>
                            <?php
                        }
                    ?>

                </div>

            </div>
        </div>

        <?php
    }

    static function image($post, $LANG, $helper = null)
    {
        $fromUserPhoto = "/assets/img/profile_default_photo.png";

        if (strlen($post['owner']['bigPhotoUrl']) != 0) {

            $fromUserPhoto = $post['owner']['bigPhotoUrl'];
        }

        $time = new language(NULL, $LANG['lang-code']);

        ?>

        <div class="card custom-list-item post-item" data-id="<?php echo $post['id']; ?>">

            <li class="item-content">

            <div class="mb-2 item-header">

                <a href="/<?php echo $post['owner']['username']; ?>" class="item-logo" style="background-image:url(<?php echo $fromUserPhoto; ?>)"></a>

                <div class="dropdown">
                    <a class="mb-sm-0 item-menu" data-toggle="dropdown">
                        <i class="iconfont icofont-curved-down"></i>
                    </a>

                    <div class="dropdown-menu">

                        <?php

                        if ((auth::isSession() && $post['owner']['id'] == auth::getCurrentUserId())) {

                            ?>
                            <a class="dropdown-item" href="javascript:void(0)" onclick="Gallery.remove('<?php echo $post['id']; ?>'); return false;"><?php echo $LANG['action-remove']; ?></a>
                            <?php

                        } else {

                            ?>
                            <a class="dropdown-item" href="javascript:void(0)" onclick="Gallery.showReportDialog('<?php echo $post['id']; ?>', '<?php echo REPORT_TYPE_GALLERY_ITEM; ?>'); return false;"><?php echo $LANG['action-report']; ?></a>
                            <?php
                        }

                        ?>

                    </div>
                </div>

                <?php

                if ($post['owner']['online']) echo "<span title=\"Online\" class=\"item-logo-online\"></span>";

                ?>

                <a href="/<?php echo $post['owner']['username']; ?>" class="custom-item-link post-item-fullname"><?php echo $post['owner']['fullname']; ?></a>
                

                <span class="post-item-time"><a href="/<?php echo $post['owner']['username']; ?>/gallery/<?php echo $post['id']; ?>"><?php echo $time->timeAgo($post['createAt']); ?></a></span>

            </div>

            <div class="item-meta post-item-content">

                <?php

                if ($post['itemType'] == ITEM_TYPE_IMAGE && strlen($post['imgUrl'])) {

                    ?>
                    <img class="post-img" data-href="<?php echo $post['imgUrl']; ?>" onclick="blueimp.Gallery($(this)); return false" style="" alt="post-img" src="<?php echo $post['imgUrl']; ?>">
                    <?php

                }
                ?>

            </div

            </li>

        </div>

        <?php
    }

    static function galleryItem($photo, $LANG, $helper, $preview = false, $advanced = false)
    {

        ?>

        <div class="gallery-item <?php if ($advanced) echo 'gallery-advanced-item'; ?>" data-id="<?php echo $photo['id']; ?>">

            <div class="item-inner">

                <?php

                    if (!$preview) {

                        if (auth::getCurrentUserId() != 0 && auth::getCurrentUserId() == $photo['owner']['id']) {

                            ?>

                            <span title="<?php echo $LANG['action-remove']; ?>" class="action" onclick="Gallery.remove('<?php echo $photo['id']; ?>'); return false;"><i class="icon icofont icofont-close-line"></i></span>

                            <?php

                        } else {

                            ?>
                            <span title="<?php echo $LANG['action-report']; ?>" class="action" onclick="Gallery.showReportDialog('<?php echo $photo['id']; ?>', '<?php echo REPORT_TYPE_GALLERY_ITEM; ?>'); return false;"><i class="icon icofont icofont-flag"></i></span>
                            <?php
                        }
                    }

                ?>

                <!--     onclick="blueimp.Gallery($(this)); return false"           -->

                <!--        <?php //echo $photo['originImgUrl']; ?>        -->

                <a class="" href="/<?php echo $photo['owner']['username']; ?>/gallery/<?php echo $photo['id']; ?>" >

                    <span class="card-loader-container">
                        <div class="loader"><i class="ic icon-spin icon-spin"></i></div>
                    </span>

                    <div class="gallery-item-preview" style="background-image:url(<?php echo $previewImg; ?>)">

                        <?php

                            if (!$preview) {


                                    ?>
                                        <span class="info-badge black"><i class="icon icofont icofont-clock-time"></i> <?php echo $photo['timeAgo']; ?></span>

                                <?php
                            }

                        ?>

                    </div>
                </a>

                <?php

                    if ($advanced) {

                        $profilePhotoUrl = "/assets/img/profile_default_photo.png";

                        if (strlen($photo['owner']['bigPhotoUrl']) != 0) {

                            $profilePhotoUrl = $photo['owner']['bigPhotoUrl'];
                        }

                        ?>
                        <div class="p-0">

                            <div class="card-item classic-item default-item p-2">
                                <div class="card-body p-0">
                                    <span class="card-header">
                                        <a href="/<?php echo $photo['owner']['username']; ?>"><img class="card-icon" src="<?php echo $profilePhotoUrl; ?>"></a>

                                        <?php

                                            if ($photo['owner']['online']) {

                                                ?>
                                                    <span title="Online" class="card-online-icon"></span>
                                                <?php
                                            }
                                        ?>

                                        <div class="card-content">
                                            <span class="card-title">
                                                <a href="/<?php echo $photo['owner']['username']; ?>"><?php echo $photo['owner']['fullname']; ?></a>

                                            </span>
                                            <span class="card-username">@<?php echo $photo['owner']['username']; ?></span>
                                        </div>
                                    </span>
                                </div>
                            </div>

                        </div>
                        <?php
                    }
                ?>

            </div>
        </div>

        <?php
    }

    static function previewGalleryItem($photo, $LANG, $helper)
    {

        ?>

        <div class="gallery-item col-3 col-lg-2 col-md-2 col-sm-3" data-id="<?php echo $photo['id']; ?>">

            <div class="item-inner">

                <a class="" href="/<?php echo $photo['owner']['username']; ?>/gallery/<?php echo $photo['id']; ?>">

                    <span class="card-loader-container">
                        <div class="loader"><i class="ic icon-spin icon-spin"></i></div>
                    </span>

                    <div class="gallery-item-preview" style="background-image:url(<?php echo $previewImg; ?>)">

                    </div>

                </a>

            </div>
        </div>

        <?php
    }

    static function previewPeopleItem($item, $LANG, $helper)
    {
        ?>

        <div class="gallery-item col-3 col-lg-2 col-md-2 col-sm-3" data-id="<?php echo $item['id']; ?>">

            <div class="item-inner">

                <?php

                $previewImg = "/assets/img/profile_default_photo.png";

                if (strlen($item['bigPhotoUrl']) != 0) {

                    $previewImg = $item['bigPhotoUrl'];
                }

                ?>


                <a class="" href="/<?php echo $item['username']; ?>" title="<?php echo $item['fullname']; ?>">

                    <span class="card-loader-container">
                        <div class="loader"><i class="ic icon-spin icon-spin"></i></div>
                    </span>

                    <div class="gallery-item-preview" style="background-image:url(<?php echo $previewImg; ?>)">

                    </div>

                </a>

            </div>
        </div>

        <?php
    }


}
