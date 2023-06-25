<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title><?php echo $page_title; ?></title>
    <meta name="google-site-verification" content="" />
    <meta name='yandex-verification' content='' />
    <meta name="msvalidate.01" content="" />
    <meta property="og:site_name" content="<?php echo APP_TITLE; ?>">
    <meta property="og:title" content="<?php echo $page_title; ?>">

    <link rel="stylesheet" href="/css/bootstrap-grid.css" type="text/css" media="screen">
    <link rel="stylesheet" href="/css/bootstrap.css" type="text/css" media="screen">
    <link rel="stylesheet" href="/css/bootstrap-slider.css" type="text/css" media="screen">
    <link rel="stylesheet" href="/css/icofont.css" type="text/css" media="screen">
    <link rel="stylesheet" href="/css/font-awesome.css" type="text/css" media="screen">
    <link rel="stylesheet" href="/css/blueimp-gallery.min.css" type="text/css" media="screen">

    <link rel="stylesheet" href="/css/main.css" type="text/css" media="screen,projection"/>
    <link rel="stylesheet" href="/css/my.css?x=1" type="text/css" media="screen,projection"/>

    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>

    <meta charset="utf-8">
    <meta name="description" content="">
    <link href="/assets/img/favicon.png" rel="shortcut icon" type="image/x-icon">
    <?php
        foreach($css_files as $css): ?>
        <link rel="stylesheet" href="/css/<?php echo $css."?x=10"; ?>" type="text/css" media="screen">
    <?php
        endforeach;
    ?>

    <?php

        if (isset($page_id) && $page_id === "signup" || isset($page_id) && $page_id === "update" || isset($page_id) && $page_id === "main" || isset($page_id) && $page_id === "support" || isset($page_id) && $page_id === "remind" || isset($page_id) && $page_id === "otp") {

            ?>
            <script src="https://www.google.com/recaptcha/api.js?render=<?php echo RECAPTCHA_SITE_KEY; ?>"></script>
            <?php
        }

        if (isset($page_id) && $page_id === "otp") {

            ?>
                <script src="https://www.gstatic.com/firebasejs/7.17.1/firebase-app.js"></script>

            <script src="https://www.gstatic.com/firebasejs/7.17.1/firebase-auth.js"></script>
            <?php
        }
    ?>
</head>