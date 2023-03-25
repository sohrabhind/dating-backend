<?php

/*!
	 * https://hindbyte.com
	 * hindbyte@gmail.com
	 *
	 * Copyright 2012-2021 Demyanchuk Dmitry (hindbyte@gmail.com)
	 */

session_start();
error_reporting(E_ALL); // set to 0 when you resource is ready for all
define("APP_SIGNATURE", "hindbyte"); // Add signature constant to protect include modules
include_once("sys/core/init.inc.php");

$page_id = '';

// Auto authorize if installed cookie

if (!auth::isSession() && isset($_COOKIE['user_name']) && isset($_COOKIE['user_password'])) {

    $account = new account($dbo, $helper->getUserId($_COOKIE['user_name']));

    $accountInfo = $account->get();

    if (!$accountInfo['error'] && $accountInfo['state'] == ACCOUNT_STATE_ENABLED) {

        $auth = new auth($dbo);

        if ($auth->authorize($accountInfo['id'], $_COOKIE['user_password'])) {

            auth::setSession($accountInfo['id'], $accountInfo['username'], $accountInfo['fullname'], $accountInfo['lowPhotoUrl'], $accountInfo['balance'], $accountInfo['pro'], $accountInfo['free_messages_count'], 0, $_COOKIE['user_password']);

            $account->setLastActive();

        } else {

            auth::clearCookie();
        }

    } else {

        auth::clearCookie();
    }
}

if (!empty($_GET)) {

    if (!isset($_GET['q'])) {

        include_once("html/main.inc.php");
        exit;
    }

    $request = htmlentities($_GET['q'], ENT_QUOTES);
    $request = helper::escapeText($request);
    $request = explode('/', trim($request, '/'));

    $cnt = count($request);

	switch ($cnt) {

		case 0: {

			include_once("html/main.inc.php");
			exit;
		}

		case 1: {

			if (file_exists("html/page.".$request[0].".inc.php")) {

				include_once("html/page.".$request[0].".inc.php");
				exit;

			}  else if ($helper->isUserExists($request[0])) {

				include_once("html/profile.inc.php");
				exit;

			} else {

				include_once("html/error.inc.php");
				exit;
			}
		}

		case 2: {

			if (file_exists( "html/".$request[0]."/page.".$request[1].".inc.php")) {

				include_once("html/" . $request[0] . "/page." . $request[1] . ".inc.php");
				exit;

			} else if (file_exists("html/app/".$request[1].".php")) {

                include_once("html/app/" . $request[1] . ".php");
                exit;

            } else if ($helper->isUserExists($request[0])) {

                if (file_exists("html/profile/page." . $request[1] . ".inc.php")) {

                    include_once("html/profile/page." . $request[1] . ".inc.php");
                    exit;

                } else {

                    include_once("html/error.inc.php");
                    exit;
                }

			} else {

				include_once("html/error.inc.php");
				exit;
			}
		}

		case 3: {

			switch ($request[1]) {

                case 'gallery': {

                    if ($helper->isUserExists($request[0])) {

                        include_once("html/gallery/page.show.inc.php");
                        exit;
                    }

                    break;
                }

                default: {

                    if (file_exists("html/".$request[0]."/".$request[1]."/page.".$request[2].".inc.php")) {

                        include_once("html/".$request[0]."/".$request[1]."/page.".$request[2].".inc.php");
                        exit;

                    } else {

                        include_once("html/error.inc.php");
                        exit;
                    }

                    break;
                }
			}
		}

		case 4: {

            switch ($request[0]) {

                case 'api': {

                    if (file_exists("api/".$request[1]."/method/".$request[3].".inc.php")) {

                        include_once("sys/config/api.inc.php");

                        include_once("api/".$request[1]."/method/".$request[3].".inc.php");
                        exit;

                    } else if (file_exists("html/".$request[0]."/".$request[1]."/".$request[2]."/page.".$request[3].".inc.php")) {

                        include_once("html/".$request[0]."/".$request[1]."/".$request[2]."/page.".$request[3].".inc.php");
                        exit;

                    } else {

                        include_once("html/error.inc.php");
                        exit;
                    }

                    break;
                }

                default: {

                    if ($helper->isUserExists($request[0])) {

                        switch ($request[1]) {

                            case 'gallery' : {

                                if (file_exists("html/gallery/page.".$request[3].".inc.php")) {

                                    include_once("html/gallery/page.".$request[3].".inc.php");
                                    exit;

                                } else {

                                    include_once("html/error.inc.php");
                                    exit;
                                }

                                break;
                            }

                            default: {

                                include_once("html/error.inc.php");
                                exit;
                            }
                        }

                    } else {

                        if ( file_exists("html/".$request[0]."/".$request[1]."/".$request[2]."/page.".$request[3].".inc.php") ) {

                            include_once("html/".$request[0]."/".$request[1]."/".$request[2]."/page.".$request[3].".inc.php");
                            exit;

                        } else {

                            include_once("html/error.inc.php");
                            exit;
                        }
                    }

                    break;
                }
            }
		}

        case 5: {

            switch ($request[0]) {

                case 'api': {

                    if (file_exists("api/".$request[1]."/method/".$request[3]."/".$request[4].".inc.php")) {

                        include_once("sys/config/api.inc.php");

                        include_once("api/".$request[1]."/method/".$request[3]."/".$request[4].".inc.php");
                        exit;

                    } else if (file_exists("html/".$request[0]."/".$request[1]."/".$request[2]."/".$request[3]."/page.".$request[4].".inc.php")) {

                        include_once("html/".$request[0]."/".$request[1]."/".$request[2]."/page.".$request[3]."/".$request[4].".inc.php");
                        exit;

                    } else {

                        include_once("html/error.inc.php");
                        exit;
                    }

                    break;
                }

                default: {

                    if (file_exists("html/".$request[0]."/".$request[1]."/".$request[2]."/".$request[3]."/page.".$request[4].".inc.php") ) {

                        include_once("html/".$request[0]."/".$request[1]."/".$request[2]."/".$request[3]."/page.".$request[4].".inc.php");
                        exit;

                    } else {

                        include_once("html/error.inc.php");
                        exit;
                    }

                    break;
                }
            }
        }

		default: {

			include_once("html/error.inc.php");
			exit;
		}
	}

} else {

	$request = array();
	include_once("html/main.inc.php");
	exit;
}
