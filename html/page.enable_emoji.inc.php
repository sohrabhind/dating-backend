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

    $page_id = "emoji";

    include_once("sys/core/initialize.inc.php");

    $update = new update($dbo);
    $update->setChatEmojiSupport();
    $update->setDialogsEmojiSupport();
    $update->setPhotosEmojiSupport();
    $update->setGiftsEmojiSupport();
    $update->setImagesCommentsEmojiSupport();
    unset($update);

    $css_files = array("my.css");
    $page_title = APP_TITLE;

    include_once("html/common/site_header.inc.php");
?>

<body class="remind-page sn-hide">

    <?php

        include_once("html/common/site_topbar.inc.php");
    ?>

    <div class="wrap content-page">
        <div class="main-column">
            <div class="main-content">

                <div class="standard-page">

                    <div class="success-container" style="margin-top: 15px;">
                        <ul>
                            <b>Success!</b>
                            <br>
                            Your MySQL version:
                            <?php

                            if (function_exists('mysql_get_client_info')) {

                                print mysql_get_client_info();

                            } else {

                                echo $dbo->query('select version()')->fetchColumn();
                            }
                            ?>
                            <br>
                            Database refactoring success!
                        </ul>
                    </div>

                </div>

            </div>
        </div>

    </div>

    <?php

        include_once("html/common/site_footer.inc.php");
    ?>

</body>
</html>