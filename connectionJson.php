<?php
	session_start();
	echo json_encode($_SESSION["donnees"]); //à envoyer a l'appli SSI on a connection.json
?>