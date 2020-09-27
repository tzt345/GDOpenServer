<?php
<<<<<<< Updated upstream
// Bot account
$botUID = 0; // The UserID of the account.
$botAID = 0; // The AccountID of the account. Also known as extID in the "users" table.
// Account settings
$unregisteredUploadLevels = 1; // Indicates if unregistered(green) users can upload levels.
$loginRateLimitCountToDisable = 5; // Indicates how many logins of one account are allowed for the set time below. Set to 0 to disable it.
$loginRateLimitDisableTime = 60; // Time in minutes; Indicates how long the user's account will be rate limited (which then prevents the user from logging in).
=======
// Bot Account //
$botAID = 0;
$botUID = 0;

// User Limits //

$unregisteredUploadLevels = 1; // Indicates if unregistered(green) users can upload levels or no
$loginRateLimitCountToDisable = 5; // Indicates how many logins should be done to disable user's account for the below variable's time; set to 0 to disable
$loginRateLimitDisableTime = 60; // Time in minutes; indicates how much time user's account will get disabled for rate limiting; set to 0 to disable
>>>>>>> Stashed changes
?>