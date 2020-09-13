<?php
// Here is all reupload related configs

$api_key = "dc467dd431fc48eb0244b0aead929ccd"; // The soundcloud developer api key to use to reupload songs
$reupUID = 0; // The UserID of the account.
$reupAID = 0; // The AccountID of the account. Also known as extID in the "users" table.
$isLevelReuploadLimitDaily = 0; // 0 for false and 1 for true
$level_reupload = 0; // any number for applying the limit, isLevelReuploadLimitDaily indicates that if this limit resets daily, 0 for unlimited and -1 to disable reuploading 
$song_reupload = 0; // just like level reuploading limit, but for songs
$can_reupload_from_direct_links = 1; // 0 to disable direct link song reuploading and 1 for enabling it
?>