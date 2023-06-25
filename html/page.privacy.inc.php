<?php

    

    $page_id = "privacy";

    $css_files = array("my.css");
    $page_title = $LANG['page-privacy']." | ".APP_TITLE;

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

                    if (file_exists("html/privacy/".$LANG['lang-code'].".inc.php")) {

                        include_once("html/privacy/".$LANG['lang-code'].".inc.php");

                    } else {

                        include_once("html/privacy/en.inc.php");
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