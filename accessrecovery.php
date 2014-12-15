<?php

error_reporting(E_ALL);

require_once("header.php");
require_once("class.util.php");
require_once("class.userData.php");
require_once("class.validation.php");
require_once("class.database.php");

$message = "";

if ( isset($_GET['sendmail']) && $_GET['sendmail']==1 ) {
	$matricule = $_POST['matricule'];
	if ( isset($matricule) && validation::matricule($matricule) ) {
		$user = new userData($_POST['matricule']);
		if ( $user->getExist() ) {
			$password = util::generatePassword();
			$pregArray = array(
                        array(
                            'key' => "/@@FIRSTNAME@@/",
                            'value' => $user->getFirstName()  
                        ),
                        array(
                            'key' => "/@@LASTNAME@@/",
                            'value' => $user->getLastName()
                        ),
                        array(
                        	'key' => "/@@PASSWORD@@/",
                        	'value' => $password
                        )
        	);
        	$database = database::instance();	
        	$database->requete("UPDATE st_authentication SET password='".md5($password)."' WHERE matricule='".$matricule."'");
          	util::sendEmail($user->getEmail(), 'accessrecovery.txt', $pregArray, "Stationnement AEP - Demande de nouveau mot de passe");
          	$message = util::UTF8toISO8859("Un courriel avec votre nouveau mot de passe vous a été envoyé");
		} else {
			$message = 'Utilisateur non existant';
		}
	} else {
		$message = 'Matricule invalide';
	}

} 
?>
<div>
	<?php 
		if ($message != "") {
			echo $message.'</br></br>';
		}
	?>
	Pour obtenir un nouveau mot de passe, entrez votre matricule et cliquez sur le bouton de validation. Vous receverez alors un nouveau mot de passe par courriel. 
	<div class="widget">
		<div class="widgetcontent">
			<form method="post" action="?sendmail=1" name="connexion">
				<div style="display:block">
					<div style="display:block">
						<label style="display:inline;vertical-align:middle">Courriel</label> 
						<input style="width:125px;float:right;vertical-align:middle"" type="text" name="email" id="email" />
					</div>
					<div style="display:block;clear:both">
						<label style="display:inline;vertical-align:middle">Matricule</label> 
						<input style="width:125px;float:right;vertical-align:middle" name="matricule" size="8" maxlength="7" id="matricule" />
					</div>
					<div style="display:block;margin-top:15px;clear:both">
						<input style="" type="submit" value="Envoyer mot de passe" class="searchButton"/>
					</div>     
				</div>
			</form>
		</div>
	</div>
	<p>Si vous rencontrez un probleme avec la recuperation de mot de passe, veuillez contacter l'administrateur: anthony.buffet@polymtl.ca</p>
</div>
<?php

