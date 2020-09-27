<?php
$cloudSaveEncryption = 0; // 0 = Password will be replaced in the save files, 1 = Save files will be encrypted (which is password dependant).
$sessionGrants = 1; // 0 = GJP check is done every time; 1 = GJP check is done once per hour; drastically improves performance, slightly descreases security.
$sessionGrantsTime = 60; // Time in minutes, for how much time until a GJP check session expires for an IP, set to 0 or lower to permanently grant access to an IP.
$IPChecking = 1; // 0 = Doesn't check for IP addresses; 1 = Checks IP addresses when needed. You should NOT turn this off, unless getIP() results into 127.0.0.1.
$onlyWebRegistration = 0; // Only allows website GDPS account registration through tools/account/registerAccount.php; Won't apply the website account verification due to being all on website
$accountVerification = 0; // 0 = No verification required, 1 = Verifying your account with /tools/account/verifyAccount.php, 2 = Verifying your account using a link sent to your E-Mail.
?>