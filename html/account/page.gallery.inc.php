<?php

    /*!
     * hindbyte.com
     *
     * https://hindbyte.com
     * hindbyte@gmail.com
     *
     * Copyright 2012-2021 Demyanchuk Dmitry (hindbyte@gmail.com)
     */

    if (!defined("APP_SIGNATURE")) {

        header("Location: /");
        exit;
    }

    if (!$auth->authorize(auth::getCurrentUserId(), auth::getAccessToken())) {

        header('Location: /');
    }

    $gallery = new gallery($dbo);
    $gallery->setRequestFrom(auth::getCurrentUserId());

    $items_all = $gallery->count();
    $items_loaded = 0;

    $settings = new settings($dbo);
    $app_settings = $settings->get();
    unset($settings);

    auth::newAuthenticityToken();

    $page_id = "my-gallery";

    $css_files = array();
    $page_title = $LANG['page-gallery']." | ".APP_TITLE;

    include_once("html/common/site_header.inc.php");

?>

<body class="page-gallery">

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

                    <div class="gallery-intro-header">
                        <h1 class="gallery-title"><?php echo $LANG['page-gallery']; ?></h1>
                        <p class="gallery-sub-title"><?php echo $LANG['page-gallery-sub-title']; ?></p>
                    </div>
                </div>

                <div class="card mb-2 new-post-form-container">

                    <div class="card-header">
                        <h3 class="card-title"><?php echo $LANG['label-post-form-title']; ?></h3>

                        <?php

                            if (!$auto_moderate) {

                                ?>
                                    <h6 class="card-subtitle mt-2"><i class="iconfont icofont-warning-alt mr-1"></i><?php echo $LANG['label-post-form-subtitle']; ?></h6>
                                <?php
                            }
                        ?>
                    </div>

                    <form onsubmit="create_item(); return false;" class="new-post-form" action="/" method="post">

                        <input autocomplete="off" type="hidden" name="accountId" value="<?php echo auth::getCurrentUserId(); ?>">
                        <input autocomplete="off" type="hidden" name="accessToken" value="<?php echo auth::getAccessToken(); ?>">
                        <input autocomplete="off" type="hidden" name="accessMode" value="0">
                        <input autocomplete="off" type="hidden" name="itemType" value="0">
                        <input autocomplete="off" type="hidden" name="imgUrl" value="">

                        <div class="editor-block">
                            <a href="/<?php echo auth::getCurrentUserLogin(); ?>" class="avatar" style="background-image:url(<?php echo auth::getCurrentUserPhotoUrl(); ?>)"></a>

                            <textarea name="comment" maxlength="1000" placeholder="<?php echo $LANG['placeholder-gallery-item-description']; ?>" style="overflow: hidden; overflow-wrap: break-word; resize: none; height: 65px;"></textarea>

                            <div class="dropdown emoji-dropdown dropup" style="">

                                <span class="smile-button btn-emoji-picker" data-toggle="dropdown" aria-expanded="false">
                                    <i class="btn-emoji-picker-icon iconfont icofont-slightly-smile"></i>
                                </span>

                                <div class="dropdown-menu dropdown-menu-right mt-2" x-placement="top-end" style="position: absolute; will-change: transform; top: 0px; left: 0px; transform: translate3d(443px, -26px, 0px);">
                                    <div class="emoji-items">
                                        <div class="emoji-item">😀</div>
                                        <div class="emoji-item">😁</div>
                                        <div class="emoji-item">😂</div>
                                        <div class="emoji-item">😃</div>
                                        <div class="emoji-item">😄</div>
                                        <div class="emoji-item">😅</div>
                                        <div class="emoji-item">😆</div>
                                        <div class="emoji-item">😉</div>
                                        <div class="emoji-item">😊</div>
                                        <div class="emoji-item">😋</div>
                                        <div class="emoji-item">😎</div>
                                        <div class="emoji-item">😍</div>
                                        <div class="emoji-item">😘</div>
                                        <div class="emoji-item">🤗</div>
                                        <div class="emoji-item">🤩</div>
                                        <div class="emoji-item">🤔</div>
                                        <div class="emoji-item">🤨</div>
                                        <div class="emoji-item">😐</div>
                                        <div class="emoji-item">🙄</div>
                                        <div class="emoji-item">😏</div>
                                        <div class="emoji-item">😣</div>
                                        <div class="emoji-item">😥</div>
                                        <div class="emoji-item">😮</div>
                                        <div class="emoji-item">🤐</div>
                                        <div class="emoji-item">😯</div>
                                        <div class="emoji-item">😪</div>
                                        <div class="emoji-item">😫</div>
                                        <div class="emoji-item">😴</div>
                                        <div class="emoji-item">😌</div>
                                        <div class="emoji-item">😜</div>
                                        <div class="emoji-item">🤤</div>
                                        <div class="emoji-item">😓</div>
                                        <div class="emoji-item">😔</div>
                                        <div class="emoji-item">🤑</div>
                                        <div class="emoji-item">😲</div>
                                        <div class="emoji-item">🙁</div>
                                        <div class="emoji-item">😖</div>
                                        <div class="emoji-item">😞</div>
                                        <div class="emoji-item">😟</div>
                                        <div class="emoji-item">😤</div>
                                        <div class="emoji-item">😢</div>
                                        <div class="emoji-item">😭</div>
                                        <div class="emoji-item">😦</div>
                                        <div class="emoji-item">😧</div>
                                        <div class="emoji-item">😨</div>
                                        <div class="emoji-item">😩</div>
                                        <div class="emoji-item">😰</div>
                                        <div class="emoji-item">😱</div>
                                        <div class="emoji-item">😳</div>
                                        <div class="emoji-item">🤪</div>
                                        <div class="emoji-item">😵</div>
                                        <div class="emoji-item">😡</div>
                                        <div class="emoji-item">😠</div>
                                        <div class="emoji-item">🤬</div>
                                        <div class="emoji-item">😷</div>
                                        <div class="emoji-item">🤒</div>
                                        <div class="emoji-item">🤕</div>
                                        <div class="emoji-item">🤢</div>
                                        <div class="emoji-item">🤮</div>
                                        <div class="emoji-item">🤧</div>
                                        <div class="emoji-item">😇</div>
                                        <div class="emoji-item">🤠</div>
                                        <div class="emoji-item">🤡</div>
                                        <div class="emoji-item">🤥</div>
                                        <div class="emoji-item">🤫</div>
                                        <div class="emoji-item">🤭</div>
                                        <div class="emoji-item">🧐</div>
                                        <div class="emoji-item">🤓</div>
                                        <div class="emoji-item">😈</div>
                                        <div class="emoji-item">👿</div>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <div class="img_container d-block hidden">

                            <div class="img-items-list-page d-inline-block w-100" style="">

                            </div>

                        </div>

                        <div class="form_actions">

                            <div class="upload-progress hidden">
                                <div class="progress-bar " role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width: 100%;">100%</div>
                            </div>

                            <div class="item-actions">

                                <div class="post-addons-block">

                                    <div class="btn btn-secondary item-upload-button image-upload-button item-add-image">
                                        <input type="file" id="item-image-upload" name="uploaded_file">
                                        <i class="iconfont icofont-ui-image mr-2"></i><?php echo $LANG['action-add-photo']; ?>
                                    </div>

                                </div>


                                <div class="post-options-block">

                                    <button style="padding: 7px 16px;" class="primary_btn blue" value="ask">Post</button>

                                </div>

                            </div>

                        </div>
                    </form>

                </div>

                <div class="standard-page cardview-container p-0 items-container">

                    <?php

                    $result = $gallery->get(0, auth::getCurrentUserId(), true);

                    $items_loaded = count($result['items']);

                    if ($items_loaded != 0) {

                        ?>
                        <div class="cardview items-view">
                            <?php

                            foreach ($result['items'] as $key => $value) {

                                draw::galleryItem($value, $LANG, $helper);
                            }

                            ?>
                        </div>
                        <?php

                        if ($items_all > 20) {

                            ?>

                            <header class="top-banner loading-banner p-0 pt-3">

                                <div class="prompt">
                                    <button onclick="Items.more('/<?php echo auth::getCurrentUserLogin(); ?>/gallery', '<?php echo $result['itemId']; ?>'); return false;" class="button more loading-button"><?php echo $LANG['action-more']; ?></button>
                                </div>

                            </header>

                            <?php
                        }

                    } else {

                        ?>

                        <div class="card information-banner">
                            <div class="card-header">
                                <div class="card-body">
                                    <h5 class="m-0"><?php echo $LANG['label-empty-list']; ?></h5>
                                </div>
                            </div>
                        </div>

                        <?php
                    }
                    ?>

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

            var $image_upload_button = $('div.image-upload-button');
            var $image_container = $('div.img_container');
            var $item_actions = $('div.item-actions');
            var $upload_progress = $('div.upload-progress ');

            var $infobox = $('div#info-box');

            $("#item-image-upload").fileupload({
                formData: {accountId: account.id, accessToken: account.accessToken},
                name: 'image',
                url: "/api/" + options.api_version + "/method/gallery.uploadImg",
                dropZone:  '',
                dataType: 'json',
                singleFileUploads: true,
                multiple: false,
                maxNumberOfFiles: 1,
                maxFileSize: constants.IMAGE_FILE_MAX_SIZE,
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

                    $item_actions.addClass("hidden");
                    $upload_progress.removeClass("hidden");

                    $("#item-image-upload").trigger('start');
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

                    $upload_progress.find('.progress-bar').attr('aria-valuenow', progress).css('width', progress + '%').text(progress + '%');
                },
                done: function (e, data) {

                    console.log("done");

                    var result = jQuery.parseJSON(data.jqXHR.responseText);

                    if (result.hasOwnProperty('error')) {

                        if (result.error === false) {

                            if (result.hasOwnProperty('normalPhotoUrl')) {

                                var html = '<div class="gallery-item new-post-media-item">';
                                html +=' <div class="item-inner">';
                                html += '<div class="gallery-item-preview" style="background-image:url(' + result.normalPhotoUrl + ')">';
                                html += '<span class="action" onclick="delete_item($(this))">×</span>';
                                html += '</div>';  // gallery-item-preview
                                html += '</div>';  // item-inner
                                html += '</div>';  // gallery-item
                                $image_container.find('.img-items-list-page').html(html);
                                $('input[name=imgUrl]').val(result.normalPhotoUrl);
                                $('input[name=itemType]').val("0");
                            }

                        } else {

                            $infobox.find('#info-box-message').text(result.error_description);
                            $infobox.modal('show');
                        }
                    }

                    $("#item-image-upload").trigger('done');
                },
                fail: function (e, data) {

                    console.log("fail");

                    console.log(data.errorThrown);
                },
                always: function (e, data) {

                    console.log("always");

                    update_ui();

                    $upload_progress.addClass("hidden");

                    $("#item-image-upload").trigger('always');
                }
            });

            function update_ui() {

                $item_actions.removeClass('hidden');

                if ($image_container.find('.gallery-item').length != 0) {

                    $image_container.removeClass('hidden');

                    $image_upload_button.addClass('hidden');

                } else {

                    $image_container.addClass('hidden');

                    $image_upload_button.removeClass('hidden');
                }
            }

            function delete_item(thisObj) {

                thisObj.parents('div.new-post-media-item').remove();
                $('input[name=imgUrl]').val("");
                $('input[name=itemType]').val("0");

                update_ui();
            }


            function create_item() {

                if ($image_container.find('.gallery-item').length == 0) {

                    return;
                }

                $.ajax({
                    type: 'POST',
                    url: "/api/" + options.api_version + "/method/gallery.new",
                    data: $("form.new-post-form").serialize(),
                    dataType: 'json',
                    timeout: 30000,
                    success: function(response) {

                        location.reload();
                    },
                    error: function(xhr, type){

                    }
                });
            }

        </script>


</body
</html>
