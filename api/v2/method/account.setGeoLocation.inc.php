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

    $accountId = isset($_POST['accountId']) ? $_POST['accountId'] : 0;
    $accessToken = isset($_POST['accessToken']) ? $_POST['accessToken'] : '';

    $lat = isset($_POST['lat']) ? $_POST['lat'] : '';
    $lng = isset($_POST['lng']) ? $_POST['lng'] : '';

    $lat = helper::clearText($lat);
    $lat = helper::escapeText($lat);

    $lng = helper::clearText($lng);
    $lng = helper::escapeText($lng);

    $result = array("error" => true,
                    "error_code" => ERROR_CODE_INITIATE);

    $auth = new auth($dbo);

    if (!$auth->authorize($accountId, $accessToken)) {

        api::printError(ERROR_ACCESS_TOKEN, "Error authorization.");
    }

    $result = array("error" => false,
                    "error_code" => ERROR_SUCCESS);

    $account = new account($dbo, $accountId);

    if (strlen($lat) > 0 && strlen($lng) > 0) {

        $result = $account->setGeoLocation($lat, $lng);

        $result['lng'] = $lng;

    } else {

        $geo = new geo($dbo);

        $info = $geo->info(helper::ip_addr());

        if ($info['geoplugin_status'] == 206) {

            $result = $account->setGeoLocation($info['geoplugin_latitude'], $info['geoplugin_longitude']);

        } else {

            // 37.421011, -122.084968 | Mountain View, CA 94043, USA   ;)

            $result = $account->setGeoLocation("37.421011", "-122.084968");
        }
    }

    echo json_encode($result);
    exit;
}
