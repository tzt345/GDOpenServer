<?php
$query = $db->prepare("SELECT starStars, starFeatured, starEpic, starMagic, isCPShared FROM levels WHERE levelID=:levelID");
$query->execute([':levelID' => $levelID]);
$result = $query->fetch();
$deservedcp = 0;
if($result["starStars"] != 0){
    $deservedcp += $rateCP;
}
if($result["starFeatured"] != 0){
    $deservedcp += $featureCP;
}
if($result["starEpic"] != 0){
    $deservedcp += $epicCP;
}
if($isMagicSectionManual == 1 AND $result["starMagic"] != 0){
    $deservedcp += $magicCP;
}
$query3 = $db->prepare("SELECT userID FROM cpshares WHERE levelID = :levelID");
$query3->execute([':levelID' => $levelID]);
$shares = $query->fetchAll();
if ($CPSharedWhole == 1) {
	$addCP = $deservedcp;
} else {
	$sharecount = $query3->rowCount() + 1;
	$addCP = round($deservedcp / $sharecount);
}
if ($result["isCPShared"] == 1) {
    $CPShare = round($addCP);
    foreach($shares as &$share){
        $query4 = $db->prepare("UPDATE users SET creatorPoints = creatorPoints - :CPShare WHERE userID = :userID");
        $query4->execute([':userID' => $share["userID"], ':CPShare' => $CPShare]);
    }
    $query = $db->prepare("DELETE FROM cpshares WHERE levelID = :levelID");
    $query->execute([':levelID' => $levelID]);
} else {
    exit("temp_0_Error: The level does not share creator points with anyone.");
}
exit("temp_0_The level is no longer sharing awarded creator points with other users.");
?>