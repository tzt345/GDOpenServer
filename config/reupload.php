<?php
// REUPLOAD LIMITATION //

// LEVELS
$isLevelReuploadLimitDaily = 0; // 1 will reset the limit daily, 0 to let all users have a FIXED reupload limit
$level_reupload = 0; // any number for applying the limit, isLevelReuploadLimitDaily indicates that if this limit resets daily, 0 for unlimited and -1 to disable reuploading 
// SONGS
$api_key = "dc467dd431fc48eb0244b0aead929ccd"; // The soundcloud developer api key to use to reupload songs, better don't change this unless you can provide your own key
$isSongReuploadLimitDaily = 0; // similar to the $isLevelReuploadLimitDaily setting, for songs
$song_reupload = 0; // similar to the $level_reupload setting, for songs
$canReuploadFromDirectLinks = 1; // 0 to disable direct links, 1 to keep it enabled. It's good to disable them to avoid possible copyright issues.

?>
