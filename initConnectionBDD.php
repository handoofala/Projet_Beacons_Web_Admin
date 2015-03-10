<?php
	include("config.php");
	try{
		$pdo_option[PDO::ATTR_ERRMODE] = PDO::ERRMODE_EXCEPTION;
		$bdd = new PDO('mysql:host='.$hostname.';dbname='.$dattabase,$loggin,$password,$pdo_option);
		$mysqli = new mysqli($hostname, $loggin, $password, $dattabase);
		if ($mysqli->connect_errno) {
			printf("Échec de la connexion : %s\n", $mysqli->connect_error);
			exit();
		}
	}
	catch(Exception $e){
		die('Erreur : '.$e->getMessage());
	}
?>