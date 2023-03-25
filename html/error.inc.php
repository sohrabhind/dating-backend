<?php

    /*!
     * ifsoft.co.uk
     *
     * http://ifsoft.com.ua, http://ifsoft.co.uk
     * hindbyte@gmail.com
     *
     * Copyright 2012-2019 Demyanchuk Dmitry (hindbyte@gmail.com)
     */

    $page_id = "error";

    $css_files = array("main.css", "my.css");
    $page_title = $LANG['page-error-404']." | ".APP_TITLE;

    include_once("html/common/site_header.inc.php");

?>

<body class="remind-page sn-hide">

    <?php

        include_once("html/common/site_topbar.inc.php");
    ?>

    <div class="wrap content-page">

        <div class="main-column">

            <div class="main-content">

                <header class="top-banner">

                    <div class="info">
                        <h1><?php echo $LANG['label-error-404']; ?></h1>
                    </div>

                    <div class="prompt">
                        <a href="/" class="post-job-button button green">
                            <?php echo $LANG['action-back-to-main-page']; ?>
                        </a>
                    </div>

                </header>

            </div>
        </div>

    </div>

    <?php

    include_once("html/common/site_footer.inc.php");
    ?>


</body
</html>