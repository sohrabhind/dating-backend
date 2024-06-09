<?php

if (!auth::isSession()) {

?>

    <div class="top-header">
        <div class="container">
            <div class="d-flex">

                <a class="logo" href="/">
                    <img class="header-brand-img" src="/assets/icons/logo.png" alt="<?php echo APP_NAME; ?>>">&nbsp;<?php echo APP_TITLE; ?>
                </a>

            </div>
        </div>
    </div>

<?php

}

?>