<?php
	session_start();
	include("../initPage.php");
	$inputJSON = file_get_contents('php://input');
	$input= json_decode( $inputJSON, TRUE );
	
	function redirectionErreur401(){
		header('HTTP/1.0 409 Conflict : user is not currently in this room.');
		exit;
	}

	function leaveTchat($token){
		include("../initConnectionBDD.php");
		
        $req = $bdd->prepare("SELECT count(pseudo) AS nbUser, id AS id_user FROM users WHERE token = :token");
        $req->execute(array(':token' => strip_tags($token)));
        $donnees = $req->fetch();
        $req->closeCursor();
        
		if($donnees["nbUser"] == 1){
			$req = $bdd->prepare("DELETE FROM lien_rooms_users WHERE id_user = :id_user");
			$req->execute(array(':id_user' => strip_tags($donnees["id_user"])));
		}else{
			redirectionErreur401();
		}
	}

	if(isset($input["token"])){
		leaveTchat($input["token"]);
	}else{
		echo "Variable error : token does not exist";
	}
?>