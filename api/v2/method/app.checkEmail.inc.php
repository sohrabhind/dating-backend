<?php

/*!
 * ifsoft.co.uk
 *
 * http://ifsoft.com.ua, http://ifsoft.co.uk
 * hindbyte@gmail.com
 *
 * Copyright 2012-2019 Demyanchuk Dmitry (hindbyte@gmail.com)
 */

if (!empty($_POST)) {

    $email = isset($_POST['email']) ? $_POST['email'] : '';

    $email = helper::clearText($email);
    $email = helper::escapeText($email);

    $result = array("error" => true);

    if (!$helper->isEmailExists($email)) {

        $result = array("error" => false);
    }

    echo json_encode($result);
    exit;
}
