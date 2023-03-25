<div class="card" style="margin-bottom: 150px">
    <div class="card-header">
        <h3 class="card-title"><?php echo $LANG['label-warning']; ?></h3>
        <h5 class="card-description">
            <?php

                if ($profileInfo['state'] == ACCOUNT_STATE_DISABLED) {

                    // deactivated

                    echo $LANG['label-account-disabled'];

                } else {

                    echo $LANG['label-account-blocked'];
                }
            ?>
        </h5>
    </div>
</div>
