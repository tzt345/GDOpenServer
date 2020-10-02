<?php
include dirname(__FILE__) . "/../../lib/connection.php";
if (isset($commentarray[1])) {
    $diffArray = $gs->getDiffFromName($commentarray[1]);
} else {
    exit("temp_0_Error: No input given.");
}
$starDifficulty = $diffArray[0];
$starDemon = $diffArray[1];
$starAuto = $diffArray[2];
if (isset($commentarray[2]) AND is_numeric($commentarray[2])) {
    $starStars = $commentarray[2];
} else {
    $starStars = 0;
}
if (isset($commentarray[3]) AND is_numeric($commentarray[3])) {
    $starFeatured = $commentarray[3];
    if ($starFeatured >= 1) {
        $starFeatured = 1;
    } else {
        $starFeatured = 0;
    }
} else {
    $starFeatured = 0;
}
if (isset($commentarray[4]) AND is_numeric($commentarray[4])) {
    $starCoins = $commentarray[4];
    if ($starCoins >= 1) {
        $starCoins = 1;
    } else {
        $starCoins = 0;
    }
} else {
    $starCoins = 0;
}
<<<<<<< HEAD
//$response = ucfirst($starDifficulty)." "; -- debugging maybe??
if ($starStars != 0) {
    $response .= "$commentarray[1] with $starStars stars";
} else {
	$response .= "$commentarray[1] with no stars";
=======
$response = ucfirst($starDifficulty);
if ($starStars != 0) {
    $response .= " ".$starStars."*";
>>>>>>> 2e74b5a9203ff4b134d945d996fa4e3fc2fcb77f
}
$query = $db->prepare("UPDATE levels SET starStars=:starStars, starDifficulty=:starDifficulty, starDemon=:starDemon, starAuto=:starAuto WHERE levelID=:levelID");
$query->execute([':starStars' => $starStars, ':starDifficulty' => $starDifficulty, ':starDemon' => $starDemon, ':starAuto' => $starAuto, ':levelID' => $levelID]);
$query = $db->prepare("INSERT INTO modactions (type, value, value2, value3, timestamp, account) VALUES (1, :value, :value2, :levelID, :timestamp, :id)");
$query->execute([':value' => ucfirst($commentarray[1]), ':timestamp' => $uploadDate, ':id' => $accountID, ':value2' => $starStars, ':levelID' => $levelID]);
if ($starFeatured == 1) {
    if ($gs->checkPermission($accountID, "commandFeature")) {
        $query = $db->prepare("INSERT INTO modactions (type, value, value3, timestamp, account) VALUES (2, 1, :levelID, :timestamp, :id)");
        $query->execute([':timestamp' => $uploadDate, ':id' => $accountID, ':levelID' => $levelID]);
        $query = $db->prepare("UPDATE levels SET starFeatured=1 WHERE levelID=:levelID");
        $query->execute([':levelID' => $levelID]);
        $response .= " and featured it";
    }
}
if ($starCoins == 1) {
    if ($gs->checkPermission($accountID, "commandVerifycoins")) {
        $query = $db->prepare("INSERT INTO modactions (type, value, value3, timestamp, account) VALUES (3, 1, :levelID, :timestamp, :id)");
        $query->execute([':timestamp' => $uploadDate, ':id' => $accountID, ':levelID' => $levelID]);
        $query = $db->prepare("UPDATE levels SET starCoins=1 WHERE levelID=:levelID");
        $query->execute([':levelID' => $levelID]);
<<<<<<< HEAD
        $response .= "and has verified coins";
=======
        $response .= " and verified it's coins";
>>>>>>> 2e74b5a9203ff4b134d945d996fa4e3fc2fcb77f
    }
}
exit("temp_0_Level successfully rated to $response.");
?>