<?php
	session_start();
	header('Content-Type: application/json');
	$inputJSON = file_get_contents('php://input');
	$input= json_decode( $inputJSON, TRUE );
	
	function redirectionErreur401(){
		header('HTTP/1.0 401 Unauthorized : password / login incorrect');
		exit;
	}

	function getToken($login, $pswd){
		include("../initConnectionBDD.php");
		
        $req = $bdd->prepare("SELECT * FROM users WHERE pseudo = :pseudo");
        $req->execute(array(':pseudo' => strip_tags($login)));
        $donnees = $req->fetch();
        $req->closeCursor();
        
        if(hash('sha256', $pswd) == $donnees["pswd"]){
			$token = md5(uniqid(mt_rand(), true));
			$req = $bdd->prepare("UPDATE users SET token = :token WHERE pseudo = :pseudo");
			$req->execute(array(
				':token' => $token,
				':pseudo' => strip_tags($login)
			));
			$req->closeCursor();
			$dataToSend["token"] = $token;
			echo json_encode($dataToSend);
        }else{
			redirectionErreur401();
		}
	}

	
	if(isset($input["login"]) AND isset($input["password"])){
		getToken($input["login"], $input["password"]);
	}else{
		echo "Variables error : login and password do not exist";
	}
?>