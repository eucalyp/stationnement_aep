<?php

error_reporting(E_ALL);

require_once("class.authentification.php");
require_once('class.config.php');
require_once("class.demandeListe.php");
require_once("class.demande.php");
require_once("class.log.php");
require_once("class.validation.php");
require_once("class.userData.php");
require_once("class.car.php");

define('CANCEL_DEMAND_POST', 'cancelDemand');
define('REACTIVATE_DEMAND_POST', 'reactivateDemand');

$objAuth = authentification::instance();
$objLog  = log::instance();
$objvalid= validation::instance();
$objDemandeListe = new demandeListe();
$objDemande = null;
 $fileFieldIndex = 0;

if($objAuth->estIdentifie()) {
	
	$matricule = $_SESSION['usager'];
	
	$submissionTarget = util::getParam($_POST,'submissionTarget');
	$submissionType = util::getParam($_POST,'submissionType');
	
    $demande = new demande($matricule);
	if($submissionTarget == 'demande')
    {
    	if($submissionType == CANCEL_DEMAND_POST)
		{
			$demande->cancelDemand();
            header("Location: demande.php");
		}
		else if($submissionType == REACTIVATE_DEMAND_POST)
		{
			$demande->reactivateDemand();
			header("Location: demande.php");
		}
		else
		{
			$demande->getDataFromPost($_POST, $matricule);
			
			if($demande->isValid())
            {
				if($demande->saveToDatabase($matricule))
				{
					header("Location: demande.php");	
				}
            }
		}
	}
	else if($submissionTarget == 'changeInfos')
	{
		header("Location: register.php");	
	}
	else
	{
		$demande->getDataGromDB($matricule);
	}
	
	
    require_once("header.php");
   
    // STATUS
	//////////////////////////
	
    print(createStatusForm($demande->getStatus()));

    if(!util::isWebsiteOpen()) {
        print("<h3>LES DEMANDES SONT PRESENTEMMENT FERMES - Vous serez avisé par courriel lorsque la période de demande ouvrira. Bonne journée.<h3>");
    }
	// INFORMATIONS PERSONELLES
	//////////////////////////
	
	$userData = $demande->getUserData();
	
	print('<form method="post" action="register.php?" name="" enctype="multipart/form-data">
			<input type="hidden" name="submissionTarget" value="changeInfos"/>');	
			
	print('<fieldset >
			<legend> <h3>Informations personnelles</h3> </legend>
			Assurez que vos informations personnelles sont exactes et valides avant de faire votre demande. 
			<div style="display:block;margin-top:10px" >');
				
				print( createInfoField(userData::LAST_NAME_TAG, $userData->getLastName().", ".$userData->getFirstName()));
				print( createInfoField(userData::EMAIL_TAG, $userData->getEmail()));
				print( createInfoField(userData::ADDRESS_TAG, util::UTF8toISO8859($userData->getAddress()).", ".$userData->getCity().", ".$userData->getZipCode()));
				print( createInfoField(userData::PHONE_TAG, $userData->getPhone()));
				
	print('	</div>			
			<div class="registerField" style="float: right;margin-top:8 ">
				<input class="searchButton" style="font-size: 16" size="15" value="Modifier" type="submit" />
			</div>
		</fieldset>');
						
	print('</form>');

	// DEMANDE
	//////////////////////////
	print('<form method="post" action="" name="demande" enctype="multipart/form-data">
			<input type="hidden" name="submissionTarget" value="demande"/>');	
		
	print('<fieldset >
			<legend> <h3>Demande de stationnement</h3> </legend>
			<div style="display:block" >');
			
    printn('<label>Type de paiement :</label>'.printSelect(demande::PAYMENT_METHOD_FORM_FIELD, $demande->getPaymentMethod(), $demande->getPaymentErrorMessage()).'<br/></br>');
	
	createCheckBoxField(demande::CARPOOLING_FORM_FIELD, 
						$demande->isCarpooling(), 
						"Je prevois faire du covoiturage (si oui, indiquer le nom, prénom et matricule des personnes 
						avec qui vous prevoyez covoiturer dans les notes concernant votre demande");
	
	createCheckBoxField(demande::CARPOOLING_OTHERS_FORM_FIELD, 
						$demande->isCarpoolingOthers(), 
						'Je serais interesse à trouver des personnes pour faire du covoiturage');
					
    printn('<label class="formFieldFileLabel">Note concernant votre demande (optionnel) :</label><textarea name="'.demande::DETAILS_FORM_FIELD.'" style="max-width:65%" cols="40" rows="7">'.$demande->getDetails().'</textarea><br/>');

    print(createDemandFileField('Preuve de permis de conduire',
    							'(nom de fichier alphanum&eacuterique [a-z][0-9] sans espace)', 
								'', 
								'file', 
								demande::DRIVING_LICENSE_FORM_FIELD, 
								'', 
								demande::DRIVING_LICENSE_FORM_FIELD, 
								$demande->getLicenseErrorMessage(), 
								$demande->getLicense()));
								
    print(createDemandFileField('Preuve de résidence',
    							'(nom de fichier alphanum&eacuterique [a-z][0-9] sans espace)', 
    							'', 
    							'file', 
    							demande::PROOF_OF_RESIDENCE_FORM_FIELD, 
    							'', 
    							demande::PROOF_OF_RESIDENCE_FORM_FIELD, 
    							$demande->getResidenceErrorMessage(),
    							$demande->getResidenceProof()));

	print('	</div>			
		</fieldset>');
		
	// VOITURES
	//////////////////////////
    createCarForm($demande->getFirstCar(), '1');	 
    createCarForm($demande->getSecondCar(), '2 - optionnel');		

	print('	<div class="registerField" style="float: right;margin-top:8;margin-bottom:15 ">				
			<input class="searchButton" style="font-size: 16" size="15" value="'.($demande->hasExistingDemandInDB() ? "Enregistrer" : "Soumettre").'" type="submit"'.(util::isWebsiteOpen() ? "" : "disabled").'/>
			</div>');
			
	print('</form>');
} 
else 
{
    require_once("header.php");
    print("Vous devez &ecirctre identifi&eacute pour pouvoir faire une demande ou consulter une demande en cours</br>");
    require_once('class.loginWidget.php');
	print(loginWidget::getWidget("http://$_SERVER[HTTP_HOST]$_SERVER[PHP_SELF]", $login));
}


readfile('light_footer.html');


exit(0);

function createStatusForm(demandStatus $status)
{
	
	if(!$status->hasStatus())
		return;
	
	$onClickAction = $status->isCanceled() 	? 	""
											: 	"return confirm('Attention: cela annulera votre demande.Vous pourrez toujours la r&eacuteactiver par la suite, mais elle ne sera pas trait&eacutee entre temps.')";
	
	print('<form method="post" action="" name="demande" enctype="multipart/form-data">
			<input type="hidden" name="submissionTarget" value="demande"/>
			<input type="hidden" name="submissionType" value="'.($status->isCanceled() ? REACTIVATE_DEMAND_POST : CANCEL_DEMAND_POST ).'"/>');
				
	print('<fieldset style="margin-bottom:25px">
				<legend> <b><h2>&eacutetat de la demande </h2></b> </legend>
					<div style="display:block" >
						<div>
							<label style="vertical-align:baseline" >État de la demande: </label>
							 <h4 style="display:inline" >'.$status->getName().'<h4>
						</div>
						<div>'.$status->getDescription().'</div>');
					
	print('			</div>
				<div class="registerField" style="float: right;margin-top:8 ">
					<input 	class="searchButton" style="font-size: 16" 
							onclick="'.$onClickAction.'" 
							size="15" value="'.($status->isCanceled() ? "R&eacuteactiver demande" : "Annuler demande").'" type="submit" />
				</div>
			</fieldset>');
	print('</form>');			
}

function createCarForm(car $car, $title) {
	
	print('<fieldset >
				<legend> <h3>V&eacutehicule '.$title.'</h3> </legend>
				<div style="display:block" >');
			
    print(createDemandTextField(car::MODEL_TAG, $car->getModel(), '', $car->getModelParamName(), '32', $car->getModelParamName(), $car->getModelErrorMessage()));				
    print(createDemandTextField(car::COLOR_TAG, $car->getColor(), '',  $car->getColorParamName(), '16', $car->getColorParamName(), $car->getColorErrorMessage()));
    print(createDemandTextField(car::YEAR_TAG, $car->getYear(), '', $car->getYearParamName(), '4', $car->getYearParamName() , $car->getYearErrorMessage()));				
    print(createDemandTextField(car::LICENSE_TAG, $car->getLicense(), '', $car->getLicenseParamName(), '128', $car->getLicenseParamName(), $car->getLicenseErrorMessage()));
    print(createCheckBoxField($car->getElectricParamName(), $car->getIsElectric(), car::ELECTRIC_TAG));
    print(createDemandFileField(car::INSURANCE_TAG,
    							'(nom de fichier alphanum&eacuterique [a-z][0-9] sans espace)', 
    							'', 
    							'file', 
    							$car->getInsuranceParamName(), 
    							'', 
    							$car->getInsuranceParamName(), 
    							$car->getInsuranceErrorMessage(), 
    							$car->getInsurance()));
	
	print('	</div>			
		</fieldset>');

}

function createInfoField($fieldText, $value)
{
	return '<div>
				<label>'.$fieldText.': </label>
				<label>'.$value.'</label>	
			</div>';
	
}

function createCheckBoxField($name, $value, $text )
{
	$isChecked = $value ? "checked='checked'": "";
	printn('<input name="'.$name.'" class="formCheckboxInput" value="1" type="checkbox" '.$isChecked.'>'.$text.'.<br><br>');
}

function createDemandFileField($fieldText, $fieldSubtext, $value, $type, $fieldName, $maxLength, $id, $errMessage, file $file)
{
	global $fileFieldIndex;
	$fileFieldIndex++;
	
	$asterix = empty($errMessage) ? "" : "*";
	
	// If file comes from an URL, display it. If not, no file is present on server/database: we give the normal input field to upload one
	if(!$file->isLoadedFromUrl())
	{						
		return '<div class="formFileField" >
					<span style="float:left">			
						<label class="formFieldFileLabel" >'.$fieldText.'</label> <br>
						<label class="formFieldFileSubtextLabel" >'.$fieldSubtext.'</label>
					</span>
					<div style="display:inline" ></div>
					<input class = "formInput" value="'.$value.'" type="'.$type.'" name="'.$fieldName.'" size="8" maxlength="'.$maxLength.'" id="'.$id.'" />
					<label class="formErrorField" >'.$asterix." ".$errMessage.'</label>
				</div>';					
	}
	else 
	{
		// Check different types of files, for now only image
		
		// Image
		$fileInput = "fileInput".$fileFieldIndex;
		$changeFileButton = "changeFileButton".$fileFieldIndex;
		
		return '<div>		
					<div  class="formFileFieldLabelContainer">
						<label class="formFieldFileLabel">'.$fieldText.'</label> 
					</div>
					<div style="margin-left:205px;  padding: 5px; border-left:1px solid rgba(0, 0, 0, 0.4)"> 
						<span width="75%"  >
							<img  class="formFileImage" src="'.$file->getOutputLocation().'">  </img>					
							<div style="text-align:center" >
								<input id="'.$fileInput.'" style="display:none; margin-left:auto; margin-right:auto; margin-top:10px" value="'.$value.'" type="'.$type.'" name="'.$fieldName.'" size="8" maxlength="'.$maxLength.'" id="'.$id.'" />
								<div id="'.$changeFileButton.'" style="cursor:pointer; margin-top: 10px; margin-left:auto; margin-right:auto" onclick="replace(\''.$changeFileButton.'\',\''.$fileInput.'\')">
									<img width="16px" height="16px" src="images/file_icon.png" ></img>
									<span>Modifier</span>
								</div>
							</div>
						</span>
						<label class="formErrorField" >'.$asterix." ".$errMessage.'</label>
					</div>
				</div>';
	}
}

function createDemandTextField($fieldText, $value, $fieldSubtext, $fieldName, $maxLength, $id, $errMessage, $class="formInput", $extra="")
{
	return createDemandField($fieldText, $value, 'text', $fieldName, $maxLength, $id, $errMessage, $class="formInput", $extra="", $fieldSubtext);
}

function createDemandField($fieldText, $value, $type, $fieldName, $maxLength, $id, $errMessage, $class="formInput", $extra="", $fieldSubtext="")
{
	$asterix = empty($errMessage) ? "" : "*";
	return '<div class="formField" >			
					<label class="formFieldLabel" >'.$fieldText.'</label>
				<input '.$extra.' class = "'.$class.'" value="'.$value.'" type="'.$type.'" name="'.$fieldName.'" size="8" maxlength="'.$maxLength.'" id="'.$id.'" />
				<label class="formErrorField" >'.$asterix." ".$errMessage.'</label>
			</div>';
}   
    if (strlen($retour) > 0)
    {
        $retour = "<font color=\"#ff0000\"><b>$retour</b></font>";
    }
    elseif (!$demande->sauvegarde())
    {
        $retour .= "<font color=\"#ff0000\"><b>Impossible d'effectuer la sauvegarde! <br></b></font>\n";
    }
    else
    {
        if ($idAuDebut == '0')
        {
            $email = file_get_contents('emailBienvenue.txt');
            $email = preg_replace("/@@PRENOM@@/",$demande->getPrenom(),$email);
            $email = preg_replace("/@@NOM@@/",$demande->getNom(),$email);
            $email = preg_replace("/@@ID@@/",$demande->getID(),$email);
            
            $headers = 'From: Stationnement AEP <stationnement@aep.polymtl.ca>' . "\r\n" .
                       'X-Mailer: PHP/' . phpversion();
            
            if (config::SendEmail) mail($demande->getEmail(),"Demande de stationnement recue (".$demande->getId().")",$email, $headers);
            $output = file_get_contents('bienvenue.html');
            $output = preg_replace("/@@ID@@/",$demande->getID(),$output);
            print $output;
            readfile('footer.html');
            exit(0);
        }
        elseif ($changementStatus)
        {
            $statutEnMot = printLecture('statusLong',$demande->getStatus());
            $email = file_get_contents('emailStatut.txt');
            $email = preg_replace("/@@PRENOM@@/",$demande->getPrenom(),$email);
            $email = preg_replace("/@@NOM@@/",$demande->getNom(),$email);
            $email = preg_replace("/@@ID@@/",$demande->getID(),$email);
            $email = preg_replace("/@@STATUT@@/",$statutEnMot,$email);
            $headers = 'From: Stationnement AEP <stationnement@aep.polymtl.ca>' . "\r\n" .
                       'X-Mailer: PHP/' . phpversion();
            if (config::SendEmail) 
            {
            	 mail($demande->getEmail(),"Demande de stationnement (".$demande->getId().") : changement de statut",$email, $headers);
            	$retour .= "<font color=\"#00cc00\"><b>Email envoy&eacute avec succ&eagraves concernant le statut! <br></b></font>\n";
        	}
        
        $retour .= "<font color=\"#00cc00\"><b>Sauvegarde effectu&eacutee avec succ&eagraves! <br></b></font>\n";
    }
    
    return $retour;
}
/*****************************************************************************
    Le script suivant permet de récupérer la distance et le temps de trajet entre une information suffisament précise pour géolocaliser  
 */

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



// Retourne le tag <select> selon le $type.
// La $valeur sera sélectionnée.
function printSelect($type, $valeur, $errorMessage = "")
{
    if ($type == 'limiteNb' || $type == 'statusRecherche' || $type=demande::PAYMENT_METHOD_FORM_FIELD)
    {
        $retour = '<select name="'.$type.'">'."\n";
    }
    else
    {
        $retour = '<select name="form_'.$type.'">'."\n";
    }

    if ($type == 'statusEtudiant')
    {
        $retour .= '<option value="0">--</option>'."\n";
        if ($valeur == DEMANDE_STATUSETUDIANT_PLEIN) {$selected='selected';} else {$selected='';}
        $retour .= '<option value="'.DEMANDE_STATUSETUDIANT_PLEIN.'" '.$selected.'>Temps partiel</option>'."\n";
        if ($valeur == DEMANDE_STATUSETUDIANT_PARTIEL) {$selected='selected';} else {$selected='';}
        $retour .= '<option value="'.DEMANDE_STATUSETUDIANT_PARTIEL.'" '.$selected.'>Temps plein</option>'."\n";
    }
    elseif ($type == demande::PAYMENT_METHOD_FORM_FIELD)
    {
        $retour .= '<option value="0">--</option>'."\n";
        if ($valeur == DEMANDE_TYPE_COMPTANT) {$selected='selected';} else {$selected='';}
        $retour .= '<option value="'.DEMANDE_TYPE_COMPTANT.'" '.$selected.'>Comptant</option>'."\n";
        if ($valeur == DEMANDE_TYPE_CHEQUE) {$selected='selected';} else {$selected='';}
        $retour .= '<option value="'.DEMANDE_TYPE_CHEQUE.'" '.$selected.'>Chèque</option>'."\n";
        if ($valeur == DEMANDE_TYPE_MANDAT) {$selected='selected';} else {$selected='';}
        $retour .= '<option value="'.DEMANDE_TYPE_MANDAT.'" '.$selected.'>Mandat poste</option>'."\n";
        if ($valeur == DEMANDE_TYPE_INTERAC) {$selected='selected';} else {$selected='';}
        $retour .= '<option value="'.DEMANDE_TYPE_INTERAC.'" '.$selected.'>Interac</option>'."\n";
        if ($valeur == DEMANDE_TYPE_VISA) {$selected='selected';} else {$selected='';}
        $retour .= '<option value="'.DEMANDE_TYPE_VISA.'" '.$selected.'>Visa</option>'."\n";
        if ($valeur == DEMANDE_TYPE_MC) {$selected='selected';} else {$selected='';}
        $retour .= '<option value="'.DEMANDE_TYPE_MC.'" '.$selected.'>MasterCard</option>'."\n";
    }
    elseif ($type == 'status')
    {
        if ($valeur == DEMANDE_STATUS_ATTENTE) {$selected='selected';} else {$selected='';}
        $retour .= '<option value="'.DEMANDE_STATUS_ATTENTE.'" '.$selected.'>En attente</option>'."\n";
        if ($valeur == DEMANDE_STATUS_PREUVEOK) {$selected='selected';} else {$selected='';}
        $retour .= '<option value="'.DEMANDE_STATUS_PREUVEOK.'" '.$selected.'>Preuves acceptées</option>'."\n";
        if ($valeur == DEMANDE_STATUS_ACCEPTE) {$selected='selected';} else {$selected='';}
        $retour .= '<option value="'.DEMANDE_STATUS_ACCEPTE.'" '.$selected.'>Acceptée</option>'."\n";
        if ($valeur == DEMANDE_STATUS_IMPRIME) {$selected='selected';} else {$selected='';}
        $retour .= '<option value="'.DEMANDE_STATUS_IMPRIME.'" '.$selected.'>Imprimée</option>'."\n";
        if ($valeur == DEMANDE_STATUS_PAYE) {$selected='selected';} else {$selected='';}
        $retour .= '<option value="'.DEMANDE_STATUS_PAYE.'" '.$selected.'>Payée</option>'."\n";
        if ($valeur == DEMANDE_STATUS_REFUSE) {$selected='selected';} else {$selected='';}
        $retour .= '<option value="'.DEMANDE_STATUS_REFUSE.'" '.$selected.'>Refusée</option>'."\n";
        if ($valeur == DEMANDE_STATUS_ANNULE) {$selected='selected';} else {$selected='';}
        $retour .= '<option value="'.DEMANDE_STATUS_ANNULE.'" '.$selected.'>Annulée</option>'."\n";
    }
    elseif ($type == 'statusRecherche')
    {
        if ($valeur == '-1') {$selected='selected';} else {$selected='';}
        $retour .= '<option value="-1" '.$selected.'>-- Tous --</option>'."\n";
        if ($valeur == DEMANDE_STATUS_ATTENTE) {$selected='selected';} else {$selected='';}
        $retour .= '<option value="'.DEMANDE_STATUS_ATTENTE.'" '.$selected.'>En attente</option>'."\n";
        if ($valeur == DEMANDE_STATUS_PREUVEOK) {$selected='selected';} else {$selected='';}
        $retour .= '<option value="'.DEMANDE_STATUS_PREUVEOK.'" '.$selected.'>Preuves acceptées</option>'."\n";
        if ($valeur == DEMANDE_STATUS_ACCEPTE) {$selected='selected';} else {$selected='';}
        $retour .= '<option value="'.DEMANDE_STATUS_ACCEPTE.'" '.$selected.'>Acceptée</option>'."\n";
        if ($valeur == DEMANDE_STATUS_IMPRIME) {$selected='selected';} else {$selected='';}
        $retour .= '<option value="'.DEMANDE_STATUS_IMPRIME.'" '.$selected.'>Imprimée</option>'."\n";
        if ($valeur == DEMANDE_STATUS_PAYE) {$selected='selected';} else {$selected='';}
        $retour .= '<option value="'.DEMANDE_STATUS_PAYE.'" '.$selected.'>Payée</option>'."\n";
        if ($valeur == DEMANDE_STATUS_REFUSE) {$selected='selected';} else {$selected='';}
        $retour .= '<option value="'.DEMANDE_STATUS_REFUSE.'" '.$selected.'>Refusée</option>'."\n";
        if ($valeur == DEMANDE_STATUS_ANNULE) {$selected='selected';} else {$selected='';}
        $retour .= '<option value="'.DEMANDE_STATUS_ANNULE.'" '.$selected.'>Annulée</option>'."\n";
    }
    elseif ($type == 'categoriePermis')
    {
        if ($valeur == DEMANDE_PERMIS_C) {$selected='selected';} else {$selected='';}
        $retour .= '<option value="'.DEMANDE_PERMIS_C.'" '.$selected.'>C</option>'."\n";
    }
    
    elseif ($type == 'limiteNb')
    {
        if ($valeur == 10) {$selected='selected';} else {$selected='';}
        $retour .= '<option value="10" '.$selected.'>10</option>'."\n";
        if ($valeur == 25) {$selected='selected';} else {$selected='';}
        $retour .= '<option value="25" '.$selected.'>25</option>'."\n";
        if ($valeur == 50) {$selected='selected';} else {$selected='';}
        $retour .= '<option value="50" '.$selected.'>50</option>'."\n";
        if ($valeur == 100) {$selected='selected';} else {$selected='';}
        $retour .= '<option value="100" '.$selected.'>100</option>'."\n";
        if ($valeur == 10000) {$selected='selected';} else {$selected='';}
        $retour .= '<option value="10000" '.$selected.'>10000</option>'."\n";
    }
    
	$asterix = empty($errorMessage) ? "" : "*";
    $retour .= '</select>';
    $retour .= '<label class="formErrorField" >'.$asterix." ".$errorMessage.'</label>'."\n";
    return $retour;
}

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

// Remplace les double guillements par des simples pour faciliter le tout avec le HTML.
function checkGuillemets($texte)
{
    return preg_replace('/"/',"'",$texte);
}

?>
