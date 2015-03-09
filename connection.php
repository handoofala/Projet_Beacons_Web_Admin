<!DOCTYPE html>
<?php
	session_start();
	include("initPage.php");
	if(isset($_POST["password"])){
		$req = $bdd->prepare("SELECT pswd FROM users WHERE pseudo = :pseudo");
		$req->execute(array(':pseudo' => strip_tags($_POST["pseudo"])));
		$donnees = $req->fetch();
		$req->closeCursor();
		if(hash('sha256', $_POST["password"]) == $donnees["pswd"]){
			$_SESSION["connected"] = true;
			$_SESSION["user"] = $_POST["pseudo"];
		}
	}else if(isset($_SESSION["user"]) AND isset($_SESSION["connected"]) AND ($_SESSION["connected"] == true) AND ($_SESSION["user"] != NULL OR $_SESSION["user"] != "")){
		$_SESSION["connected"] = false;
		$req = $bdd->prepare("SELECT count(pseudo) AS nbUser FROM users WHERE pseudo = :pseudo");
		$req->execute(array(':pseudo' => strip_tags($_SESSION["user"])));
		$donnees = $req->fetch();
		$req->closeCursor();
		if($donnees["nbUser"] == 1){
			$_SESSION["connected"] = true;
		}
	}else{
		$_SESSION["connected"] = false;
	}
?>
<html>
	<head>
		<?php include("head.php");
		if(isset($_SESSION["connected"]) AND ($_SESSION["connected"] == false)){
			?><meta http-equiv="refresh" content="5; URL=http://127.0.0.1/ProjetBeacon"><?php
		}?>
		<title>Projet Beacons</title>
	</head>
	<body>
		<center>
			<header>
				<?php include("nav.php");?>
			</header>
			<section>
				<?php if(isset($_SESSION["user"]) AND isset($_SESSION["connected"]) AND ($_SESSION["connected"] == true)) {
					?><p>Connected</p><?php
				}else{
					?><p>Error : try to sign in again</p><?php
				}?>
			</section>
			<footer>
				<?php include("footer.php");?>
			</footer>
		</center>
	</body>
</html>