<?php



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
