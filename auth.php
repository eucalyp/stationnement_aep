<?php

error_reporting(E_ALL);

require_once("class.authentification.php");


$objAuth = authentification::instance();
$objLog  = log::instance();

$redirectPage = 'index.php';

if(isset($_GET['redirect'])) {
    $redirectPage = $_GET['redirect'];
}

if (isset($_POST['username']) && isset($_POST['password']))
{
    $login = 0;
    if ($objAuth->verification($_POST['username'],$_POST['password']))
    {
        $login = 1;
    }
	
	if($objAuth->isUserAdmin() && !isset($_GET['redirect']))
		$redirectPage = "gestion.php";
	
    header("Location:$redirectPage?login=$login");
}
?>

<form method="post">
Usager : <input type="text" name="user"><br>
Mot de passe : <input type="password" name="pass"><br>
<input type="submit" value="soumettre"><br>
</form>
