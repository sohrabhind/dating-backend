<?php

    /*!
     * ifsoft engine
     *
     * http://ifsoft.com.ua, http://ifsoft.co.uk
     * hindbyte@gmail.com
     *
     * Copyright 2012-2019 Demyanchuk Dmitry (hindbyte@gmail.com)
     */

    $page_id = "restore_success";

    $css_files = array("my.css", "landing.css");
    $page_title = APP_TITLE;

    include_once("html/common/site_header.inc.php");
?>

<body class="home">

    <?php

        include_once("html/common/site_topbar.inc.php");
    ?>

    <div class="content-page">

        <div class="limiter">

            <div class="container-login100">

                <div class="wrap-login100">

                    <div class="standard-page">

                        <h1><?php echo $LANG['label-success']; ?>!</h1>

                        <div class="opt-in">
                            <label for="user_receive_digest">
                                <b><?php echo $LANG['label-password-reset-success']; ?></span></b>
                            </label>
                        </div>
                    </div>

                </div>

            </div>

            <?php

                include_once("html/common/site_footer.inc.php");
            ?>

        </div>


    </div>

</body>
</html>