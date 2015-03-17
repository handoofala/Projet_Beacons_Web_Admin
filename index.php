<!DOCTYPE html>
<?php
	session_start();
	include("initPage.php");
	if(isset($_GET["logOut"]) AND $_GET["logOut"] == true){
		$_SESSION["connected"] = false;
		$_SESSION["user"] = "";
		if (ini_get("session.use_cookies")) {
			$params = session_get_cookie_params();
			setcookie(session_name(), '', time() - 42000,
				$params["path"], $params["domain"],
				$params["secure"], $params["httponly"]
			);
		}
		session_destroy();
	}
?>
<html>
	<head>
		<?php 
			include("head.php");
			if(isset($_SESSION["user"]) AND isset($_SESSION["connected"]) AND ($_SESSION["connected"] == true)){
				?><meta http-equiv="refresh" content="1; URL=http://git.ethandev.fr/connection.php"><?php
			}
		?>
		<title>Projet Beacons</title>
	</head>
	<body class="textAlignCenter">
			<header>
				<?php include("nav.php");?>
			</header>
			<section>
				<form class="ConnectionForm" action="connection.php" method="POST">
					<h4 class="fontSize18em">Sign in :</h4>
					<input type="text" name="pseudo" autofocus placeholder="Pseudonyme" class="fontSize13em margin6"/>
					<input type="password" name="password" placeholder="Password" class="fontSize13em"/>
					<input type="submit" value="Sign in" class="fontSize13em margin6"/>
					<p>Not registered yet ? <a href="signUp.php">Sign up for free</a></p>
				</form>
			</section>
			<footer>
				<?php include("footer.php");?>
			</footer>
	</body>
</html>