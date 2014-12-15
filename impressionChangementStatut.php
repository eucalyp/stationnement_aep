<?php

error_reporting(E_ALL);

require_once("class.authentification.php");
require_once("class.demandeListe.php");
require_once("class.demande.php");
require_once("class.log.php");
require_once("class.validation.php");

$objAuth = authentification::instance();
#$objLog  = log::instance();
#$objvalid= validation::instance();

if (getParam('logout') == '1')
{
    session_destroy();
    header("Location: auth.php");
    exit();
}

if (!$objAuth->estIdentifie())
{
    header("Location: auth.php");
    exit();
}

$objDemande = new demande();
$objDemande->ouvrir(getParam('id'));
$objDemande->setStatus(DEMANDE_STATUS_IMPRIME);

if ($objDemande->sauvegarde())
{
    printn("Le statut est maintenant : imprimé.<br><br>Vous pouvez fermer la fenetre.");
}
else
{
    printn("Erreur!  Impossible de changer le statut!");
}

exit(0);


function getParam($param)
{
    if (isset($_POST[$param]))
    {
        return $_POST[$param];
    }
    if (isset($_GET[$param])) 
    {
        return $_GET[$param];
    }
    return null;
}

function printn ($txt) { print $txt."\n"; }


?>
