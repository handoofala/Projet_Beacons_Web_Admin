<?php
	session_start();
	include("../initPage.php");
	header('Content-Type: application/json');
	
	function redirectionErreur401(){
		header('HTTP/1.0 401 Unauthorized : password / login incorrect');
		exit;
	}

	function getToken($login, $password){
        $req = $bdd->prepare("SELECT * FROM users WHERE pseudo = :pseudo");
        $req->execute(array(':pseudo' => strip_tags($login)));
        $donnees = $req->fetch();
        $req->closeCursor();
        
        if(hash('sha256', $_POST["password"]) == $donnees["pswd"]){
			$token = $donnees["pseudo"];
			$dataToSend[1] = $token;
			echo json_encode($dataToSend);
        }
        redirectionErreur401();
	}

if(isset($_POST["data"])){
	$jsonData = json_decode($_POST["data"]);
	getToken(jsonData.get("login"), jsonData.get("password"));
}else{
	echo "Variable error : $_POST[\"data\"] does not exist";
}
?>