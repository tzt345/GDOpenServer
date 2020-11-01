<?php
session_start();
//error_reporting(0);
include dirname(__FILE__)."/../../incl/lib/connection.php";
require_once dirname(__FILE__)."/../incl/dashboardLib.php";
$dl = new dashboardLib();
require_once dirname(__FILE__)."/../../incl/lib/mainLib.php";
$gs = new mainLib();
if(isset($_POST["url"])){
	$songID = $gs->songReupload($_SESSION["accountID"], $_POST["url"]);
	if($songID < 0){
		$errorDesc = $dl->getLocalizedString("songAddError$songID");
		$dl->printBox('<h1>'.$dl->getLocalizedString("songAdd")."</h1>
						<p>".$dl->getLocalizedString("errorGeneric")." $songID ($errorDesc)</p>
						<a class='btn btn-primary btn-block' href='".$_SERVER["REQUEST_URI"]."'>".$dl->getLocalizedString("tryAgainBTN")."</a>","reupload");
	}else{
		$dl->printBox("<h1>".$dl->getLocalizedString("songAdd")."</h1>
						<p>Song Reuploaded: $songID</p>
						<a class='btn btn-primary btn-block' href='".$_SERVER["REQUEST_URI"]."'>".$dl->getLocalizedString("songAddAnotherBTN")."</a>","reupload");
	}
}else{
	$dl->printBox('<h1>'.$dl->getLocalizedString("songAdd").'</h1>
				<form action="" method="post">
					<div class="form-group">
						<label for="urlField">'.$dl->getLocalizedString("songAddUrlFieldLabel").'</label>
						<input type="text" class="form-control" id="urlField" name="url" placeholder="'.$dl->getLocalizedString("songAddUrlFieldPlaceholder").'">
					</div>
					<button type="submit" class="btn btn-primary btn-block">'.$dl->getLocalizedString("reuploadBTN").'</button>
				</form>',"reupload");
}
?>