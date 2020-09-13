<?php
$cloudSaveEncryption = 0; // 0 = password string replacement, 1 = cloud save encryption (password dependant)
$sessionGrants = 1; // 0 = GJP check is done every time; 1 = GJP check is done once per hour; drastically improves performance, slightly descreases security
$IPchecking = 1; // 0 = Doesn't check for IP addresses; 1 = Checks IP addresses when needed. You should NOT turn this off, unless getIP() results into 127.0.0.1.
?>
