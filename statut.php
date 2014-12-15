<?php
error_reporting(E_ALL);

require_once("class.demande.php");
require_once("class.log.php");

$demande = new demande();

readfile('header.php');

if (@isset($_POST['soumission']) && $_POST['soumission'] == '1')
{
    if ($_POST['id'] < 1 || !$demande->ouvrir($_POST['id']))
    {
        print "<font color=\"#ff0000\"><b>Demande introuvable!</b></font>\n<br>";
    } else
    {
        print "Statut de la demande numéro <b>".$_POST['id']."</b> : ".getStatusLecture($demande->getStatus()).'<br>';
    }
}

?>
<br>
Veuillez indiquer le numéro de votre demande pour vérifier son statut.  Si vous ne connaissez pas le numéro de votre demande, veuillez en avisez <a href="mailto:stationnement@aep.polymtl.ca">stationnement@aep.polymtl.ca</a>.
<br><br>
<form method="POST">
<input type="text" name="id"><br>
<input type="submit" value="Consulter le statut">
<input type="hidden" name="soumission" value="1">
</form>
<?php

readfile('footer.html');

function getStatusLecture($valeur)
{
    if ($valeur == DEMANDE_STATUS_ATTENTE)
    {
        return "Demande reçue, en attente...  Pièces justificatives <font color=\"#ff0000\"><b>NON-REÇUES</b></font>.";
    }
    if ($valeur == DEMANDE_STATUS_REFUSE)
    {
        return "Demande <font color=\"#ff0000\"><b>refusée</b></font>!";
    }
    if ($valeur == DEMANDE_STATUS_ACCEPTE)
    {
        return "Demande <font color=\"#00cc00\"><b>acceptée</b></font>!";
    }
    if ($valeur == DEMANDE_STATUS_PAYE)
    {
        return "Demande <font color=\"#00cc00\"><b>acceptée</b></font> et payée!";
    }
    if ($valeur == DEMANDE_STATUS_PREUVEOK)
    {
        return "Demande reçue, en attente...  Pièces justificatives <font color=\"#00cc00\"><b>REÇUES</b></font>.";
    }
    if ($valeur == DEMANDE_STATUS_ANNULE)
    {
        return "Demande <font color=\"#ff0000\"><b>annulée</b></font>.";
    }
    if ($valeur == DEMANDE_STATUS_IMPRIME)
    {
        return "Demande <font color=\"#00cc00\"><b>acceptée</b></font> et transférée au SDI (C-317.3).";
    }
    return "Statut inconnu.";
}

?>
