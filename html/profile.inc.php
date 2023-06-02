<?php

    /*!
     * https://hindbyte.com
     * hindbyte@gmail.com
     *
     * Copyright 2012-2021 Demyanchuk Dmitry (hindbyte@gmail.com)
     */

    if (!defined("APP_SIGNATURE")) {

        header("Location: /");
        exit;
    }

    if (!auth::isSession()) {

        header('Location: /');
        exit;
    }

    if (!$auth->authorize(auth::getCurrentUserId(), auth::getAccessToken())) {

        auth::unsetSession();

        header('Location: /');
        exit;
    }

    $welcome_block = false;

    if (isset($_SESSION['welcome_block'])) {

        $welcome_block = true;

        unset($_SESSION['welcome_block']);
    }

    $profileId = $helper->getUserId($request[0]);

    $profile = new profile($dbo, $profileId);

    $profile->setRequestFrom(auth::getCurrentUserId());
    $profileInfo = $profile->get();

    if ($profileInfo['error'] === true) {

        header("Location: /");
        exit;
    }

    $myPage = false;

    if ($profileInfo['id'] == auth::getCurrentUserId()) {

        $account = new account($dbo, $profileInfo['id']);
        $account->setLastActive();
        unset($account);

        $myPage = true;

    }

    // Photo

    $profilePhotoUrl = $profileInfo['bigPhotoUrl'];

    if (strlen($profilePhotoUrl) == 0) {

        $profilePhotoUrl = "/assets/img/profile_default_photo.png";
    }

    auth::newAuthenticityToken();

    $page_id = "profile";

    if ($myPage) {

        $page_id = "my-profile";
    }

    $css_files = array("my.css", "account.css");
    $page_title = $profileInfo['fullname']." | ".APP_TITLE;

    include_once("html/common/site_header.inc.php");

?>

<body class="profile-page">

    <?php

        include_once("html/common/site_topbar.inc.php");
    ?>


    <div class="wrap content-page">

        <div class="main-column row">

            <?php

                include_once("html/common/site_sidenav.inc.php");
            ?>

            <div class="col-lg-9 col-md-12" id="content">

                <?php

                if ($profileInfo['state'] != ACCOUNT_STATE_ENABLED) {

                    include_once("html/stubs/profile.php");

                } else {

                    ?>
                        <?php

                            if ($welcome_block) {

                                ?>
                                    <div class="card mb-3" id="welcome-block">
                                        <div class="card-header">
                                            <h3 class="card-title"><?php echo $LANG['label-welcome-title']; ?></h3>
                                            <h5 class="card-description"><?php echo $LANG['label-welcome-sub-title']; ?></h5>
                                        </div>
                                    </div>
                                <?php
                            }

                        ?>

                        <div class="main-content">

                            <?php

                            if ($myPage) {

                                ?>

                                <div class="upload-progress">
                                    <div class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0"
                                         aria-valuemax="100"></div>
                                </div>

                                <?php
                            }
                            ?>


                            <div class="profile_cover" style="background-image: url(<?php echo $profilePhotoUrl; ?>); background-position: 0;">

                                <?php

                                if ($myPage) {

                                    ?>

                                    <div class="profile_add_cover profile-upload-actions">
                                        <span class="upload-button"><input type="file" id="photo-upload" name="uploaded_file"><?php echo $LANG['action-change-photo']; ?></span>
                                    </div>

                                    <?php
                                }
                                ?>
                            </div>

                            <div id="addon_block">

                                <?php

                                if (auth::isSession() && $myPage) {

                                    ?>

                                    <a href="/account/settings" class="flat_btn noselect"><?php echo $LANG['action-edit-profile']; ?></a>

                                    <?php
                                }

                                if (!$myPage) {

                                    ?>

                                    <?php

                                    if (!$profileInfo['iLiked']) {

                                        ?>

                                        <a onclick="Profile.like('<?php echo $profileInfo['id']; ?>'); return false;" class="flat_btn noselect like-btn">Like</a>

                                        <?php

                                    }
                                    ?>

                                    <?php

                                    if ($profileInfo['allowMessages'] == 0) {

                                        ?>

                                            <a data-toggle="modal" data-target="#profile-messages-not-allowed" href="javascript: void(0)" style="" class="flat_btn noselect"><?php echo $LANG['action-send-message']; ?></a>

                                        <?php

                                    } else {

                                        ?>
                                            <a href="/account/chat?chat_id=0&user_id=<?php echo $profileInfo['id']; ?>" style="" class="flat_btn noselect"><?php echo $LANG['action-send-message']; ?></a>
                                        <?php
                                    }

                                    ?>

                                    <a onclick="Report.showDialog('<?php echo $profileInfo['id']; ?>', '<?php echo REPORT_TYPE_PROFILE; ?>'); return false;" class="flat_btn noselect"><?php echo $LANG['action-report']; ?></a>

                                    <?php

                                    if ($profileInfo['blocked']) {

                                        ?>
                                        <a data-action="unblock" data-toggle="modal" data-target="#profile-unblock-dlg" onclick="Profile.getBlockBox('<?php echo $profileInfo['id']; ?>'); return false;" class="flat_btn noselect block-btn"><?php echo $LANG['action-unblock']; ?></a>
                                        <?php

                                    } else {

                                        ?>
                                        <a data-action="block" data-toggle="modal" data-target="#profile-block-dlg" onclick="Profile.getBlockBox('<?php echo $profileInfo['id']; ?>'); return false;" class="flat_btn noselect block-btn"><?php echo $LANG['action-block']; ?></a>
                                        <?php
                                    }
                                    ?>

                                    <?php
                                }
                                ?>
                            </div>

                            <div class="profile-content standard-page">

                                <div class="user-info">

                                    <div class="">

                                        <div class="profile-user-photo-container">

                                            <span class="profile-photo-loader ">
                                                <div class="loader">
                                                    <i class="fa fa-circle-notch"></i>
                                                </div>
                                            </span>

                                            <a href="<?php echo $profilenormalImageUrl; ?>" class="profile_img_wrap profile-user-photo-link">
                                                <span alt="Photo" class="profile-user-photo user_image profile-user-photo-bg" style="background-image: url('<?php echo $profilePhotoUrl; ?>') " onclick="blueimp.Gallery($('.profile-user-photo-link')); return false"></span>
                                            </a>

                                        </div>

                                        <div class="basic-info">
                                            <h1>
                                                <?php echo $profileInfo['fullname']; ?>
                                            </h1>

                                            <h4 style="margin: 0">@<?php echo $profileInfo['username']; ?></h4>

                                            <?php

                                            if ($profileInfo['online']) {

                                                ?>
                                                <span class="info-item info-item-online">Online</span>
                                                <?php

                                            } else {

                                                if ($profileInfo['lastAuthorize'] == 0) {

                                                    ?>
                                                    <span class="info-item info-item-online">Offline</span>
                                                    <?php

                                                } else {

                                                    ?>
                                                    <span class="info-item info-item-online"><?php echo $profileInfo['lastAuthorizeTimeAgo']; ?></span>
                                                    <?php
                                                }
                                            }
                                            ?>

                                        </div>

                                    </div>
                                </div>



                                <!--   <div class="profile-content standard-page"> END-->
                            </div>

                        </div>

                        <?php


                        if ($profileInfo['imagesCount'] != 0) {

                            if ($profileInfo['id'] == auth::getCurrentUserId() || $profileInfo['allowShowMyGallery'] == 0) {

                                ?>
                                    <div class="main-content">
                                        <div class="card border-0 mt-4 col-12 p-0" id="preview-gallery-block">
                                            <div class="card-header border-0">
                                                <h3 class="card-title"><i class="icofont icofont-image mr-2"></i><span class="counter-button-title"><?php echo $LANG['page-gallery']; ?> <span id="stat_images_count" class="counter-button-indicator"><?php echo $profileInfo['imagesCount']; ?></span></span></h3>
                                                <span class="action-link"><a href="/<?php echo $profileInfo['username']; ?>/gallery"><?php echo $LANG['action-show-all']; ?></a></span>
                                            </div>

                                            <div class="card-body p-2">
                                                <div class="grid-list">

                                                    <?php

                                                    $gallery = new gallery($dbo);
                                                    $gallery->setRequestFrom($profileInfo['id']);
                                                    $result = $gallery->get(0, $profileInfo['id'], true, 1, 6);

                                                    foreach ($result['items'] as $key => $value) {

                                                        draw::previewGalleryItem($value, $LANG, $helper);
                                                    }
                                                    ?>

                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                <?php
                            }
                        }
                        

                        if ($profileInfo['likesCount'] != 0) {

                            if ($profileInfo['id'] == auth::getCurrentUserId() || $profileInfo['allowShowMyLikes'] == 0) {

                                ?>
                                <div class="main-content">
                                    <div class="card border-0 mt-4 col-12 p-0" id="preview-likes-block">
                                        <div class="card-header border-0">
                                            <h3 class="card-title"><i class="icofont icofont-heart mr-2"></i><span class="counter-button-title"><?php echo $LANG['page-likes']; ?> <span id="stat_likes_count" class="counter-button-indicator"><?php echo $profileInfo['likesCount']; ?></span></span></h3>
                                            <span class="action-link"><a href="/<?php echo $profileInfo['username']; ?>/likes"><?php echo $LANG['action-show-all']; ?></a></span>
                                        </div>

                                        <div class="card-body p-2">
                                            <div class="grid-list">

                                                <?php

                                                $result = $profile->getFans(0, 6);

                                                foreach ($result['items'] as $key => $value) {

                                                    draw::previewPeopleItem($value, $LANG, $helper);
                                                }
                                                ?>

                                            </div>
                                        </div>

                                    </div>
                                </div>
                                <?php
                            }
                        }

                        ?>

                        <div class="main-content profile-info-content">

                            <div class="standard-page">

                                <?php

                                include_once("html/stubs/profile_info_content.inc.php");
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

    if ($myPage) {

        ?>

        <?php
    }

    if (!$myPage && auth::getCurrentUserId() != 0) {

        ?>


        <div class="modal modal-form fade profile-block-dlg" id="profile-block-dlg" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">

                    <form id="profile-block-form" action="/api/v2/method/blacklist.add" method="post">

                        <input type="hidden" name="accessToken" value="<?php echo auth::getAccessToken(); ?>">
                        <input type="hidden" name="accountId" value="<?php echo auth::getCurrentUserId(); ?>">

                        <input type="hidden" name="profileId" value="<?php echo $profileInfo['id']; ?>">
                        <input type="hidden" name="reason" value="">

                        <div class="modal-header">
                            <h5 class="modal-title placeholder-title"><?php echo $LANG['dlg-confirm-block-title']; ?></h5>
                            <button class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>

                        <div class="modal-body">

                            <div class="error-summary alert alert-warning"><?php echo sprintf($LANG['msg-block-user-text'], "<strong>".$profileInfo['fullname']."</strong>", "<strong>".$profileInfo['fullname']."</strong>"); ?></div>

                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php echo $LANG['action-no']; ?></button>
                            <button type="button"  onclick="Profile.block('<?php echo $profileInfo['id']; ?>'); return false;" data-dismiss="modal" class="btn btn-primary"><?php echo $LANG['action-yes']; ?></button>
                        </div>
                    </form>

                </div>
            </div>
        </div>

        <div class="modal modal-form fade profile-messages-not-allowed" id="profile-messages-not-allowed" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">

                    <div class="modal-header">
                        <h5 class="modal-title placeholder-title"><?php echo $profileInfo['fullname']; ?></h5>
                        <button class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <div class="modal-body">

                        <div class="error-summary alert alert-warning"><?php echo sprintf($LANG['label-messages-not-allowed'], "<strong>".$profileInfo['fullname']."</strong>"); ?></div>

                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn blue" data-dismiss="modal"><?php echo $LANG['action-close']; ?></button>
                    </div>

                </div>
            </div>
        </div>

        <?php
    }
    ?>

    <?php

        include_once("html/common/site_footer.inc.php");
    ?>

        <script type="text/javascript">

            var $infobox = $('#info-box');



            window.Report || ( window.Report = {} );

            Report.showDialog = function (itemId, itemType) {

                var html = '<div id="reportModal" class="modal fade">';
                html +=' <div class="modal-dialog modal-dialog-centered" role="document">';
                html += '<div class="modal-content">';
                html += '<div class="modal-header">';
                html += '<h5 class="modal-title" id="reportModal">' + strings.sz_action_report + '</h5>'
                html += '<button class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>';
                html += '</div>'; // modal-header
                html += '<div class="modal-body">';

                html += '<a onclick="Report.send(\'' + itemId + '\', \'' + itemType + '\', \'0\'); return false;" class="box-menu-item" href="javascript:void(0)">' + strings.sz_report_reason_1 + '</a>';
                html += '<a onclick="Report.send(\'' + itemId + '\', \'' + itemType + '\', \'1\'); return false;" class="box-menu-item" href="javascript:void(0)">' + strings.sz_report_reason_2 + '</a>';
                html += '<a onclick="Report.send(\'' + itemId + '\', \'' + itemType + '\', \'2\'); return false;" class="box-menu-item" href="javascript:void(0)">' + strings.sz_report_reason_3 + '</a>';
                html += '<a onclick="Report.send(\'' + itemId + '\', \'' + itemType + '\', \'3\'); return false;" class="box-menu-item" href="javascript:void(0)">' + strings.sz_report_reason_4 + '</a>';

                html += '</div>'; // modal-body
                html += '<div class="modal-footer">';
                html += '<button type="button" class="btn blue" data-dismiss="modal">' + strings.sz_action_close + '</button>';
                html += '</div>';  // footer
                html += '</div>';  // modal-content
                html += '</div>';  // modal-dialog
                html += '</div>';  // reportModal
                $("#modal-section").html(html);
                $("#reportModal").modal();
            };

            Report.send = function (itemId, itemType, abuseId) {

                // itemType = for next code updates

                $('#reportModal').modal('toggle');

                $.ajax({
                    type: 'POST',
                    url: '/api/' + options.api_version + '/method/profile.report',
                    data: 'accessToken=' + account.accessToken + "&accountId=" + account.id + "&profileId=" + itemId + "&reason=" + abuseId,
                    dataType: 'json',
                    timeout: 30000,
                    success: function(response) {

                        //
                    },
                    error: function(xhr, type) {

                        //
                    }
                });
            };

            window.Profile || ( window.Profile = {} );

            Profile.getReportBox = function(user_id, title) {

                var url = "/ajax/profile/method/report.php/?action=get-box&user_id=" + user_id;
                $.colorbox({width:"450px", href: url, title: title, fixed:true});
            };

            Profile.sendReport = function (profile_id, reason, access_token) {

                $.ajax({
                    type: 'POST',
                    url: '/ajax/profile/method/report.php',
                    data: 'profile_id=' + profile_id + "&reason=" + reason + "&access_token=" + access_token,
                    dataType: 'json',
                    timeout: 30000,
                    success: function(response) {

                        $.colorbox.close();

                    },
                    error: function(xhr, type){

                    }
                });
            };

            Profile.getBlockBox = function(profile_id) {

                var attr = $("a.block-btn").attr("data-action");

                if (typeof attr !== typeof undefined) {

                    if (attr === "block") {

                        $('#profile-block-dlg').modal('show');

                    } else {

                        $("a.block-btn").text(strings.sz_action_block);
                        $("a.block-btn").attr("data-action", "block");

                        Profile.unBlock(profile_id);
                    }
                }
            };

            Profile.block = function(profile_id) {

                $("a.block-btn").text(strings.sz_action_unblock);
                $("a.block-btn").attr("data-action", "unblock");

                $.ajax({
                    type: 'POST',
                    url: "/api/" + options.api_version + "/method/blacklist.add",
                    data: "accountId=" + account.id + "&accessToken=" + account.accessToken + "&profileId=" + profile_id,
                    dataType: 'json',
                    timeout: 30000,
                    success: function (response) {


                    },
                    error: function (xhr, type) {

                        $("a.block-btn").text(strings.sz_action_block);
                        $("a.block-btn").attr("data-action", "block");
                    }
                });
            };


            Profile.unBlock = function(profile_id) {

                $.ajax({
                    type: 'POST',
                    url: "/api/" + options.api_version + "/method/blacklist.remove",
                    data: "accountId=" + account.id + "&accessToken=" + account.accessToken + "&profileId=" + profile_id,
                    dataType: 'json',
                    timeout: 30000,
                    success: function (response) {


                    },
                    error: function (xhr, type) {

                    }
                });
            };

            Profile.like = function (profile_id) {

                $("a.like-btn").hide();

                $.ajax({
                    type: 'POST',
                    url: '/api/' + options.api_version + '/method/profile.like',
                    data: 'profileId=' + profile_id + "&accessToken=" + account.accessToken + "&accountId=" + account.id,
                    dataType: 'json',
                    timeout: 30000,
                    success: function(response){

                        $("a.like-btn").remove();
                    },
                    error: function(xhr, type){

                        $("a.like-btn").show();
                    }
                });
            };

            $("#photo-upload").fileupload({
                formData: {accountId: <?php echo auth::getCurrentUserId(); ?>, accessToken: "<?php echo auth::getAccessToken(); ?>", imgType: 0},
                name: 'image',
                url: "/api/" + options.api_version + "/method/profile.uploadImg",
                dropZone:  '',
                dataType: 'json',
                singleFileUploads: true,
                multiple: false,
                maxNumberOfFiles: 1,
                maxFileSize: constants.MAX_FILE_SIZE,
                acceptFileTypes: "", // or regex: /(jpeg)|(jpg)|(png)$/i
                "files":null,
                minFileSize: null,
                messages: {
                    "maxNumberOfFiles":"Maximum number of files exceeded",
                    "acceptFileTypes":"File type not allowed",
                    "maxFileSize": "File is too big",
                    "minFileSize": "File is too small"},
                process: true,
                start: function (e, data) {

                    console.log("start");

                    $('div.upload-progress').css("display", "block");
                    $('div.profile-upload-actions').addClass('hidden');

                    $("#photo-upload").trigger('start');
                },
                processfail: function(e, data) {

                    console.log("processfail");

                    if (data.files.error) {

                        $infobox.find('#info-box-message').text(data.files[0].error);
                        $infobox.modal('show');
                    }
                },
                progressall: function (e, data) {

                    console.log("progressall");

                    var progress = parseInt(data.loaded / data.total * 100, 10);

                    $('div.upload-progress').find('.progress-bar').attr('aria-valuenow', progress).css('width', progress + '%').text(progress + '%');
                },
                done: function (e, data) {

                    console.log("done");

                    var result = jQuery.parseJSON(data.jqXHR.responseText);

                    if (result.hasOwnProperty('error')) {

                        if (result.error === false) {

                            if (result.hasOwnProperty('bigPhotoUrl')) {

                                $("span.profile-user-photo").css("background-image", "url(" + result.bigPhotoUrl + ")");
                                $("span.avatar").css("background-image", "url(" + result.bigPhotoUrl + ")");
                                $("a.profile-user-photo-link").attr("href", result.bigPhotoUrl);
                                $("img.profile-photo-avatar").attr("src", result.bigPhotoUrl);

                                $('#welcome-block').remove();
                            }

                        } else {

                            $infobox.find('#info-box-message').text(result.error_description);
                            $infobox.modal('show');
                        }
                    }

                    $("#photo-upload").trigger('done');
                },
                fail: function (e, data) {

                    console.log(data.errorThrown);
                },
                always: function (e, data) {

                    console.log("always");

                    $('div.upload-progress').css("display", "none");
                    $('div.profile-upload-actions').removeClass('hidden');

                    $("#photo-upload").trigger('always');
                }
            });

        </script>

</body>
</html>