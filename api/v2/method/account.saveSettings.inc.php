<?php

/*!
 * ifsoft.co.uk
 *
 * http://ifsoft.com.ua, https://ifsoft.co.uk, https://hindbyte.com
 * hindbyte@gmail.com
 *
 * Copyright 2012-2020 Demyanchuk Dmitry (hindbyte@gmail.com)
 */

if (!empty($_POST)) {

    $accountId = isset($_POST['accountId']) ? $_POST['accountId'] : '';
    $accessToken = isset($_POST['accessToken']) ? $_POST['accessToken'] : '';

    $fullname = isset($_POST['fullname']) ? $_POST['fullname'] : '';
    $location = isset($_POST['location']) ? $_POST['location'] : '';
    $instagramPage = isset($_POST['instagramPage']) ? $_POST['instagramPage'] : '';
    $bio = isset($_POST['bio']) ? $_POST['bio'] : '';

    $gender = isset($_POST['gender']) ? $_POST['gender'] : 0;
    $year = isset($_POST['year']) ? $_POST['year'] : 0;
    $month = isset($_POST['month']) ? $_POST['month'] : 0;
    $day = isset($_POST['day']) ? $_POST['day'] : 0;

    $u_age = isset($_POST['age']) ? $_POST['age'] : 0;

    $u_height = isset($_POST['height']) ? $_POST['height'] : 0;
    $u_weight = isset($_POST['weight']) ? $_POST['weight'] : 0;

    $iStatus = isset($_POST['iStatus']) ? $_POST['iStatus'] : 0;
    $religiousViews = isset($_POST['religiousViews']) ? $_POST['religiousViews'] : 0;
    $smokingViews = isset($_POST['smokingViews']) ? $_POST['smokingViews'] : 0;
    $alcoholViews = isset($_POST['alcoholViews']) ? $_POST['alcoholViews'] : 0;
    $lookingViews = isset($_POST['lookingViews']) ? $_POST['lookingViews'] : 0;
    $interestedViews = isset($_POST['interestedViews']) ? $_POST['interestedViews'] : 0;

    $allowShowMyBirthday = isset($_POST['allowShowMyBirthday']) ? $_POST['allowShowMyBirthday'] : 0;

    $iStatus = helper::clearInt($iStatus);
    $religiousViews = helper::clearInt($religiousViews);
    $smokingViews = helper::clearInt($smokingViews);
    $alcoholViews = helper::clearInt($alcoholViews);
    $lookingViews = helper::clearInt($lookingViews);
    $interestedViews = helper::clearInt($interestedViews);

    $allowShowMyBirthday = helper::clearInt($allowShowMyBirthday);

    $accountId = helper::clearInt($accountId);

    $fullname = helper::clearText($fullname);
    $fullname = helper::escapeText($fullname);

    $location = helper::clearText($location);
    $location = helper::escapeText($location);

    $instagramPage = helper::clearText($instagramPage);
    $instagramPage = helper::escapeText($instagramPage);

    $bio = helper::clearText($bio);

    $bio = preg_replace( "/[\r\n]+/", " ", $bio);    //replace all new lines to one new line
    $bio = preg_replace('/\s+/', ' ', $bio);        //replace all white spaces to one space

    $bio = helper::escapeText($bio);

    $gender = helper::clearInt($gender);

    $u_age = helper::clearInt($u_age);
    $u_height = helper::clearInt($u_height);
    $u_weight = helper::clearInt($u_weight);

    $year = helper::clearInt($year);
    $month = helper::clearInt($month);
    $day = helper::clearInt($day);

    $auth = new auth($dbo);

    if (!$auth->authorize($accountId, $accessToken)) {

        api::printError(ERROR_ACCESS_TOKEN, "Error authorization.");
    }

    $result = array("error" => true,
                    "error_code" => ERROR_CODE_INITIATE);

    $account = new account($dbo, $accountId);
    $account->setLastActive();

    if ($u_age > 17 && $u_age < 111) {

        $account->setAge($u_age);
    }

    if ($u_height > -1 && $u_height < 300) {

        $account->setHeight($u_height);
    }

    if ($u_weight > -1 && $u_weight < 300) {

        $account->setWeight($u_weight);
    }

    $account->setFullname($fullname);
    $account->setLocation($location);
    $account->setStatus($bio);

    $account->setGender($gender);
    $account->setBirth($year, $month, $day);

    $account->set_iStatus($iStatus);
    $account->set_iReligiousView($religiousViews);
    $account->set_iSmokingViews($smokingViews);
    $account->set_iAlcoholViews($alcoholViews);
    $account->set_iLooking($lookingViews);
    $account->set_iInterested($interestedViews);

    $account->set_allowShowMyBirthday($allowShowMyBirthday);
    

    if (helper::isValidURL($instagramPage)) {

        $account->setInstagramPage($instagramPage);

    } else {

        $account->setInstagramPage("");
    }

    $result = $account->get();

    echo json_encode($result);
    exit;
}
