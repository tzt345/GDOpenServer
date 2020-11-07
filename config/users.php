<?php
// Bot account
$botUID = 0; // The UserID of the account.
$botAID = 0; // The AccountID of the account. Also known as extID in the "users" table.
// Account settings
$unregisteredUploadLevels = 1; // Indicates if unregistered (green) users can upload levels.
$loginRateLimitCountToDisable = 5; // Indicates how many logins of one account are allowed for the set time below. Set to 0 to disable it.
$loginRateLimitDisableTime = 60; // Time in minutes; Indicates how long the user's account will be rate limited (which then prevents the user from logging in).
$nonModsCanSuggest = 1; // Indicates if players can bypass the 'actionSuggestRating' permission, allowing them to suggest the rating of a level.
?>