<?php

    /*!
     * ifsoft.co.uk
     *
     * http://ifsoft.com.ua, http://ifsoft.co.uk
     * hindbyte@gmail.com
     *
     * Copyright 2012-2019 Demyanchuk Dmitry (hindbyte@gmail.com)
     */

    if (!$auth->authorize(auth::getCurrentUserId(), auth::getAccessToken())) {

        auth::unsetSession();

        header('Location: /');
        exit;
    }

    $profile = new profile($dbo, auth::getCurrentUserId());

    $messages = new msg($dbo);
    $messages->setRequestFrom(auth::getCurrentUserId());

    if (isset($_GET['action'])) {

        $messages_count = $messages->getNewMessagesCount();

        echo $messages_count;
        exit;
    }

    $account = new account($dbo, auth::getCurrentUserId());
    $account->setLastActive();
    unset($account);

    $inbox_all = $messages->myActiveChatsCount();
    $inbox_loaded = 0;

    if (!empty($_POST)) {

        $messageCreateAt = isset($_POST['messageCreateAt']) ? $_POST['messageCreateAt'] : 0;
        $loaded = isset($_POST['loaded']) ? $_POST['loaded'] : 0;

        $messageCreateAt = helper::clearInt($messageCreateAt);
        $loaded = helper::clearInt($loaded);

        $result = $messages->getDialogs_new($messageCreateAt);

        $inbox_loaded = count($result['chats']);

        $result['chats_loaded'] = $inbox_loaded + $loaded;
        $result['chats_all'] = $inbox_all;

        if ($inbox_all != 0) {

            ob_start();

            foreach ($result['chats'] as $key => $value) {

                draw($value, $LANG, $helper);
            }

            if ($result['chats_loaded'] < $inbox_all) {

                ?>

                <header class="top-banner loading-banner">

                    <div class="prompt">
                        <button onclick="Messages.moreItems('<?php echo $result['messageCreateAt']; ?>'); return false;" class="button more loading-button"><?php echo $LANG['action-more']; ?></button>
                    </div>

                </header>

            <?php
            }

            $result['html'] = ob_get_clean();
        }

        echo json_encode($result);
        exit;
    }

    $page_id = "messages";

    $css_files = array("my.css", "account.css");
    $page_title = $LANG['page-messages']." | ".APP_TITLE;

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
                            <?php echo $LANG['page-messages']; ?>
                        </div>
                        <div class="page-title-content-bottom-inner">
                            <?php echo $LANG['label-messages-sub-title']; ?>
                        </div>
                    </div>

                    <div class="content-list-page">

                        <?php

                        $result = $messages->getDialogs_new(0);

                        $inbox_loaded = count($result['chats']);

                        if ($inbox_loaded != 0) {

                            ?>

                            <ul class="cards-list content-list">

                                <?php

                                    foreach ($result['chats'] as $key => $value) {

                                        draw($value, $LANG, $helper);
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

                            if ($inbox_all > 20) {

                                ?>

                                <header class="top-banner loading-banner">

                                    <div class="prompt">
                                        <button onclick="Messages.moreItems('<?php echo $result['messageCreateAt']; ?>'); return false;" class="button more loading-button"><?php echo $LANG['action-more']; ?></button>
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

    <script type="text/javascript" src="/js/chat.js"></script>

        <script type="text/javascript">

            var inbox_all = <?php echo $inbox_all; ?>;
            var inbox_loaded = <?php echo $inbox_loaded; ?>;

            window.Messages || ( window.Messages = {} );

            Messages.moreItems = function (offset) {

                $.ajax({
                    type: 'POST',
                    url: '/account/messages',
                    data: 'messageCreateAt=' + offset + "&loaded=" + inbox_loaded,
                    dataType: 'json',
                    timeout: 30000,
                    success: function(response){

                        $('header.loading-banner').remove();

                        if (response.hasOwnProperty('html')){

                            $("ul.content-list").append(response.html);
                        }

                        inbox_loaded = response.inbox_loaded;
                        inbox_all = response.inbox_all;
                    },
                    error: function(xhr, type){

                    }
                });
            };

        </script>

</body>
</html>

<?php

    function draw($chat, $LANG, $helper)
    {

        $profilePhotoUrl = "/assets/img/profile_default_photo.png";

        if (strlen($chat['withUserPhotoUrl']) != 0) {

            $profilePhotoUrl = $chat['withUserPhotoUrl'];
        }

        $time = new language(NULL, $LANG['lang-code']);

        ?>

            <li class="card-item classic-item default-item chat-item" data-id="<?php echo $chat['id']; ?>">
                <div class="card-body">
                    <span class="card-header">
                        <a href="/<?php echo $chat['withUserUsername']; ?>">
                            <img class="card-icon" src="<?php echo $profilePhotoUrl; ?>"/>
                        </a>

                        <?php if ($chat['withUserOnline']) echo "<span title=\"Online\" class=\"card-online-icon\"></span>"; ?>

                        <div class="card-content">
                            <span class="card-title">
                                <a href="/<?php echo $chat['withUserUsername']; ?>"><?php echo $chat['withUserFullname']; ?></a>
                            </span>

                            <span class="card-date"><?php echo $time->timeAgo($chat['lastMessageCreateAt']); ?></span>

                            <span class="card-status-text">

                                <?php

                                    if (strlen($chat['lastMessage']) == 0) {

                                        echo "Image";

                                    } else {

                                        echo $chat['lastMessage'];
                                    }
                                ?>

                            </span>

                            <?php

                                if ($chat['newMessagesCount'] != 0) {

                                    ?>
                                        <span class="card-counter red"><?php echo $chat['newMessagesCount']; ?></span>
                                    <?php
                                }
                            ?>

                            <span class="card-action">
                                <a href="javascript:void(0)" onclick="Messages.removeChat('<?php echo $chat['id']; ?>', '<?php echo $chat['withUserId']; ?>'); return false;" class="card-act negative"><?php echo $LANG['action-remove']; ?></a>
                                <a href="/account/chat?chat_id=<?php echo $chat['id']; ?>&user_id=<?php echo $chat['withUserId']; ?>" class="card-act active"><?php echo $LANG['action-go-to-conversation']; ?></a>
                            </span>
                        </div>
                    </span>
                </div>
            </li>

        <?php
    }

?>