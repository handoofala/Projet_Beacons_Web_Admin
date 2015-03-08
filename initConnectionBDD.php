<?php
	include("config.php");
	try{
		$pdo_option[PDO::ATTR_ERRMODE] = PDO::ERRMODE_EXCEPTION;
		$bdd = new PDO('mysql:host='.$hostname.';dbname='.$dattabase,$loggin,$password,$pdo_option);
	}
	catch(Exception $e){
		die('Erreur : '.$e->getMessage());
	}
?>