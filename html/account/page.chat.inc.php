<?php

    /*!
     * ifsoft.co.uk
     *
     * http://ifsoft.com.ua, httpa://ifsoft.co.uk, https://hindbyte.com
     * hindbyte@gmail.com
     *
     * Copyright 2012-2020 Demyanchuk Dmitry (hindbyte@gmail.com)
     */

    if (!$auth->authorize(auth::getCurrentUserId(), auth::getAccessToken())) {

        auth::unsetSession();

        header('Location: /');
        exit;
    }

    $showForm = true;

    $chat_from_user_id = 0;
    $chat_to_user_id = 0;

    $chat_id = 0;
    $user_id = 0;

    $my_info = array();
    $my_profile = new account($dbo, auth::getCurrentUserId());
    $my_info = $my_profile->get();

    $chat_info = array("messages" => array());
    $user_info = array();

    $messages = new msg($dbo);
    $messages->setRequestFrom(auth::getCurrentUserId());

    if (!isset($_GET['chat_id']) && !isset($_GET['user_id'])) {

        header('Location: /');
        exit;

    } else {

        $chat_id = isset($_GET['chat_id']) ? $_GET['chat_id'] : 0;
        $user_id = isset($_GET['user_id']) ? $_GET['user_id'] : 0;

        $chat_id = helper::clearInt($chat_id);
        $user_id = helper::clearInt($user_id);

        $user = new profile($dbo, $user_id);
        $user->setRequestFrom(auth::getCurrentUserId());
        $user_info = $user->get();
        unset($user);

        if ($user_info['error']) {

            header('Location: /');
            exit;
        }

        $chat_id_test = $messages->getChatId(auth::getCurrentUserId(), $user_id);

        if ($chat_id != 0 && $chat_id_test != $chat_id) {

            header('Location: /');
            exit;
        }

        if ($chat_id == 0) {

            $chat_id = $messages->getChatId(auth::getCurrentUserId(), $user_id);

            if ($chat_id != 0) {

                header('Location: /account/chat?chat_id='.$chat_id.'&user_id='.$user_id);
                exit;
            }
        }

        if ($chat_id != 0) {

            $chat_info = $messages->get($chat_id, 0);

            $chat_from_user_id = $chat_info['chatFromUserId'];
            $chat_to_user_id = $chat_info['chatToUserId'];
        }
    }

    if ($user_info['state'] != ACCOUNT_STATE_ENABLED) {

        $showForm = false;
    }

    if ($user_info['allowMessages'] == 0 && $user_info['friend'] === false) {

        $showForm = false;
    }

    $blacklist = new blacklist($dbo);
    $blacklist->setRequestFrom($user_info['id']);

    if ($blacklist->isExists(auth::getCurrentUserId())) {

        $showForm = false;
    }

    $items_all = $messages->messagesCountByChat($chat_id);
    $items_loaded = 0;

    $page_id = "chat";

    $css_files = array("my.css", "account.css");
    $page_title = $LANG['page-messages']." | ".APP_TITLE;

    include_once("html/common/site_header.inc.php");

?>

<body class="chat-page sn-hide">

    <?php

        include_once("html/common/site_topbar.inc.php");
    ?>

	<div class="wrap content-page">

		<div class="main-column">

            <div class="row">


                <div class="col-4 d-none d-lg-block">

                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><?php echo $LANG['label-chats']; ?></h3>
                        </div>
                        <div class="card-body p-0">

                            <?php

                            $result = $messages->getDialogs_new(0);

                            $chats_loaded = count($result['chats']);

                            if ($chats_loaded != 0) {

                                ?>

                                <div class="cards-list chats-content-list">

                                    <?php

                                    foreach ($result['chats'] as $key => $value) {

                                        drawChatItem($value, $chat_id, $LANG, $helper);
                                    }
                                    ?>
                                </div>

                                <?php

                            }
                            ?>

                        </div>
                    </div>

                </div>

                <div class="col-12 col-lg-8">

                    <div class="card main-content" style="max-width: 100%">

                        <div class="card-header">
                            <h3 class="card-title"><?php echo $user_info['fullname']; ?></h3>
                        </div>

                        <div class="card-body standard-page p-2">

                            <div class="content-list-page">

                                <?php

                                if ($items_all > 20) {

                                    ?>

                                    <header class="top-banner loading-banner">

                                        <div class="prompt">
                                            <button onclick="Messages.more('<?php echo $chat_id ?>', '<?php echo $user_id ?>'); return false;" class="button more loading-button noselect"><?php echo $LANG['action-more']; ?></button>
                                        </div>

                                    </header>

                                    <?php
                                }

                                ?>

                                <ul class="cards-list content-list">

                                <?php

                                $result = $chat_info;

                                $items_loaded = count($result['messages']);

                                if ($items_loaded != 0) {


                                        foreach (array_reverse($result['messages']) as $key => $value) {

                                            draw::messageItem($value, $user_info, $LANG, $helper);
                                        }
                                }

                                ?>

                                </ul>

                                <?php

                                if ($items_loaded == 0) {

                                    ?>

                                    <div class="warning-container mx-2 mb-4 mt-0 empty-list-banner">
                                        <b><?php echo $LANG['label-chat-empty']; ?></b>
                                        <br>
                                        <?php echo $LANG['label-chat-empty-promo']; ?>
                                    </div>

                                    <?php
                                }
                                ?>

                                <?php

                                if ($showForm) {

                                    ?>

                                    <div class="comment_form comment-form standard-page p-2">

                                        <form class="" onsubmit="

                                        <?php

                                        if (auth::getCurrentLevelMode() == 0 && auth::getCurrentFreeMessagesCount() < 1) {

                                            ?>
                                                Messages.showProAlert(); return false;
                                            <?php

                                        } else {

                                            ?>
                                                Messages.create('<?php echo $chat_id; ?>', '<?php echo $user_id; ?>'); return false;
                                            <?php
                                        }
                                        ?>

                                                ">
                                            <input type="hidden" name="message_image" value="">
                                            <div class="d-flex">
                                                <input class="comment_text" name="message_text" maxlength="340" placeholder="<?php echo $LANG['label-placeholder-message']; ?>" type="text" value="">
                                                <button style="padding: 5px 16px; font-size: 18px" class="ml-2 primary_btn blue comment_send mt-0">
                                                    <i class="iconfont icofont-paper-plane"></i>
                                                </button>
                                            </div>

                                            <div class="mt-2 d-flex">

                                                <div class="dropdown emoji-dropdown dropup" style="">

                                                    <div class="smile-button btn-emoji-picker flat_btn mr-1" data-toggle="dropdown">
                                                        <i class="iconfont icofont-slightly-smile"></i>
                                                    </div>

                                                    <div class="dropdown-menu dropdown-menu-left">
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

                                                <div class="dropdown emoji-dropdown dropup" style="">

                                                    <span class="smile-button btn-sticker-picker flat_btn mr-1" data-toggle="dropdown">
                                                        <i class="iconfont icofont-label"></i>
                                                    </span>

                                                    <div class="dropdown-menu dropdown-menu-left">
                                                        <div class="sticker-items">

                                                            <?php

                                                            $stickers = new sticker($dbo);
                                                            $stickers->setRequestFrom(auth::getCurrentUserId());

                                                            $result = $stickers->db_get(0, 300);

                                                            foreach ($result['items'] as $item) {

                                                                ?>
                                                                <div data-id="<?php echo $item['id']; ?>" data-img-url="<?php echo $item['imgUrl']; ?>" class="sticker-item" style="background-image: url('<?php echo $item['imgUrl']; ?>');"></div>
                                                                <?php
                                                            }
                                                            ?>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="smile-button flat_btn button image-upload-button">
                                                    <input type="file" id="image-upload" name="uploaded_file">
                                                    <i class="iconfont icofont-ui-image"></i>
                                                </div>

                                                <div class="image-upload-progress hidden">
                                                    <div style="height: 1rem;" class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                                                </div>

                                            </div>

                                            <div class="image-upload-img mt-2 hidden">
                                                <img style="width: 100%;" class="msg_img_preview" src="/assets/img/camera.png">
                                                <span title="<?php echo $LANG['action-remove']; ?>" class="remove" onclick="removeUploadedImg(); return false;">×</span>
                                            </div>

                                        </form>

                                    </div>

                                    <?php
                                }
                                ?>


                            </div>

                        </div>

                    </div>

                </div>

            </div>
		</div>

	</div>

    <div class="modal modal-form fade level-mode-dlg" id="level-mode-dlg" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title placeholder-title"><?php echo APP_TITLE; ?></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true"></span>
                    </button>
                </div>

                <div class="modal-body">

                    <div class="error-summary alert alert-warning"><?php echo $LANG['label-level-mode-alert']; ?></div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn button btn-secondary" data-dismiss="modal"><?php echo $LANG['action-close']; ?></button>
                    <a class="btn button btn-primary" href="/account/upgrades"><?php echo $LANG['page-upgrades']; ?></a>
                </div>

            </div>
        </div>
    </div>

    <div class="modal modal-form fade otp-verification-dlg" id="otp-verification-dlg" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title placeholder-title"><?php echo APP_TITLE; ?></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true"></span>
                    </button>
                </div>

                <div class="modal-body">

                    <div class="error-summary alert alert-warning"><?php echo $LANG['label-otp-verification-promo']; ?></div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn button btn-secondary" data-dismiss="modal"><?php echo $LANG['action-cancel']; ?></button>
                    <a class="btn button btn-primary" href="/account/settings/otp"><?php echo $LANG['page-otp']; ?></a>
                </div>

            </div>
        </div>
    </div>

        <?php

            include_once("html/common/site_footer.inc.php");
        ?>

        <script type="text/javascript" src="/js/chat.js"></script>


        <script type="text/javascript">

            var items_all = <?php echo $items_all; ?>;
            var items_loaded = <?php echo $items_loaded; ?>;
            var chat_id = <?php echo $chat_id; ?>;
            var chat_from_user_id = <?php echo $chat_from_user_id; ?>;
            var chat_to_user_id = <?php echo $chat_to_user_id; ?>;

            var $infobox = $('#info-box');

            $(document).ready(function(){

                if (chat_id != 0) {

                    App.chatInit('<?php echo $chat_id; ?>', '<?php echo $user_id; ?>', '<?php echo auth::getAccessToken(); ?>');

                    if (chat_from_user_id != 0 && chat_to_user_id != 0) {

                        Messages.updateChat(chat_id, chat_from_user_id, chat_to_user_id);
                    }
                }

                $("body").on("click", "a", function() {

                    if (chat_id != 0 && chat_from_user_id != 0 && chat_to_user_id != 0) {

                        Messages.updateChat(chat_id, chat_from_user_id, chat_to_user_id);
                    }
                });

                $(document).on('click', '.sticker-item', function() {

                    Messages.sendSticker('<?php echo $chat_id; ?>', '<?php echo $user_id; ?>', $(this).attr('data-id'), $(this).attr('data-img-url'));

                    $(".btn-sticker-picker").dropdown('toggle');

                    return false;
                });
            });

            Messages.showProAlert = function() {

                $('#level-mode-dlg').modal('show');
            };

            $("#image-upload").fileupload({
                formData: {accountId: <?php echo auth::getCurrentUserId(); ?>, accessToken: "<?php echo auth::getAccessToken(); ?>"},
                name: 'image',
                url: "/api/" + options.api_version + "/method/msg.uploadImg",
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
                    "maxNumberOfFiles": "Maximum number of files exceeded",
                    "acceptFileTypes": "File type not allowed",
                    "maxFileSize": "File is too big",
                    "minFileSize": "File is too small"},
                process: true,
                start: function (e, data) {

                    console.log("start");

                    $('div.image-upload-progress').removeClass("hidden");
                    $('div.image-upload-button').addClass('hidden');
                    $('div.image-upload-img').addClass('hidden');
                    $('button.comment_send').addClass("hidden");
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

                    $('div.image-upload-progress').find('.progress-bar').attr('aria-valuenow', progress).css('width', progress + '%').text(progress + '%');
                },
                done: function (e, data) {

                    console.log("done");

                    var result = jQuery.parseJSON(data.jqXHR.responseText);

                    if (result.hasOwnProperty('error')) {

                        if (result.error === false) {

                            if (result.hasOwnProperty('imgUrl')) {

                                $("input[name=message_image]").val(result.imgUrl);
                                $("img.msg_img_preview").attr("src", result.imgUrl);

                                $('div.image-upload-img').removeClass('hidden');
                            }

                        } else {

                            $('div.image-upload-button').removeClass('hidden');

                            $infobox.find('#info-box-message').text(result.error_description);
                            $infobox.modal('show');

                        }
                    }
                },
                fail: function (e, data) {

                    console.log("always");
                },
                always: function (e, data) {

                    console.log("always");

                    $('button.comment_send').removeClass("hidden");
                    $('div.image-upload-progress').addClass("hidden");
                }

            });

            function removeUploadedImg() {

                $('div.image-upload-progress').addClass("hidden");
                $('div.image-upload-img').addClass('hidden');
                $('div.image-upload-button').removeClass('hidden');
                $("input[name=message_image]").val("");
            }

        </script>


</body>
</html>

<?php

function drawChatItem($item, $current_chat_id, $LANG, $helper)
{
    $time = new language(NULL, $LANG['lang-code']);
    $profilePhotoUrl = "/assets/img/profile_default_photo.png";

    if (strlen($item['withUserPhotoUrl']) != 0) {

        $profilePhotoUrl = $item['withUserPhotoUrl'];
    }

    ?>

    <li class="card-item classic-item default-item" data-id="<?php echo $item['id']; ?>" >
        <a class="touch-item d-block <?php if ($current_chat_id == $item['id']) echo "active"; ?>" href="/account/chat?chat_id=<?php echo $item['id']; ?>&user_id=<?php echo $item['withUserId']; ?>">
            <div class="card-body p-2 bg-transparent">
                <span class="card-header p-0 border-0">
                    <span>
                        <img class="card-icon" src="<?php echo $profilePhotoUrl; ?>"/>
                    </span>

                    <?php if ($item['withUserOnline']) echo "<span title=\"Online\" class=\"card-online-icon\"></span>"; ?>
                    <div class="card-content">
                        <div class="card-title">
                            <span><?php echo $item['withUserFullname']; ?></span>
                        </div>
                        <span class="card-status-text">

                            <?php

                            if (strlen($item['lastMessage']) == 0) {

                                echo "Image";

                            } else {

                                echo $item['lastMessage'];
                            }
                            ?>

                            <?php

                            if ($item['newMessagesCount'] != 0 && $current_chat_id != $item['id']) {

                                ?>
                                <span class="card-counter red"><?php echo $item['newMessagesCount']; ?></span>
                                <?php
                            }
                            ?>

                        </span>
                    </div>
                </span>
            </div>
        </a>
    </li>

    <?php
}