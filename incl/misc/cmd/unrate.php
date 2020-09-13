<?php
function unrate($commentarray, $uploadDate, $accountID, $levelID) {
    include dirname(__FILE__)."/../../lib/connection.php";
    $query = $db->prepare("UPDATE levels SET starStars='0', starDifficulty='0', starDemon='0', starAuto='0' WHERE levelID=:levelID");
    $query->execute();
    $query = $db->prepare("INSERT INTO modactions (type, value, value2, value3, timestamp, account) VALUES ('1', 'na', '0', :levelID, :timestamp, :id)");
    $query->execute([':timestamp' => $uploadDate, ':id' => $accountID, ':levelID' => $levelID]);
    return true;
}
?>