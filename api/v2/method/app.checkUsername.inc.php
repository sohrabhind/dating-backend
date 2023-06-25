<?php



if (!empty($_POST)) {

    $username = isset($_POST['username']) ? $_POST['username'] : '';

    $username = helper::clearText($username);
    $username = helper::escapeText($username);

    $result = array("error" => true);

    if (!$helper->isUserExists($username)) {

        $result = array("error" => false);
    }

    echo json_encode($result);
    exit;
}
