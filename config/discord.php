<?php
// Replace the 0 with any webhook URL of your choice.
// Discord webhook example: "https://discordapp.com/api/webhooks/****/****"
$webhook_private = "0";
$webhook_pub = "0";

// PRIVATE LOGS FOR $webhook_private
// 0 = Disabled, 1 = Enabled
$logSessionLogins = 0; // Only if $sessionGrants is set to 1 in security.php
$logModActions = 1; // Logs ratings, uploads, command usage and much more.
$logRegisters = 1; // Logs when a new user is created.

// PUBLIC LOGS FOR $webhook_pub
$logLevelRates = 1; // Level rate and unrates.
$logLevelMisc = 0; // Whenever a level gets deleted, featured or epic rating.
$logModerator = 1; // Will show which moderator/user did something. Forced to be on for private webhook.

// ALL WEBHOOKS
/* 0 = Disabled,
1 = Enabled for Public only,
2 = Enabled for Private only,
3 = Enabled for both webhooks.
*/

$logRateReq = 3; // Logs when a level was sent by an user. Won't apply if the user has "commandRate" permission.
?>