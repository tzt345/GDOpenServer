<?php
// REUPLOAD LIMITATION //

// Levels
$isLevelReuploadLimitDaily = 0; // 1 will reset the limit daily, 0 to let all users have a FIXED reupload limit.
$levelReupload = 0; // Any number for applying the limit, isLevelReuploadLimitDaily indicates that if this limit resets daily, 0 for unlimited and -1 to disable reuploading.
$useProxy = 0; // Don't use this yet lol
// Songs
$soundcloudAPIKey = "dc467dd431fc48eb0244b0aead929ccd"; // The SoundCloud developer API key to use for reuploading songs from it, best to not change this unless you can provide your own key.
$isSongReuploadLimitDaily = 0; // Similar to the $isLevelReuploadLimitDaily setting, but for songs.
$songReupload = 0; // Similar to the $levelReupload setting, but for songs.
$canReuploadFromDirectLinks = 1; // 0 to disable direct links, 1 to keep it enabled. It's good to disable them to avoid possible copyright issues.
$useProxy = 0; // Don't use this yet lol
?>