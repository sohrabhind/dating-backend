<?php

$page_id = "privacy";

$css_files = array("my.css");
$page_title = $LANG['delete-profile'] . " | " . APP_TITLE;

include_once("html/common/site_header.inc.php");
$error = "By deleting your account, you will lose access to Poppi and all your profile information, messages, and activity will be permanently deleted from our systems.";
// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $email = $_POST["email"];
    $password = $_POST["password"];
    // Verify if email and password are provided
    if (empty($email) || empty($password)) {
        $error = "Please provide both email and password.";
    } else {
        $account = new account($dbo);
        $result = $account->signin($email, $password);
        if (!$result['error']) {
            $accountId = $result['accountId'];
            $account = new account($dbo, $accountId);
            $result = $account->deactivation($currentPassword);
            $error = "Your account has been deleted successfully.";
        } else {
            $error = "Please provide correct email or password.";
        }
    }
}
?>

<body class="about-page sn-hide">

    <?php
    include_once("html/common/site_topbar.inc.php");
    ?>

    <div class="wrap content-page">
        <div class="main-column">
            <div class="main-content">
                <div class="wrap content-page">
                    <div class="main-column">
                        <div class="main-content" style="margin-bottom: 20px; margin-top: 15px">
                            <h2>Delete Account</h2>
                            <p class="error"><?=$error?></p>
                            <form method="post">
                                <label for="email">Email:</label>
                                <input type="email" name="email" id="email" required><br><br>
                                <label for="password">Password:</label>
                                <input type="password" name="password" id="password" required><br><br>
                                <input type="submit" style="background:red;" value="Delete Account">
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php
    include_once("html/common/site_footer.inc.php");
    ?>


</body </html>