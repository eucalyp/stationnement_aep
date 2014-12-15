<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"       "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
 <html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr"    dir="ltr">
 <head>
     <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
 <meta name="KEYWORDS" content="Stationnement" />
     <meta name="robots" content="index,follow" />
     <link rel="shortcut icon" href="http://www.aep.polymtl.ca/        favicon.ico" />
<title>Stationnement - AEP</title>
</head>
<!--<body onload="javascript:window.print()">-->
<?php
    
    error_reporting(E_ALL);

    require_once('class.authentification.php');
    require_once('class.util.php');
    require_once('class.demande.php');
    require_once('class.database.php');
    
    $auth = authentification::instance();
    $matricule = util::getParam($_GET, 'matricule');
    $changeStatus = util::getParam($_GET, 'changeStatus');

    if(!$auth->isUserAdmin() || empty($matricule)) {
        require_once("header.php");
        print(util::cleanUTF8("Problème d'identification ou de matricule"));
        exit(0);
    }
    
    $demande = new demande($matricule);
    $user = new userData($matricule);
    $db = database::instance();
    if($changeStatus == "1") {
        $printedStatus = demandStatus::PRINTED_STATUS;
        $result = $db->requete("SELECT description FROM st_status WHERE st_status.statusId=$printedStatus");
        $resultArray = mysql_fetch_array($result);
        $demande->getStatus()->setToPrinted(util::cleanUTF8($resultArray[demandStatus::STATUS_DESC_DB_FIELD]));      
        print('<body>');
    } else {
        print('<body onload="javascript:window.print()">');
    }

?>
<img src="AEP.gif"/>
<h2>Demande de stationnement</h2>
<p>
<fieldset>
<legend><h3>Informations personnelles</h3></legend>
<div style="display:block;margin-top:10px">
    <table>
    <?php
         printTableRow("Matricule", $user->getMatricule());
         printTableRow("Nom", $user->getLastName());
         printTableRow("Prenom", $user->getFirstName());
         printTableRow("Adresse", $user->getAddress());
         printTableRow("Ville", $user->getCity());
         printTableRow("Code postal", $user->getZipCode());
         printTableRow("Tel", $user->getPhone());
         printTableRow("Courriel", $user->getEmail());
     ?>
    </table>
</div>
</fieldset>

<fieldset>
<legend><h3>Informations sur la demande</h3></legend>
<div style="display:block;margin-top:10px">
    <table>
    <?php
         printTableRow("Date de la demande", date("d/m/Y", strtotime($demande->getModificationDate())));
         printTableRow("Status", util::cleanUTF8($demande->getStatus()->getName()));
         printTableRow("Paiement", util::cleanUTF8($demande->getPaymentMethodString()));
         printTableRow("Note", util::cleanUTF8($demande->getDetails()));
    ?>
    </table>
</div>
</fieldset>
<fieldset>
<legend><h3>Voiture 1</h3></legend>
<div style="display:block;margin-top:10px">
    <table>
    <?php
         $car = $demande->getFirstCar();
         printTableRow("Marque", util::cleanUTF8($car->getModel()));
         printTableRow("Couleur", util::cleanUTF8($car->getColor()));
         printTableRow("Annee", $car->getYear());
         printTableRow("Plaque", $car->getLicense());
    ?>
    </table>
</div>
</fieldset>
<fieldset>
<legend><h3>Voiture 2 - Optionnelle</h3></legend>
<div style="display:block;margin-top:10px">
    <table>
    <?php
         $car = $demande->getSecondCar();
         if($car->isValid()) {
            print("Pas de seconde voiture");
         } else {
             printTableRow("Marque", $car->getModel());
             printTableRow("Couleur", $car->getColor());
             printTableRow("Annee", $car->getYear());
             printTableRow("Plaque", $car->getLicense());
         }
    ?>
    </table>
</div>
</fieldset>
<?php
function printTableRow($title, $value) {
    print('<tr>
            <td style="width:250px">'.$title.' </td>
            <td>'.$value.'</td>
          </tr>');
}
?>
</p>
<a id=changeStatusLink" href="print.php?matricule=<?php echo $matricule ?>&changeStatus=1">Changer status</a>
</body>
