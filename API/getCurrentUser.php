<?php
	session_start();
	include("../initPage.php");
	header('Content-Type: application/json');
	
	function redirectionErreur401(){
		header('HTTP/1.0 401 Unauthorized : user unknown');
		exit;
	}

	function getCurrentUser($token){
        $req = $bdd->prepare("SELECT * FROM users WHERE pseudo = :pseudo");
        $req->execute(array(':pseudo' => strip_tags($token)));
        $donnees = $req->fetch();
        $req->closeCursor();
        
		if($donnees["pseudo"] != "" AND $donnees["pseudo"] != NULL){
			$pseudo = $donnees["pseudo"];
			$role = $donnees["isAdmin"];
			$dataToSend[1] = $pseudo;
			$dataToSend[2] = $role;
			echo json_encode($dataToSend);
		}else{
			redirectionErreur401();
		}
	}

if(isset($_POST["data"])){
	$jsonData = json_decode($_POST["data"]);
	getCurrentUser(jsonData.get("token"));
}else{
	echo "Variable error : $_POST[\"data\"] does not exist";
}
?>