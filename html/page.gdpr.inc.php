<?php

    /*!
     * ifsoft.co.uk
     *
     * http://ifsoft.com.ua, http://ifsoft.co.uk
     * hindbyte@gmail.com
     *
     * Copyright 2012-2019 Demyanchuk Dmitry (hindbyte@gmail.com)
     */

    $page_id = "gdpr";

    $css_files = array("my.css");
    $page_title = $LANG['page-gdpr']." | ".APP_TITLE;

    include_once("html/common/site_header.inc.php");

    ?>

<body class="about-page sn-hide">


    <?php
        include_once("html/common/site_topbar.inc.php");
    ?>


    <div class="wrap content-page">

        <div class="main-column">

            <div class="main-content">

                <?php

                    if (file_exists("html/gdpr/".$LANG['lang-code'].".inc.php")) {

                        include_once("html/gdpr/".$LANG['lang-code'].".inc.php");

                    } else {

                        include_once("html/gdpr/en.inc.php");
                    }
                ?>

            </div>

        </div>

    </div>

    <?php

        include_once("html/common/site_footer.inc.php");
    ?>


</body
</html>