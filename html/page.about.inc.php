<?php

    /*!
     * ifsoft.co.uk
     *
     * http://ifsoft.com.ua, http://ifsoft.co.uk
     * hindbyte@gmail.com
     *
     * Copyright 2012-2019 Demyanchuk Dmitry (hindbyte@gmail.com)
     */

    $page_id = "about";

    $css_files = array("my.css");
    $page_title = $LANG['page-about'];

    include_once("html/common/site_header.inc.php");

    ?>

<body class="about-page sn-hide">


    <?php
        include_once("html/common/site_topbar.inc.php");
    ?>


    <div class="wrap content-page">

        <div class="main-column">

            <div class="main-content">

                <section class="standard-page">
                    <h1><?php echo $LANG['page-about']; ?></h1>
                    <p><?php echo APP_TITLE." ".APP_VERSION." (web version) © ".APP_YEAR; ?></p>
                </section>

                <section class="standard-page">
                    <h1>About Example Section title</h1>

                    <h3>About Example sub-title</h3>

                    <p>About Example text. About Example text. About Example text. About Example text. About Example text.</p>

                </section>

            </div>

        </div>

    </div>

    <?php

        include_once("html/common/site_footer.inc.php");
    ?>


</body
</html>