<?php

error_reporting(E_ALL);

require_once("class.authentification.php");
require_once('class.config.php');
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
$ids = array();
$ids = $_POST['id'];
$objDemande = new demande();
foreach ($ids as $id)
{
    $changementStatus = false;
    $objDemande->ouvrir($id);
    $objDemande->setStatus(getParam('form_status'));
    
    if (getParam('form_status') != $objDemande->getStatus())
    {
        $changementStatus = true;
    }
    if (!$objDemande->setStatus(getParam('form_status')))
    {
        $changementStatus = false;
    }
    
    
    if ($objDemande->sauvegarde())
    {
        $statutEnMot = printLecture('statusLong',$objDemande->getStatus());
        $email = file_get_contents('emailStatut.txt');
        $email = preg_replace("/@@PRENOM@@/",$objDemande->getPrenom(),$email);
        $email = preg_replace("/@@NOM@@/",$objDemande->getNom(),$email);
        $email = preg_replace("/@@ID@@/",$objDemande->getID(),$email);
        $email = preg_replace("/@@STATUT@@/",$statutEnMot,$email);
        $headers = 'From: Stationnement AEP <stationnement@step.polymtl.ca>' . "\r\n" .
                   'X-Mailer: pHP/' . phpversion();
        if (config::SendEmail) mail($objDemande->getEmail(),"Demande de stationnement (".$objDemande->getId().") : changement de statut",$email, $headers);
            
        printn($id . " => Le statut est maintenant : ".printLecture('status',$objDemande->getStatus())."<br>");
    }
    else
    {
        printn($id . " => Erreur!  Impossible de changer le statut!");
    }
}


/*
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
*/

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

// Imprime le bon texte en fonction de la valeu et du type
function printLecture($type, $valeur)
{
    if ($type == "groupe")
    {
        if ($valeur == DEMANDE_GROUPE_AEP)
        {
            return "AEP";
        }
    }
    elseif ($type == "status")
    {
        if ($valeur == DEMANDE_STATUS_ATTENTE)
        {
            return "Attente";
        }
        if ($valeur == DEMANDE_STATUS_REFUSE)
        {
            return "Refus";
        }
        if ($valeur == DEMANDE_STATUS_ACCEPTE)
        {
            return "Accepté";
        }
        if ($valeur == DEMANDE_STATUS_PAYE)
        {
            return "Payé";
        }
        if ($valeur == DEMANDE_STATUS_PREUVEOK)
        {
            return "PreuvesOK";
        }
        if ($valeur == DEMANDE_STATUS_ANNULE)
        {
            return "Annulé";
        }
        if ($valeur == DEMANDE_STATUS_IMPRIME)
        {
            return "Imprimé";
        }
    }
    elseif ($type == "statusLong")
    {
        if ($valeur == DEMANDE_STATUS_ATTENTE)
        {
            return "Demande reçue, en attente...  Pièces justificatives NON-REÇUES";
        }
        if ($valeur == DEMANDE_STATUS_REFUSE)
        {
            return "Demande refusée";
        }
        if ($valeur == DEMANDE_STATUS_ACCEPTE)
        {
            return "Demande acceptée";
        }
        if ($valeur == DEMANDE_STATUS_PAYE)
        {
            return "Demande payée";
        }
        if ($valeur == DEMANDE_STATUS_PREUVEOK)
        {
            return "Demande reçue, en attente...  Pièces justificatives REÇUES";
        }
        if ($valeur == DEMANDE_STATUS_ANNULE)
        {
            return "Demande annulée";
        }
        if ($valeur == DEMANDE_STATUS_IMPRIME)
        {
            return "Demande acceptée et transférée au SDI";
        }
    }
}

?>
