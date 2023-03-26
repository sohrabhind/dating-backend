<?php

    /*!
     * https://hindbyte.com
     * hindbyte@gmail.com
     *
     * Copyright 2012-2022 Demyanchuk Dmitry (hindbyte@gmail.com)
     */

    include('sys/config/gconfig.inc.php');

    $result = array();

    if (isset($_GET["code"]))
    {
        //It will Attempt to exchange a code for an valid authentication token.
        $token = $google_client->fetchAccessTokenWithAuthCode($_GET["code"]);

        //This condition will check there is any error occur during geting authentication token. If there is no any error occur then it will execute if block of code/
        if (!isset($token['error']))
        {
            //Set the access token used for requests
            $google_client->setAccessToken($token['access_token']);

            //Create Object of Google Service OAuth 2 class
            $google_service = new Google_Service_Oauth2($google_client);

            //Get user profile data from google
            $data = $google_service->userinfo->get();

            $google_client->revokeToken();

            //Below you can find Get profile data and store into $_SESSION variable

            $helper = new helper($dbo);
            $account_id = $helper->getUserIdByGoogle($data['id']);

            if (auth::getCurrentUserId() != 0) {

                if ($account_id != 0) {

                    header("Location: /account/settings/services?status=g_error");
                    exit;

                } else {

                    $account = new account($dbo, auth::getCurrentUserId());
                    $account->setGoogleFirebaseId($data['id']);
                    unset($account);

                    header("Location: /account/settings/services?status=g_connected");
                    exit;
                }

            } else {

                if ($account_id != 0) {

                    $account = new account($dbo, $account_id);
                    $account_info = $account->get();

                    if ($account_info['state'] == ACCOUNT_STATE_ENABLED) {

                        $auth = new auth($dbo);
                        $result = $auth->create($account_id, CLIENT_ID, APP_TYPE_WEB);

                        if (!$result['error']) {

                            $account->setLastActive();

                            auth::setSession($result['accountId'], $account_info['username'], $account_info['fullname'], $account_info['lowPhotoUrl'], $account_info['balance'], $account_info['level'], $account_info['free_messages_count'], 0, $result['accessToken']);
                            auth::updateCookie($account_info['username'], $result['accessToken']);

                            header("Location: /");
                            exit;
                        }
                    }

                    unset($account);
                    unset($account_info);

                } else {

                    //new user
                    $_SESSION['oauth'] = 'google';
                    $_SESSION['uid'] = $data['id'];
                    $_SESSION['fullname'] = "Google User";

                    if (!empty($data['given_name']))
                    {

                        $_SESSION['fullname'] = $data['given_name'];
                    }

                    if (!empty($data['family_name']))
                    {

                        $_SESSION['fullname'] = $_SESSION['fullname']." ".$data['family_name'];
                    }

                    $_SESSION['email'] = "";

                    if (!empty($data['email']))
                    {

                        $_SESSION['email'] = $data['email'];
                    }

                    $_SESSION['oauth_img_link'] = "";

                    if (!empty($data['picture']))
                    {

                        $_SESSION['oauth_img_link'] = filter_var($data['picture'], FILTER_VALIDATE_URL);
                    }

                    header("Location: /signup");
                    exit;
                }
            }

        } else {

            header("Location: /");
            exit;
        }

    } else {

        if (isset($_SESSION['oauth'])) {

            unset($_SESSION['oauth']);
            unset($_SESSION['uid']);
            unset($_SESSION['fullname']);
            unset($_SESSION['email']);
            unset($_SESSION['oauth_link']);
            unset($_SESSION['oauth_img_link']);

            header("Location: /signup");
            exit;

        } else {

            header("Location: /");
            exit;
        }
    }