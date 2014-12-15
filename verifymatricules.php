<?php
error_reporting(E_ALL);
require_once('class.authentification.php');

$auth = authentification::instance();

if(!$auth->estIdentifie() || !$auth->isUserAdmin())
{
	header("Location: index.php");
	exit(0);		
}

require_once("header.php");
require_once("class.util.php");
require_once("class.database.php");
require_once("class.userData.php");

$matricules = array();

if (isset($_POST['matricules'])) {
	$list_raw = explode("\r\n", $_POST['matricules']);
	$database = database::instance();
	$result = $database->requete("SELECT matricule FROM st_authentication");	
	$matricules_db = array();
	while ($row = mysql_fetch_array($result, MYSQL_NUM) ) {
		array_push($matricules_db, $row);
	}
	echo "<h4>Liste des matricules invalides</h4>";
	echo "<ul>";
	foreach ($matricules_db as $matricule) {
		if ( !in_array($matricule[0], $list_raw) ) {
			$user = new userData($matricule[0]);
			echo '<li>'.$matricule[0].' | '.util::UTF8toISO8859($user->getFirstName()).' '.util::UTF8toISO8859($user->getLastName()).' | '.$user->getEmail().'</li>';
		}
	}	
	echo "</ul>";
}

?>
<div>
	<h4>Entrez les matricules a verifier dans le champ suivant</h4>
	<span>note: 1 matricule par ligne</span>
	<form id ="form-list" name="form1" method="post" action="?list=1">
		<textarea name="matricules" form="form-list" id="textarea-list" rows="20" cols="30"></textarea>
		<input type="submit" name="button" id="button-list" value="send"/>
	</form>
</div>

<?php
readfile('footer.html');
?>