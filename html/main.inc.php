<?php

$page_id = "main";

$css_files = array("landing.css", "my.css");
$page_title = APP_TITLE;

include_once("html/common/site_header.inc.php");

?>

<body class="home" id="main-page">

    <?php

    include_once("html/common/site_topbar.inc.php");
    ?>

    <div class="content-page">

        <div class="limiter">


            <?php

            if (strlen(GOOGLE_PLAY_LINK) != 0) {

            ?>
                <div class="wrap-landing-info-container">

                    <div class="wrap-landing-info">
                        <?php echo sprintf($LANG['label-prompt-app'], APP_TITLE, APP_TITLE) ?>
                        <a href="<?php echo GOOGLE_PLAY_LINK; ?>" target="_blank" rel="nofollow">
                            <img class="mt-4" width="170" src="/assets/icons/google_play.png">
                        </a>
                    </div>
                </div>
            <?php
            }

            $app = new app($dbo);
            $result = $app->getPreviewProfiles();
            unset($app);

            if (count($result['items']) > 2) {

            ?>
                <div class="wrap-landing-info-container mt-5">

                    <div class="wrap-landing-info">

                        <?php

                        foreach ($result['items'] as $key => $value) {

                        ?>
                            <span class="avatar" style="background-image: url('<?php echo $value['photoUrl']; ?>')"></span>
                        <?php
                        }
                        ?>

                    </div>

                </div>
            <?php
            }
            ?>


            <div id="main-footer" style=" position: fixed;
            padding: 10px 10px 0px 10px;
            bottom: 0;
            width: 100%;
            /* Height of the footer*/
            height: 40px;
            background: grey;
  background-color: grey;
  color: grey;">
                <div class="wrap">

                    <ul id="footer-nav">
                        <li><a href="/privacy"><?php echo $LANG['footer-privacy']; ?></a></li>
                        <li><a href="/terms"><?php echo $LANG['footer-terms']; ?></a></li>
                        <li><a href="/support"><?php echo $LANG['footer-support']; ?></a></li>

                        <li id="footer-copyright">
                            © <?php echo APP_YEAR; ?> <?php echo APP_TITLE; ?>
                        </li>
                    </ul>

                </div>
            </div>

        </div>


    </div>



</body </html>