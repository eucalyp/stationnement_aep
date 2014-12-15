<?php

	require_once('class.authentification.php');
	require_once('class.util.php');
	require_once('class.demande.php');
	
	$auth = authentification::instance();
	$matricule = util::getParam($_POST, 'matricule');
	
	if($auth->estIdentifie() && isset($matricule))
	{
		
		$demande = new demande($matricule);
		$demande->updateDataFromDB();
		$userData = $demande->getUserData();
		$tripInfo = $demande->getTripInfo();

		print("<div id='rightSideInfoTabs'>
				  <ul>
				    <li><a href='#personInfo'><span>Informations</span></a></li>
				    <li><a href='#otherInfos'><span>Infos additionnelles</span></a></li>
				     <li><a href='#options'><span>Options</span></a></li>
				  </ul>
				  <div id='personInfo' style='height:100%'>
				  	<label class='infoFieldTitleWithBar' ' >Nom</label>
				  	<label class='infoFieldValue' >".$userData->getLastName().", ".$userData->getFirstName()." (".$userData->getMatricule().")"."</label>
				  	
				  	<label class='infoFieldTitleWithBar' >Email</label>
				  	<a class='infoFieldValue' href='mailto:".$userData->getEmail()."' target='_blank'>".$userData->getEmail()."</a>
				  	
				  	<label class='infoFieldTitleWithBar' >T&eacutel&eacutephone</label>
				  	<label class='infoFieldValue'>".$userData->getPhone()."</label>
					
				  	<label class='infoFieldTitleWithBar' >Adresse</label>
				  	<label class='infoFieldValue'>".$userData->getAddress().", ".$userData->getCity().", ".$userData->getZipCode()."</label>
				  	
				  	<img id='googleMap' src=".getMapImageUrl($tripInfo)."></img>
				  	
					<div class='bottomBorderedElement' style='margin-top:8px'>
						<label style='display:inline'>D&eacutesire faire du covoiturage</label>
						<label class='infoFieldValue' style='float:right; font-weight:bold'>".($demande->isCarpooling() ? 'Oui' : 'Non')."</label>
					</div>
					
					<div class='bottomBorderedElement' style='margin-top:4px'>
						<label style='display:inline'>D&eacutesire faire du covoiturage avec d'autres</label>
						<label class='infoFieldValue' style='float:right; font-weight:bold'>".($demande->isCarpoolingOthers() ? 'Oui' : 'Non')."</label>
					</div>
					
				  </div>
				  <div id='otherInfos' style='height:100%'>
				  	<label class='infoFieldTitleWithBar' ' >M&eacutethode de paiement</label>
				  	<label class='infoFieldValue' >".util::getPaymentMethodNameFromId(($demande->getPaymentMethod()))."</label>
				  	
					<label class='infoFieldTitleWithBar' ' >Date de cr&eacuteation</label>
				  	<label class='infoFieldValue' >".$demande->getModificationDate()."</label>
				  	
					<label class='infoFieldTitleWithBar' ' >Date de derni&egravere modification</label>
				  	<label class='infoFieldValue' >".$demande->getCreationDate()."</label>
				  	
					<label class='infoFieldTitleWithBar' ' >Temps de trajet estim&eacute</label>
				  	<label class='infoFieldValue' >".$demande->getTripInfo()->getDurationInMinutes()." minutes</label>
				  </div>
				  <div id='options' style='height:100%'>
				  	
				  </div>
			  </div>");  
	}
	else 
	{
		exit(0);	
	}
  
  
  function getMapImageUrl(tripInfo $tripInfo)
  {
	$location = $tripInfo->getLatitude().",".$tripInfo->getLongitude();
	$location = rawurlencode(mb_convert_encoding($location,"UTF-8"));
	$poly = "45.504448,-73.614204";
	return "http://maps.googleapis.com/maps/api/staticmap?center=$location&zoom=10&size=375x200&markers=color:red%7Clabel:P%7C".$poly."&markers=color:blue%7Clabel:S%7C".$location."&maptype=roadmap&sensor=false";
  }
?>
