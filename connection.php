<!DOCTYPE html>
<?php
	session_start();
	include("initPage.php");
	if(isset($_GET["json"]) AND $_GET["json"] == true){
		$_POST["pseudo"] = $_POST["A VOIR AVEC EUX"];
		$_POST["password"] = $_POST["A VOIR AUSSI AVEC EUX"];
	}
	
	if(isset($_POST["role"])){
		$req = $bdd->prepare("INSERT INTO users VALUES(
			'',
			:pseudo,
			:pswd,
			:role,
			:email,
			NOW(),
			''
			)
		");
		$req->execute(array(
			':pseudo' => strip_tags($_POST["pseudoUser"]),
			':pswd' => hash('sha256', $_POST["passwordUser"]),
			':role' => strip_tags($_POST["role"]),
			':email' => strip_tags($_POST["mailUser"])
		));
		$req->closeCursor();
		
		
		$req = $bdd->prepare("SELECT id_ecole FROM lien_users_ecoles WHERE id_user = (SELECT id FROM users WHERE pseudo = :pseudo)");
		$req->execute(array(
			':pseudo' => strip_tags($_SESSION["user"])
		));
		$donnees1 = $req->fetch();
		$req->closeCursor();
		
		$req = $bdd->prepare("SELECT id FROM users WHERE pseudo = :pseudoUser");
		$req->execute(array(
			':pseudoUser' => strip_tags($_POST["pseudoUser"])
		));
		$donnees2 = $req->fetch();
		$req->closeCursor();
		
		$req = $bdd->prepare("INSERT INTO lien_users_ecoles VALUES(
			:id_ecole,
			:id_user
			)
		");
		$req->execute(array(
			':id_ecole' => $donnees1["id_ecole"],
			':id_user' => $donnees2["id"]
		));
		$req->closeCursor();
	}
	
	if(isset($_POST["addRoom"])){
		$req = $bdd->query("SELECT id FROM beacons ORDER BY id DESC LIMIT 1");
		$donnees = $req->fetch();
		$lastId = $donnees["id"];
		$req->closeCursor();
		
		$req = $bdd->prepare("SELECT id_ecole FROM lien_users_ecoles WHERE id_user = (SELECT id FROM users WHERE pseudo = :pseudo)");
		$req->execute(array(
			':pseudo' => strip_tags($_SESSION["user"])
		));
		$donnees = $req->fetch();
		$id_ecole = $donnees["id_ecole"];
		$req->closeCursor();
		
		$req = $bdd->prepare("INSERT INTO beacons VALUES (
			'',
			:UUID
			)
		");
		$req->execute(array(':UUID' => strip_tags($_POST["beacon1"])));
		$req->execute(array(':UUID' => strip_tags($_POST["beacon2"])));
		$req->execute(array(':UUID' => strip_tags($_POST["beacon3"])));
		$req->execute(array(':UUID' => strip_tags($_POST["beacon4"])));
		$req->closeCursor();
		
		$req = $bdd->prepare("INSERT INTO rooms VALUES(
			'',
			:idM1,
			:idM2,
			:idM3,
			:idM4,
			:nameRoom,
			:passwordRoom
			)
		");
		$req->execute(array(
			':idM1' => $lastId+1,
			':idM2' => $lastId+2,
			':idM3' => $lastId+3,
			':idM4' => $lastId+4,
			':nameRoom' => strip_tags($_POST["roomName"]),
			':passwordRoom' => strip_tags(md5($_POST["passwordRoom"]))
		));
		$req->closeCursor();
		
		$req = $bdd->prepare("INSERT INTO lien_rooms_ecoles VALUES(
			:id_ecole,
			(SELECT id FROM rooms ORDER BY id DESC LIMIT 1)
			)
		");
		$req->execute(array(':id_ecole' => $id_ecole ));
		$req->closeCursor();
	}
	
	if(isset($_POST["password"])){
		$req = $bdd->prepare("SELECT pswd, isAdmin FROM users WHERE pseudo = :pseudo");
		$req->execute(array(':pseudo' => strip_tags($_POST["pseudo"])));
		$donnees = $req->fetch();
		$req->closeCursor();
		
		if(hash('sha256', $_POST["password"]) == $donnees["pswd"] AND $donnees["isAdmin"] == 2){
			$_SESSION["connected"] = true;
			$_SESSION["user"] = $_POST["pseudo"];
		}else{
			$_SESSION["connected"] = false;
			header('Location: index.php');
		}
		
	}else if(isset($_SESSION["user"]) AND isset($_SESSION["connected"]) AND ($_SESSION["connected"] == true) AND ($_SESSION["user"] != NULL OR $_SESSION["user"] != "")){
		$_SESSION["connected"] = false;
		$req = $bdd->prepare("SELECT count(pseudo) AS nbUser, isAdmin  FROM users WHERE pseudo = :pseudo");
		$req->execute(array(':pseudo' => strip_tags($_SESSION["user"])));
		$donnees = $req->fetch();
		$req->closeCursor();
		if($donnees["nbUser"] == 1 AND $donnees["isAdmin"] == 2){
			$_SESSION["connected"] = true;
		}
	}else{
		$_SESSION["connected"] = false;
		header('Location: index.php');
	}
	
	if(isset($_GET["id"])){
		$req = $bdd->prepare("SELECT id FROM users WHERE pseudo = :pseudo");
		$req->execute(array(':pseudo' => strip_tags($_SESSION["user"])));
		$donnees = $req->fetch();
		$req->closeCursor();
		
		/*we check if the link exist*/
		$req = $bdd->prepare("SELECT count(id_ecole) AS nbLinkEcole, count(id_user) AS nbLinkUser FROM lien_users_ecoles WHERE id_user = :id_user AND id_ecole = :id_ecole");
		$req->execute(array(
			':id_user' => strip_tags($donnees["id"]),
			':id_ecole' => strip_tags($_GET["id"])
		));
		$donnees2 = $req->fetch();
		$req->closeCursor();
		
		if($donnees2["nbLinkEcole"] == 1 AND $donnees2["nbLinkUser"] == 1){
			$req = $bdd->prepare("DELETE FROM lien_users_ecoles WHERE id_ecole = :id_ecole AND id_user = :id_user");
			$req->execute(array(
				':id_ecole' => strip_tags($_GET["id"]),
				':id_user' => strip_tags($donnees["id"])
			));
			$req->closeCursor();
		}
	}
	
	if(isset($_POST["addExistingSchool"])){
		$req = $bdd->prepare("SELECT id FROM users WHERE pseudo = :pseudo");
		$req->execute(array(':pseudo' => strip_tags($_SESSION["user"])));
		$idUser = $req->fetch();
		$req->closeCursor();
		
		/*we check if the link does not exist*/
		$req = $bdd->prepare("SELECT count(id_ecole) AS nbLinkEcole, count(id_user) AS nbLinkUser FROM lien_users_ecoles WHERE id_user = :id_user AND id_ecole = :id_ecole");
		$req->execute(array(
			':id_user' => strip_tags($idUser["id"]),
			':id_ecole' => strip_tags($_POST["schools"])
		));
		$donnees2 = $req->fetch();
		$req->closeCursor();
		
		if($donnees2["nbLinkEcole"] == 0 AND $donnees2["nbLinkUser"] == 0){
			$req = $bdd->prepare("INSERT INTO lien_users_ecoles VALUES(
				:idEcole,
				:idUser
				)
			");
			$req->execute(array(
				':idEcole' => strip_tags($_POST["schools"]),
				':idUser' => strip_tags($idUser["id"])
			));
			$req->closeCursor();
		}
	}
	
	if(isset($_POST["addSchool"])){
		/*We first check if the school already exist*/
		$req = $bdd->prepare("SELECT count(nomEcole) AS nbEcole, count(ville) AS nbVille FROM ecoles WHERE nomEcole = :ecole AND ville = :ville");
		$req->execute(array(
			':ecole' => strip_tags($_POST["ecole"]),
			':ville' => strip_tags($_POST["ville"])
		));
		$donnees = $req->fetch();
		$req->closeCursor();
		
		if($donnees["nbEcole"] == 0 AND $donnees["nbVille"] == 0){
			$req = $bdd->query("SELECT id+1 AS id FROM ecoles ORDER BY id DESC LIMIT 1");
			$donnees= $req->fetch();
			$req->closeCursor();
			
			if($donnees["id"] < 1 OR $donnees["id"] == NULL){
				$donnees["id"] = 0;
			}
			
			$req = $bdd->prepare("INSERT INTO ecoles VALUES(
				:id,
				:ecole,
				:ville
				)
			");
			$req->execute(array(
				':id' => $donnees["id"],
				':ecole' => strip_tags($_POST["ecole"]),
				':ville' => strip_tags($_POST["ville"])
			));
			$req->closeCursor();
			
			$req = $bdd->prepare("SELECT id FROM users WHERE pseudo = :pseudo");
			$req->execute(array(':pseudo' => strip_tags($_SESSION["user"])));
			$idUser = $req->fetch();
			$req->closeCursor();
			
			$req = $bdd->prepare("SELECT id FROM ecoles WHERE nomEcole = :ecole AND ville = :ville");
			$req->execute(array(
				':ecole' => strip_tags($_POST["ecole"]),
				':ville' => strip_tags($_POST["ville"])
			));
			$idEcole = $req->fetch();
			$req->closeCursor();
			
			/*we check if the link does not exist*/
			$req = $bdd->prepare("SELECT count(id_ecole) AS nbLinkEcole, count(id_user) AS nbLinkUser FROM lien_users_ecoles WHERE id_user = :id_user AND id_ecole = :id_ecole");
			$req->execute(array(
				':id_user' => strip_tags($donnees["id"]),
				':id_ecole' => strip_tags($idEcole["id"])
			));
			$donnees2 = $req->fetch();
			$req->closeCursor();
			
			if($donnees2["nbLinkEcole"] == 0 AND $donnees2["nbLinkUser"] == 0){				
				$req = $bdd->prepare("INSERT INTO lien_users_ecoles VALUES(
					:idEcole,
					:idUser
					)
				");
				$req->execute(array(
					':idEcole' => $idEcole["id"],
					':idUser' => $idUser["id"]
				));
				$req->closeCursor();
			}
		}
	}
?>
<html>
	<head>
		<?php include("head.php");
		if(isset($_SESSION["connected"]) AND ($_SESSION["connected"] == false)){
			?><meta http-equiv="refresh" content="5; URL=http://git.ethandev.fr/"><?php
		}?>
		<title>Projet Beacons</title>
	</head>
	<body>
		<center>
			<header>
				<?php include("nav.php");?>
				<h1>Welcome, <?php echo $_SESSION["user"];?></h1>
			</header>
			<section class="fontSize11em">
				<?php if(isset($_SESSION["user"]) AND isset($_SESSION["connected"]) AND ($_SESSION["connected"] == true)) {
					?>
					<article class="formSchools">
						<?php 
						$req = $bdd->prepare("SELECT count(id_ecole) AS nbId_ecole FROM lien_users_ecoles WHERE id_user = (SELECT id FROM users WHERE pseudo = :pseudo)");
						$req->execute(array(
							':pseudo' => strip_tags($_SESSION["user"])
						));
						$donnees = $req->fetch();
						$req->closeCursor();
						if($donnees["nbId_ecole"] == 0){?>
							<p>Add a school and link it to you:</p>
							<form method="post" action="connection.php">
								<input type="text" name="ecole" placeholder="Name of your school" class="fontSize11em DoubleLine"/>
								<input type="text" name="ville" placeholder="City of your school" class="fontSize11em DoubleLine"/>
								<input type="submit" name="addSchool" value="Add this school" class="fontSize11em DoubleLine"/>
							</form>
							<p>Link an existing school to you:</p>
							<form method="post" action="connection.php">
								<select name="schools" class="DoubleLine fontSize11em">
									<?php						
										$req = $bdd->query("SELECT * FROM ecoles");	
										while($donnees = $req->fetch()){
											?><option value="<?php echo $donnees["id"]; ?>"><?php echo $donnees["nomEcole"] . " - " . $donnees["ville"]; ?></option><?php
										}
										$req->closeCursor();
									?>
								</select>
								<input type="submit" name="addExistingSchool" value="Add this school" class="DoubleLine fontSize11em"/>
							</form>
						<?php }?>
						<p>Your school:</p>
						<table>
							<tr>
								<td class="tableTD">Ecoles</td>
								<td class="tableTD">Villes</td>
							</tr>
							<?php
								$req = $bdd->prepare("SELECT DISTINCT(id_ecole) AS id_ecole FROM lien_users_ecoles WHERE id_user = (SELECT id FROM users WHERE pseudo = :pseudo)");
								$req->execute(array(
									':pseudo' => strip_tags($_SESSION["user"])
									
								));
								$dataToSend = array();
								$i=0;
								while($donnees = $req->fetch()){
									$reqTmp = $bdd->prepare("SELECT * FROM ecoles WHERE id = :id");
									$reqTmp->execute(array(':id' => strip_tags($donnees["id_ecole"])));
									//echo $reqTmp;
									$donneesTmp = $reqTmp->fetch();
									//echo $donneesTmp;
									
									$reqTmp->closeCursor();
									
									if(isset($_GET["json"]) AND $_GET["json"] == true){
										$dataToSend[$i] = $donneesTmp;
										$_SESSION["donnees"] = $dataToSend;
									}else{
									?>
									<tr>
										
										<td class="tableTD"><?php echo $donneesTmp["nomEcole"];?></td>
										<td class="tableTD"><?php echo $donneesTmp["ville"];?></td>
										<!--<td><a href="connection.php?id=<?php echo $donneesTmp["id"];?>"><img src="img/deleteButton.png" alt="Delete"/></a></td>-->
									</tr>
									<?php
									}
									$i=$i+1;
								}
								$req->closeCursor();
								if(isset($_GET["json"]) AND $_GET["json"] == true){
									header('Location: connectionJson.php');
									exit();
								}
							?>
						</table>
						<?php 
						$req = $bdd->prepare("SELECT count(id_ecole) AS nbId_ecole FROM lien_users_ecoles WHERE id_user = (SELECT id FROM users WHERE pseudo = :pseudo)");
						$req->execute(array(
							':pseudo' => strip_tags($_SESSION["user"])
						));
						$donnees = $req->fetch();
						$req->closeCursor();
						if($donnees["nbId_ecole"] == 1){?>
							<p>Create your room and link it to your school :</p>
							<form method="post" action="connection.php">
								<input type="text" name="roomName" placeholder="Name of your room" class="fontSize11em DoubleLine"/>
								<input type="text" name="beacon1" placeholder="Beacon 1" class="fontSize11em DoubleLine"/>
								<input type="text" name="beacon2" placeholder="Beacon 2" class="fontSize11em DoubleLine"/>
								<input type="text" name="beacon3" placeholder="Beacon 3" class="fontSize11em DoubleLine"/>
								<input type="text" name="beacon4" placeholder="Beacon 4" class="fontSize11em DoubleLine"/>
								<input type="password" name="passwordRoom" placeholder="Room's password" class="fontSize11em DoubleLine"/>
								<input type="submit" name="addRoom" value="Add this room" class="fontSize11em DoubleLine"/>
							</form>
							<p>Your rooms:</p>
							<table>
								<tr>
									<td class="tableTD">Name</td>
									<td class="tableTD">Beacon 1</td>
									<td class="tableTD">Beacon 2</td>
									<td class="tableTD">Beacon 3</td>
									<td class="tableTD">Beacon 4</td>
								</tr>
								<?php
									$req = $bdd->prepare("SELECT rooms.name AS name, beacons.UUID as UUID FROM rooms
										INNER JOIN beacons
										ON beacons.id = rooms.beacon_id_1
											OR beacons.id = rooms.beacon_id_2
											OR beacons.id = rooms.beacon_id_3
											OR beacons.id = rooms.beacon_id_4
										WHERE rooms.id IN (SELECT id_room FROM lien_rooms_ecoles WHERE id_ecole = (SELECT id_ecole FROM lien_users_ecoles WHERE id_user = (SELECT id FROM users WHERE pseudo = :pseudo)))
									");
									$req->execute(array(
										':pseudo' => strip_tags($_SESSION["user"])
									));
									while($donnees = $req->fetch()){
									?>
									<tr>
										<td class="tableTD"><?php echo $donnees["name"];?></td>
										<td class="tableTD"><?php echo $donnees["UUID"];?></td>
										<?php $donnees = $req->fetch(); ?>
										<td class="tableTD"><?php echo $donnees["UUID"];?></td>
										<?php $donnees = $req->fetch(); ?>
										<td class="tableTD"><?php echo $donnees["UUID"];?></td>
										<?php $donnees = $req->fetch(); ?>
										<td class="tableTD"><?php echo $donnees["UUID"];?></td>
									</tr>
									<?php
									}
									$req->closeCursor();
								?>
							</table>
							<form method="post" action="connection.php">
								<p>Add an account :</p>
								<input type="text" name="pseudoUser" placeholder="Pseudonyme" class="fontSize13em margin6"/>
								<input type="password" name="passwordUser" placeholder="Password" class="fontSize13em"/>
								<input type="mail" name="mailUser" placeholder="example@email.com" class="fontSize13em margin6"/>
								<select name="role" class="DoubleLine fontSize11em">
									<option value="0">Etudiant</option>
									<option value="1">Professeur</option>
								</select>
								<input type="submit" value="Enregistrer" class="fontSize13em"/>
							</form>
						<?php }?>
					</article>
					<?php
				}else{
					?><p>Error : try to sign in again</p><?php
				}?>
			</section>
			<footer>
				<?php include("footer.php");?>
			</footer>
		</center>
	</body>
</html>