<?php

    /*!
     * hindbyte.com
     *
     * https://hindbyte.com
     * hindbyte@gmail.com
     *
     * Copyright 2012-2022 Demyanchuk Dmitry (hindbyte@gmail.com)
     */

$C = array();
$B = array();

$B['APP_DEMO'] = true;                                      //true = enable demo version mode

$B['APP_MESSAGES_COUNTERS'] = true;                         //true = show new messages counters
$B['APP_MYSQLI_EXTENSION'] = true;                          //if on the server is not installed mysqli extension, set to false
$B['FACEBOOK_AUTHORIZATION'] = false;                        //false = Do not show buttons Login/Signup with Facebook | true = allow display buttons Login/Signup with Facebook

$C['COMPANY_URL'] = "https://hindbyte.com/";

$B['APP_PATH'] = "app";
$B['APP_VERSION'] = "1";
$B['APP_NAME'] = "Dating App";
$B['APP_TITLE'] = "Dating App";
$B['APP_VENDOR'] = "dating.hindbyte.com";
$B['APP_YEAR'] = "2021";
$B['APP_AUTHOR'] = "Sohrab Ahmed";
$B['APP_SUPPORT_EMAIL'] = "hindbyte@gmail.com";

$B['APP_HOST'] = "dating.hindbyte.com";                 //edit to your domain, example (WARNING - without http:// and www): yourdomain.com
$B['APP_URL'] = "https://dating.hindbyte.com";           //edit to your domain url, example (WARNING - with http:// or https://): http://yourdomain.com

$B['TEMP_PATH'] = "assets/tmp/";                                //don`t edit this option
$B['PHOTO_PATH'] = "assets/photo/";                             //don`t edit this option
$B['CHAT_IMAGE_PATH'] = "assets/chat_images/";                  //don`t edit this option
$B['GIFTS_PATH'] = "assets/gifts/";                             //don`t edit this option
$B['MY_PHOTOS_PATH'] = "assets/gallery/";                             //don`t edit this option
$B['STICKERS_PATH'] = "assets/stickers/";                       //don`t edit this option

$B['GOOGLE_PLAY_LINK'] = "https://play.google.com/store/apps/details?id=com.hindbyte.dating";

$B['CLIENT_ID'] = 1;                               //Client ID | Integer | For identify the application | Example: 12567
$B['CLIENT_SECRET'] = "e862f4egfad1d65ef08f1e491690be56b24";        //Client Secret | String | Text value for identify the application | Example: wFt4*KBoNN_kjSdG13m1k3k=

// Stripe settings | Settings from Cross-Platform Mobile Payments | See documentation

$B['STRIPE_PUBLISHABLE_KEY'] = "pk_test_Fv4E55L3N8dp36NcpyxhGYzW6r";
$B['STRIPE_SECRET_KEY'] = "sk_test_hLn3Pfu0vdl7M5p45ZInquCtvbK";

// Google OAuth client |

$B['GOOGLE_CLIENT_ID'] = "332858474720-jpfrmd5olhinc2oam42b659l9c95e4jq.apps.googleusercontent.com";
$B['GOOGLE_CLIENT_SECRET'] = "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";

// Push notifications settings | For sending FCM (Firebase Cloud Messages) | https://hindbyte.com/help/how_to_create_fcm_android/

$B['GOOGLE_API_KEY'] = "AAAATX_pQOA:APA91bHB_NwcoKxBfOkdWN_H8C6Q9t6FaKr3n6dBU-HF9obr5V0AkImC3PHLEzSRwsPBKwV9t-yOfkRkmmH4tfgpKeJ_tFqQMrRXN4i0YGgg9ckBNc3C6COtuEk4vE_-G8YBXDX9IsdN";
$B['GOOGLE_SENDER_ID'] = "332858474720";

$B['FIREBASE_API_KEY'] = $B['GOOGLE_API_KEY'];
$B['FIREBASE_SENDER_ID'] = $B['GOOGLE_SENDER_ID'];

// Firebase project id need for OTP verification

$B['FIREBASE_PROJECT_ID'] = "dating-287c7";

// Facebook settings | For login/signup with facebook | https://hindbyte.com/help/how_to_create_facebook_application_and_get_app_id_and_app_secret/

$B['FACEBOOK_APP_ID'] = "3453453453445345";
$B['FACEBOOK_APP_SECRET'] = "e961f4egfad1d65ef08f1e491690be56b24";

// Recaptcha settings | Create you keys for reCAPTCHA v3 | https://www.google.com/recaptcha/admin/create

$B['RECAPTCHA_SITE_KEY'] = "6LeDMrsZ4AAAAABl-hJMuzsj2ogn3swLgflro0hmHSD";
$B['RECAPTCHA_SECRET_KEY'] = "6LeDMrsZAAgAAAKYGhjyXvK3qCqtIE5lMik6lENx9";

// SMTP Settings | For password recovery

$B['SMTP_HOST'] = 'chat.hindbyte.com';                         //SMTP host
$B['SMTP_AUTH'] = true;                                     //SMTP auth (Enable SMTP authentication)
$B['SMTP_SECURE'] = 'tls';                                  //SMTP secure (Enable TLS encryption, `ssl` also accepted)
$B['SMTP_PORT'] = 587;                                      //SMTP port (TCP port to connect to)
$B['SMTP_EMAIL'] = 'support@hindbyte.com';                     //SMTP email
$B['SMTP_USERNAME'] = 'support@hindbyte.com';                  //SMTP username
$B['SMTP_PASSWORD'] = 'password';                      //SMTP password

//Please edit database data

$C['DB_HOST'] = "localhost";                                //localhost or your db host
$C['DB_USER'] = "hindb69n_sohrab";                             //your db user
$C['DB_PASS'] = "D2EeNv@6n!S5QY";                         //your db password
$C['DB_NAME'] = "hindb69n_dating";                             //your db name


$C['DEFAULT_BALANCE'] = 10;                                    // Default user balance in level (Is charged during the user registration)

// Errors
$C['ERROR_SUCCESS'] = 0;

$C['ERROR_CODE_INITIATE'] = 100;
$C['ERROR_ACCESS_TOKEN'] = 101;
$C['ERROR_UPLOAD_NO_FILE'] = 102;
$C['ERROR_UPLOAD_FILE_SIZE'] = 103;
$C['ERROR_FILE_FORMAT'] = 104;
$C['ERROR_INCORRECT_USERNAME'] = 105;
$C['ERROR_EMPTY_FULL_NAME'] = 106;
$C['ERROR_INCORRECT_PASSWORD'] = 107;
$C['ERROR_INCORRECT_EMAIL'] = 108;

$C['ERROR_CLIENT_ID'] = 19100;
$C['ERROR_CLIENT_SECRET'] = 19101;

$C['ERROR_FILE_SIZE_BIG'] = 501;
$C['ERROR_FILE_SIZE_SMALL'] = 502;

$C['ERROR_IMAGE_FILE_FORMAT'] = 503;
$C['ERROR_IMAGE_FILE_WIDTH_HEIGHT'] = 504;

$C['ERROR_OTP_VERIFICATION'] = 506;
$C['ERROR_OTP_PHONE_NUMBER_TAKEN'] = 507;

$C['ERROR_LOGIN_TAKEN'] = 300;
$C['ERROR_EMAIL_TAKEN'] = 301;
$C['ERROR_FACEBOOK_ID_TAKEN'] = 302;

$C['ERROR_ACCOUNT_ID'] = 400;



$C['DISABLE_LIKES_GCM'] = 0;
$C['ENABLE_LIKES_GCM'] = 1;

$C['DISABLE_COMMENTS_GCM'] = 0;
$C['ENABLE_COMMENTS_GCM'] = 1;

$C['DISABLE_FOLLOWERS_GCM'] = 0;
$C['ENABLE_FOLLOWERS_GCM'] = 1;

$C['DISABLE_MESSAGES_GCM'] = 0;
$C['ENABLE_MESSAGES_GCM'] = 1;

$C['DISABLE_GIFTS_GCM'] = 0;
$C['ENABLE_GIFTS_GCM'] = 1;

$C['SEX_MALE'] = 0;
$C['SEX_FEMALE'] = 1;
$C['SEX_ANY'] = 2;

$C['USER_CREATED_SUCCESSFULLY'] = 0;
$C['USER_CREATE_FAILED'] = 1;
$C['USER_ALREADY_EXISTED'] = 2;
$C['USER_BLOCKED'] = 3;
$C['USER_NOT_FOUND'] = 4;
$C['USER_LOGIN_SUCCESSFULLY'] = 5;
$C['EMPTY_DATA'] = 6;
$C['ERROR_API_KEY'] = 7;

$C['NOTIFY_TYPE_LIKE'] = 0;
$C['NOTIFY_TYPE_FOLLOWER'] = 1;
$C['NOTIFY_TYPE_MESSAGE'] = 2;
$C['NOTIFY_TYPE_COMMENT'] = 3;
$C['NOTIFY_TYPE_COMMENT_REPLY'] = 4;
$C['NOTIFY_TYPE_FRIEND_REQUEST_ACCEPTED'] = 5;
$C['NOTIFY_TYPE_GIFT'] = 6;

$C['NOTIFY_TYPE_IMAGE_COMMENT'] = 7;
$C['NOTIFY_TYPE_IMAGE_COMMENT_REPLY'] = 8;
$C['NOTIFY_TYPE_IMAGE_LIKE'] = 9;

$C['GCM_NOTIFY_CONFIG'] = 0;
$C['GCM_NOTIFY_SYSTEM'] = 1;
$C['GCM_NOTIFY_CUSTOM'] = 2;
$C['GCM_NOTIFY_LIKE'] = 3;
$C['GCM_NOTIFY_ANSWER'] = 4;
$C['GCM_NOTIFY_QUESTION'] = 5;
$C['GCM_NOTIFY_COMMENT'] = 6;
$C['GCM_NOTIFY_FOLLOWER'] = 7;
$C['GCM_NOTIFY_PERSONAL'] = 8;
$C['GCM_NOTIFY_MESSAGE'] = 9;
$C['GCM_NOTIFY_COMMENT_REPLY'] = 10;
$C['GCM_FRIEND_REQUEST_INBOX'] = 11;
$C['GCM_FRIEND_REQUEST_ACCEPTED'] = 12;
$C['GCM_NOTIFY_GIFT'] = 14;
$C['GCM_NOTIFY_SEEN'] = 15;
$C['GCM_NOTIFY_TYPING'] = 16;
$C['GCM_NOTIFY_URL'] = 17;

$C['GCM_NOTIFY_IMAGE_COMMENT_REPLY'] = 18;
$C['GCM_NOTIFY_IMAGE_COMMENT'] = 19;
$C['GCM_NOTIFY_IMAGE_LIKE'] = 20;

$C['ACCOUNT_STATE_ENABLED'] = 0;
$C['ACCOUNT_STATE_DISABLED'] = 1;
$C['ACCOUNT_STATE_BLOCKED'] = 2;
$C['ACCOUNT_STATE_DEACTIVATED'] = 3;

$C['ACCOUNT_TYPE_USER'] = 0;
$C['ACCOUNT_TYPE_GROUP'] = 1;

$C['GALLERY_ITEM_TYPE_IMAGE'] = 0;

$C['ADMIN_ACCESS_LEVEL_FULL'] = 0;
$C['ADMIN_ACCESS_LEVEL_MODERATOR'] = 1;
$C['ADMIN_ACCESS_LEVEL_GUEST'] = 2;

$LANGS = array();
$LANGS['English'] = "en";






$B['GOOGLE_AUTHORIZATION'] = true; //false = Do not show buttons Login/Signup with Google | true = allow display buttons Login/Signup with Google

$C['IMAGE_FILE_MAX_SIZE'] = 5242880;  //Max size for image file in bytes | For example 5mb = 5*1024*1024

$C['ADMIN_ACCESS_LEVEL_ALL_RIGHTS'] = 0;
$C['ADMIN_ACCESS_LEVEL_READ_WRITE_RIGHTS'] = 1;
$C['ADMIN_ACCESS_LEVEL_MODERATOR_RIGHTS'] = 2;
$C['ADMIN_ACCESS_LEVEL_READ_ONLY_RIGHTS'] = 3;

$C['API_VERSION'] = "v2";

$C['APP_TYPE_ALL'] = -1;
$C['APP_TYPE_MANAGER'] = 0;
$C['APP_TYPE_WEB'] = 1;
$C['APP_TYPE_ANDROID'] = 2;
$C['APP_TYPE_IOS'] = 3;

$C['GCM_NOTIFY_MATCH'] = 25;

$C['GCM_NOTIFY_TYPING_START'] = 27;
$C['GCM_NOTIFY_TYPING_END'] = 28;

$C['GCM_NOTIFY_MEDIA_APPROVE'] = 1001;
$C['GCM_NOTIFY_MEDIA_REJECT'] = 1002;
$C['GCM_NOTIFY_PROFILE_PHOTO_APPROVE'] = 1003;
$C['GCM_NOTIFY_PROFILE_PHOTO_REJECT'] = 1004;
$C['GCM_NOTIFY_ACCOUNT_APPROVE'] = 1005;
$C['GCM_NOTIFY_ACCOUNT_REJECT'] = 1006;

$C['GCM_NOTIFY_CHANGE_ACCOUNT_SETTINGS'] = 30;

$C['GCM_NOTIFY_PROFILE_NEW_PROFILE_PHOTO_UPLOADED'] = 2001;
$C['GCM_NOTIFY_PROFILE_NEW_MEDIA_ITEM_UPLOADED'] = 2003;

$C['NOTIFY_TYPE_MEDIA_APPROVE'] = 10;
$C['NOTIFY_TYPE_MEDIA_REJECT'] = 11;
$C['NOTIFY_TYPE_PROFILE_PHOTO_APPROVE'] = 12;
$C['NOTIFY_TYPE_PROFILE_PHOTO_REJECT'] = 13;
$C['NOTIFY_TYPE_ACCOUNT_APPROVE'] = 14;
$C['NOTIFY_TYPE_ACCOUNT_REJECT'] = 15;

$C['IMAGE_TYPE_PROFILE_PHOTO'] = 0;

$C['REPORT_TYPE_ITEM'] = 0;
$C['REPORT_TYPE_PROFILE'] = 1;
$C['REPORT_TYPE_MESSAGE'] = 2;
$C['REPORT_TYPE_COMMENT'] = 3;
$C['REPORT_TYPE_GALLERY_ITEM'] = 4;
$C['REPORT_TYPE_MARKET_ITEM'] = 5;
$C['REPORT_TYPE_COMMUNITY'] = 6;

$C['ITEM_TYPE_IMAGE'] = 0;
$C['ITEM_TYPE_POST'] = 2;
$C['ITEM_TYPE_COMMENT'] = 3;
$C['ITEM_TYPE_GALLERY'] = 4;

// Payments

// PA - PAYMENT ACTION
$C['PA_BUY_LEVEL'] = 0;
$C['PA_BUY_GIFT'] = 1;
$C['PA_BUY_VERIFIED_BADGE'] = 2;
$C['PA_BUY_REGISTRATION_BONUS'] = 5;
$C['PA_BUY_MANUAL_BONUS'] = 7;
$C['PA_BUY_PRO_MODE'] = 8;
$C['PA_BUY_MESSAGE_PACKAGE'] = 10;
$C['PA_SEND_TRANSFER'] = 11;
$C['PA_RECEIVE_TRANSFER'] = 12;

// PT - PAYMENT TYPE
$C['PT_UNKNOWN'] = 0;
$C['PT_LEVEL'] = 1;
$C['PT_CARD'] = 2;
$C['PT_GOOGLE_PURCHASE'] = 3;
$C['PT_APPLE_PURCHASE'] = 4;
$C['PT_BONUS'] = 6;

$C['CURRENCY_USD'] = 0;
$C['CURRENCY_EUR'] = 1;


// Signin methods

$C['SIGNIN_EMAIL'] = 0;
$C['SIGNIN_OTP'] = 1;
$C['SIGNIN_FACEBOOK'] = 2;
$C['SIGNIN_GOOGLE'] = 3;
$C['SIGNIN_APPLE'] = 4;
$C['SIGNIN_TWITTER'] = 5;
$C['OAUTH_TYPE_GOOGLE'] = 1;