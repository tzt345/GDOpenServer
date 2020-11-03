<font face="verdana"><?php
include "../incl/lib/connection.php";
require "../incl/lib/exploitPatch.php";
require "../incl/lib/generatePass.php";
$ep = new exploitPatch();
$generatePass = new generatePass();

if (!empty($_POST["accountid"]) AND !empty($_POST["p"]) AND !empty($_POST["us"])){
    $accounti = $ep->remove($_POST["accountid"]);
    $psw = $ep->remove($_POST["p"]);
    $us = $ep->remove($_POST["us"]);
    $pass = $generatePass->isValidUsrname($us, $psw);

    $q1 = $db->prepare("SELECT `accountID` FROM `accounts` WHERE `userName` = :us");
    $q1->execute([':us' => $us]);           
    $username = $q1->fetch()[0];
    
    $q = $db->prepare("SELECT roleID FROM roleassign WHERE accountID = :ai");
    $q->execute([':ai' => $username]);
    $result = $q->fetch()[0];

    if ($pass) {
        if ($result >= "4") {
            $getUserIDQuery = $db->prepare("SELECT userID FROM users WHERE extID = :accountid");
            $getUserIDQuery->execute([':accountid' => $accounti]);
            $getUserIPQuery = $db->prepare("SELECT IP FROM users WHERE extID = :accountid");
            $getUserIPQuery->execute([':accountid' => $accounti]);
            $userIP = $getUserIPQuery->fetch()[0];
            $userID = $getUserIDQuery->fetch()[0];
            $query = $db->prepare(
                "DELETE FROM accounts WHERE accountID = :accountid;
                DELETE FROM users WHERE extID = :accountid;
                DELETE FROM links WHERE accountID = :accountid"
                );
                $query->execute([':accountid' => $accounti]);
            if ($_POST['ipban'] == true) {
                $query = $db->prepare("INSERT INTO bannedips (IP) VALUES (:ip)");
                $query->execute([":ip" => $userIP]);
            }
            if ($_POST["purge"] == false) {
                $query = $db->prepare("DELETE FROM acccomments WHERE userID = :userid;
                    DELETE FROM blocks WHERE person1 = :accountid OR person2 = :accountid;
                    DELETE FROM comments WHERE userID = :userid;
                    DELETE FROM friendreqs WHERE accountID = :accountid OR toAccountID = :accountid;
                    DELETE FROM friendships WHERE person1 = :accountid OR person2 = :accountid;
                    DELETE FROM levelscores WHERE accountID = :accountid;             
                    DELETE FROM messages WHERE accID = :accountid");
                $query->execute([":accountid" => $accounti, ":userid" => $userID]);
            }
            if ($_POST["levels"] == true) {
                $deleteLevelsQuery = $db->prepare("DELETE FROM levels WHERE extID = :accountid");
                $deleteLevelsQuery->execute([":accountid" => $accounti]);
            }
            echo "Account deleted!";
        } else {
            echo "Not an administrator.";
        }
    } else {
        echo "Incorrect password.";
    }
} else {
    echo '<form action="deleteAccount.php" method="post">
    Account ID: <input type="text" name="accountid"><br>
    Username: <input type="text" name="us"><br>
    Password: <input type="password" name="p"><br>
    <input type="checkbox" name="ipban" value="true"> IP ban<br>
    <input type="checkbox" name="purge" value="true"> Don\'t Purge (delete comments, messages, etc.)<br>
    <input type="checkbox" name="levels" value="true"> Delete levels (potentially very destructive!)<br>
    <input type="submit" value="Delete Account">
    </form>';
}

?>