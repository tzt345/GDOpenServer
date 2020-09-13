<?php
function rate($gs, $uploadDate, $commentarray, $accountID, $levelID) {
    include dirname(__FILE__)."/../../lib/connection.php";
    $starStars = $commentarray[2];
    if($starStars == ""){
        $starStars = 0;
    }
    $starCoins = $commentarray[3];
    $starFeatured = $commentarray[4];
    $diffArray = $gs->getDiffFromName($commentarray[1]);
    $starDemon = $diffArray[1];
    $starAuto = $diffArray[2];
    $starDifficulty = $diffArray[0];
    $query = $db->prepare("UPDATE levels SET starStars=:starStars, starDifficulty=:starDifficulty, starDemon=:starDemon, starAuto=:starAuto WHERE levelID=:levelID");
    $query->execute([':starStars' => $starStars, ':starDifficulty' => $starDifficulty, ':starDemon' => $starDemon, ':starAuto' => $starAuto, ':levelID' => $levelID]);
    $query = $db->prepare("INSERT INTO modactions (type, value, value2, value3, timestamp, account) VALUES ('1', :value, :value2, :levelID, :timestamp, :id)");
    $query->execute([':value' => $commentarray[1], ':timestamp' => $uploadDate, ':id' => $accountID, ':value2' => $starStars, ':levelID' => $levelID]);
    if(isset($starFeatured) AND is_numeric($starFeatured)){
        if ($starFeatured >= 1) {
            $starFeatured = 1;
        } elseif ($starFeatured <= 0) {
            $starFeatured = 0;
        }
        if ($gs->checkPermission($accountID, "commandFeature")) {
            $query = $db->prepare("INSERT INTO modactions (type, value, value3, timestamp, account) VALUES ('2', :value, :levelID, :timestamp, :id)");
            $query->execute([':value' => $starFeatured, ':timestamp' => $uploadDate, ':id' => $accountID, ':levelID' => $levelID]);	
            $query = $db->prepare("UPDATE levels SET starFeatured=:starFeatured WHERE levelID=:levelID");
            $query->execute([':starFeatured' => $starFeatured, ':levelID' => $levelID]);
        }
    }
    if(isset($starCoins) AND is_numeric($starCoins)){
        if ($starCoins >= 1) {
            $starCoins = 1;
        } elseif ($starCoins <= 0) {
            $starCoins = 0;
        }
        if ($gs->checkPermission($accountID, "commandVerifycoins")) {
            $query = $db->prepare("INSERT INTO modactions (type, value, value3, timestamp, account) VALUES ('3', :value, :levelID, :timestamp, :id)");
            $query->execute([':value' => $starCoins, ':timestamp' => $uploadDate, ':id' => $accountID, ':levelID' => $levelID]);
            $query = $db->prepare("UPDATE levels SET starCoins=:starCoins WHERE levelID=:levelID");
            $query->execute([':starCoins' => $starCoins, ':levelID' => $levelID]);
        }
    }
    return true;
}
?>