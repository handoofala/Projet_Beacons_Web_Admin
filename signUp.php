<!DOCTYPE html>
<?php
	session_start();
	include("initPage.php");
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
				<form class="ConnectionForm" action="saveRegister.php" method="POST">
					<h4 class="fontSize14em">Sign up : complete all fields</h4>
					<input type="text" name="pseudo" autofocus placeholder="Pseudonyme" class="fontSize13em margin6"/>
					<input type="password" name="password" placeholder="Password" class="fontSize13em"/>
					<input type="mail" name="mail" placeholder="example@email.com" class="fontSize13em margin6"/>
					<input type="submit" value="Sign up!" class="fontSize13em"/>
				</form>
			</section>
			<footer>
				<?php include("footer.php");?>
			</footer>
		</center>
	</body>
</html>