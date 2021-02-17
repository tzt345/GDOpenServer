<?php
$serverVersion = 0; // 0 = All version, 1 until 7 = 1.0 until 1.6, 10 = 1.7, 18 until 21 = 1.8 until 2.1; The server's version, used as a metadata and a limitation if $onlyVersionEqualAndBelowAllowed is set to 1.
$onlyVersionEqualAndBelowAllowed = 0; // Makes any version above $serverVersion unable to make any requests to this server.
$timezone = ""; // 
$weekStartingDay = 1; // 1 = Monday, 2 = Tuesday, 3 = Wednesday, 4 = Thursday, 5 = Friday, 6 = Saturday, 7 = Sunday; Indicates the first day in a server's week.
$userToAccountLinkingLevel = 0; // The level used for linking users to website-made accounts, adviced to use for a GDPS below version 1.8; Wayveyx's idea for 1.6 GDPS.
?>