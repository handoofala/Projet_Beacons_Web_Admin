<!DOCTYPE html>
<?php
	session_start();
	include("initPage.php");
	if(isset($_POST["pseudo"]) AND isset($_POST["password"]) AND isset($_POST["mail"])){
		$dateTimeNow = date("Y-m-d H:i:s"); //Will be useful if we would like to salt the password later
		/*we check if mail or pseudo already exist*/
		$req = $bdd->prepare("SELECT count(pseudo) AS nbUsers FROM users WHERE pseudo = :pseudo OR email = :mail");
		$req->execute(array(
			':pseudo' => strip_tags($_POST["pseudo"]),
			':mail' => strip_tags($_POST["mail"])
		));
		$donnees = $req->fetch();
		$req->closeCursor();
		if($donnees["nbUsers"] == 0){
			/*then we save the new user*/
			$req = $bdd->prepare("INSERT INTO users VALUES(
				'',
				:pseudo,
				:pswd,
				'1',
				:email,
				:dateCrea
				)
			");
			$req->execute(array(
				':pseudo' => strip_tags($_POST["pseudo"]),
				':pswd' => strip_tags(hash('sha256', $_POST["password"])),
				':email' => strip_tags($_POST["mail"]),
				':dateCrea' => $dateTimeNow
			));
			$req->closeCursor();
			$_SESSION["connected"] = true;
			$_SESSION["user"] = strip_tags($_POST["pseudo"]);
		}else{
			$error = true;
		}
	}
?>
<html>
	<head>
		<?php include("head.php");?>
		<title>Projet Beacons</title>
	</head>
	<body>
		<center>
			<header>
				<?php include("nav.php");?>
			</header>
			<section>
				<?php if(isset($error) AND $error == true){
					?><p>This user or email already exist. Try to register again with another pseudo or email</p><?php
				}?>
			</section>
			<footer>
				<?php include("footer.php");?>
			</footer>
		</center>
	</body>
</html>