<?php



if (!empty($_POST)) {

    $accountId = isset($_POST['accountId']) ? $_POST['accountId'] : '';
    $accessToken = isset($_POST['accessToken']) ? $_POST['accessToken'] : '';

    $fullname = isset($_POST['fullname']) ? $_POST['fullname'] : '';
    $location = isset($_POST['location']) ? $_POST['location'] : '';
    $interests = isset($_POST['interests']) ? $_POST['interests'] : '';
    $bio = isset($_POST['bio']) ? $_POST['bio'] : '';

    $gender = isset($_POST['gender']) ? $_POST['gender'] : 1;

    $u_age = isset($_POST['age']) ? $_POST['age'] : 0;

    $u_height = isset($_POST['height']) ? $_POST['height'] : 0;

    $religiousViews = isset($_POST['religiousViews']) ? $_POST['religiousViews'] : 0;
    $smokingViews = isset($_POST['smokingViews']) ? $_POST['smokingViews'] : 0;
    $alcoholViews = isset($_POST['alcoholViews']) ? $_POST['alcoholViews'] : 0;
    $lookingViews = isset($_POST['lookingViews']) ? $_POST['lookingViews'] : 0;
    $interestedViews = isset($_POST['interestedViews']) ? $_POST['interestedViews'] : 0;

    $religiousViews = helper::clearInt($religiousViews);
    $smokingViews = helper::clearInt($smokingViews);
    $alcoholViews = helper::clearInt($alcoholViews);
    $lookingViews = helper::clearInt($lookingViews);
    $interestedViews = helper::clearInt($interestedViews);

    $accountId = helper::clearInt($accountId);

    $fullname = helper::clearText($fullname);
    $fullname = helper::escapeText($fullname);

    $location = helper::clearText($location);
    $location = helper::escapeText($location);

    $interests = helper::clearText($interests);
    $interests = helper::escapeText($interests);

    $bio = helper::clearText($bio);

    $bio = preg_replace( "/[\r\n]+/", " ", $bio);    //replace all new lines to one new line
    $bio = preg_replace('/\s+/', ' ', $bio);        //replace all white spaces to one space

    $bio = helper::escapeText($bio);

    $gender = helper::clearInt($gender);

    $u_age = helper::clearInt($u_age);
    $u_height = helper::clearInt($u_height);

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

    $account->setFullname($fullname);
    $account->setLocation($location);
    $account->setBio($bio);

    $account->setGender($gender);
    
    $account->set_iReligiousView($religiousViews);
    $account->set_iSmokingViews($smokingViews);
    $account->set_iAlcoholViews($alcoholViews);
    $account->set_iLooking($lookingViews);
    $account->set_iInterested($interestedViews);
    $account->setInterests($interests);


    $result = $account->get();

    echo json_encode($result);
    exit;
}
