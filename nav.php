<nav>
	<li>
		<ul><a href="index.php" class="black titleSize"><h1>Projet Beacons</h1></a></ul>
		<?php
			if(isset($_SESSION["connected"]) AND $_SESSION["connected"] == true){
				?><a href="index.php?logOut=true">Log out</a><?php
			}
		?>
	</li>
</nav>