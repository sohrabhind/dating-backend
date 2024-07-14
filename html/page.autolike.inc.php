<?php
/*
$profile = new profile($dbo, 0);
$profile->setRequestFrom(0);
$toUserIds = $profile->getLikeToProfiles();
$fromUserIds = $profile->getLikeFromProfiles();

$messageTexts = array (
    "Hi",
    "Hello",
    "hi",
    "hello"
);

foreach ($toUserIds as $toUserId) {
    $fromUserId = $fromUserIds[array_rand($fromUserIds)];
    $profile = new profile($dbo, $toUserId);
    $profile->setRequestFrom($fromUserId);
    $profile->like($fromUserId, false);

    $messages = new messages($dbo);
    $messages->setRequestFrom($fromUserId);
    $result = $messages->create($toUserId, 0, $messageTexts[array_rand($messageTexts)], "", 0);
}
*/

?>

<html>
    <title>Auto Like/Message</title>
</html>
