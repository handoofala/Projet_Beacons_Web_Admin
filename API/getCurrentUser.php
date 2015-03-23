<?php
	session_start();
	header('Content-Type: application/json');
	$inputJSON = file_get_contents('php://input');
	$input= json_decode( $inputJSON, TRUE );
	
	function redirectionErreur401(){
		header('HTTP/1.0 401 Unauthorized : wrong token / token unknown');
		exit;
	}

	function getCurrentUser($token){
		include("../initConnectionBDD.php");
		
        $req = $bdd->prepare("SELECT * FROM users WHERE token = :token");
        $req->execute(array(':token' => strip_tags($token)));
        $donnees = $req->fetch();
        $req->closeCursor();
        
		if($donnees["pseudo"] != "" AND $donnees["pseudo"] != NULL){
			$dataToSend["pseudo"] = $donnees["pseudo"];
			$dataToSend["role"] = $donnees["isAdmin"];
			echo json_encode($dataToSend);
		}else{
			redirectionErreur401();
		}
	}

	if(isset($input["token"])){
		getCurrentUser($input["token"]);
	}else{
		echo "Variables error : token does not exist";
	}
?>