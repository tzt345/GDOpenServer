<?php
// Whenever a level gets rated, any below will give the amount of CP to the creator.
// NOTE: This WILL stack. Which in the end means (rate + feature + epic) = total CP reward.
$starCP = 1; 
$featureCP = 1;
$epicCP = 2; 
$magicCP = 2; // Counts if magic section is manual.

$epicInHall = 1; // 1 = Epic levels can be seen in Hall of Fame, 0 = Only levels with !hall command applied will go to Hall of Fame.
// !hall command is disabled by default if $epicInHall is set to 1.
// Note: Manually sending levels to Hall of Fame will not grant any extra creator points. 

$isMagicSectionManual = 0; // Enables magic commands and gives creator points for levels that go into that section.
$CPSharedWhole = 0; // Indicates if shared level creator points are added equally to each creator, or split between the creators; 0 for no and 1 for yes.
$dailyWeeklyCPShared = 1; // Indicates if daily/weekly creator point reward will be shared with people the creator points are shared with; 0 for no and 1 for yes.

$maximumAccountDownloads = 2; // Limits how many downloads an account can add to a level, meaning if the account downloads the level many times to increase downloads, it will only add downloads until this limit has not been applied yet to the account.
$uploadRateLimit = 60; // Time in seconds; indicates how much time an account or a user depending on the restrictions should wait until they can upload a level again.
$showCreatorBannedPeoplesLevels = 1; // Indicates if level searching shows creator banned people's levels.
?>
