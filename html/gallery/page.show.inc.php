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

    if (!auth::isSession()) {

        header("Location: /");
        exit;
    }

	$profileId = $helper->getUserId($request[0]);

	$itemExists = true;

	$profile = new profile($dbo, $profileId);

	$profile->setRequestFrom(auth::getCurrentUserId());
	$profileInfo = $profile->get();

	if ($profileInfo['error']) {

        include_once("html/error.inc.php");
		exit;
	}

	if ($profileInfo['state'] != ACCOUNT_STATE_ENABLED) {

        include_once("html/error.inc.php");
		exit;
	}

    $gallery = new gallery($dbo);
    $gallery->setRequestFrom(auth::getCurrentUserId());

	$itemId = helper::clearInt($request[2]);

	$itemInfo = $gallery->info($itemId);

	if ($itemInfo['error']) {

        // Missing
        $itemExists = false;
	}

	if ($itemExists && $itemInfo['removeAt'] != 0) {

		// Missing
        $itemExists = false;
	}

	if ($itemExists && $profileInfo['id'] != $itemInfo['owner']['id']) {

        // Missing
        $itemExists = false;
    }

    if ($itemExists && auth::getCurrentUserId() != $itemInfo['owner']['id']) {

        $settings = new settings($dbo);
        $settings_arr = $settings->get();
        // Missing
        $itemExists = false;
    }

    $access_denied = false;

    if ($profileInfo['id'] != auth::getCurrentUserId() && $profileInfo['allowShowMyGallery'] == 1 && $itemInfo['showInStream'] == 0) {

        $access_denied = true;
    }

	$page_id = "image";

	$css_files = array("main.css", "my.css");

	$page_title = $profileInfo['fullname']." | ".APP_HOST."/".$profileInfo['username'];

    include_once("html/common/site_header.inc.php");

?>

<body class="">


	<?php
        include_once("html/common/site_topbar.inc.php");
	?>


	<div class="wrap content-page">

        <div class="main-column row">

            <?php

                include_once("html/common/site_sidenav.inc.php");
            ?>

            <div class="col-lg-9 col-md-12" id="content">

                <div class="content-list-page">

                    <?php

                    if ($access_denied) {

                        ?>

                        <div class="card information-banner border-0">
                            <div class="card-header">
                                <div class="card-body">
                                    <h5 class="m-0"><?php echo $LANG['label-error-permission']; ?></h5>
                                </div>
                            </div>
                        </div>

                        <?php

                    } else {

                        if ($itemExists) {

                            if ($itemInfo['owner']['id'] == auth::getCurrentUserId()) {

                                ?>
                                    <div class="main-content">
                                        <div class="gallery-intro-header">
                                                        <h1 class="gallery-title pr-0"><i class="iconfont icofont-verification-check pr-1"></i><?php echo $LANG['label-item-moderation-success']; ?></h1>
                                                    
                                        </div>
                                    </div>

                                <?php
                            }

                            ?>

                            <div class="items-list content-list m-0">

                                <?php

                                    draw::image($itemInfo, $LANG, $helper, false);

                                ?>

                            </div>

                            <?php

                        } else {

                            ?>

                            <div class="card information-banner">
                                <div class="card-header">
                                    <div class="card-body">
                                        <h5 class="m-0"><?php echo $LANG['label-item-missing']; ?></h5>
                                    </div>
                                </div>
                            </div>

                            <?php
                        }
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

		var replyToUserId = 0;

		<?php

            if (auth::getCurrentUserId() == $profileInfo['id']) {

                ?>
					var myPage = true;
				<?php
    		}
		?>

	</script>


</body
</html>