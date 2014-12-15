
<?php
    require_once('class.authentification.php');
	require_once('class.util.php');
	require_once('class.demande.php');
	
	define('licenseTabName', 'licenseTab');
	define('residentialProofTabName', 'residentialProofTab');
	define('car1TabName', 'car1Tab');
	define('car2TabName', 'car2Tab');
	
	$auth = authentification::instance();
	$matricules = util::getParam($_POST, 'matricules');
	$section = util::getParam($_GET, 'section');
	
	if($auth->estIdentifie() && isset($matricules) && isset($section))
	{
		
		$matricules = json_decode($matricules);
		$demandCount = count($matricules);
		
		$demande = null;
		if($demandCount == 1)
		{
			$demande = new demande($matricules[0]);
			$demande->updateDataFromDB();
			$options = $demande->getStatus()->getStatusSelectorOptions();
		}
		else if($demandCount == 0) {
			exit(0);
		}
		
		if($section == 'status')
		{
			if($demandCount > 1)
				generateStatusSectionContentMultiDemands($demandCount);
			else
			
				generateStatusSectionContent($demande);
		}
		else if($section == 'proof') 
			generateProofSectionContent($demande);
		
	}
	else 
	{
		header('Location index.php');
		exit(0);
	}
	
	function generateStatusSectionContentMultiDemands($demandCount)
	{
		print('	<div>
					<div>
						<label style="font-size:17px; display:block;" >'.util::cleanUTF8( "L'�tat des $demandCount demandes s�lectionn�es sera modifi�").'</label>
						<label id="demandStateLabel" style="font-size:17px; width:150px; display:inline-block;display:none" >&Eacutetats demandes</label>
						<span id="currentStatusNameContainer">
							<label id="currentStatusName" style="font-size:26px; font-weight:bold"></label> 
							<img id="statusChangeIndicatorIcon" width="16" height="16" style="margin-left:10px; display:none" ></img>
                        <img id="loadingWheel" src="images/loading_wheel.gif" style="width:16px; height:16px;"></img>
							<label id="statusChangeMessage" ></label>
						</span>				
					</div>
					<input type="hidden" id="selectedStatus"  name="selectedStatus" value="" ></input>
					<div style="margin-top:5px">
						<label style="font-size:17px; width:120px; display:inline-block;" > Changer pour </label>				
					<select id="statusSelector" style="font-size:16">
						<option value="'.demandStatus::WAITING_STATUS.'" selected>En attente </option>
						<option value="'.demandStatus::PROOF_OK_STATUS.'">Preuves vérfiées</option>
						<option value="'.demandStatus::REFUSED_STATUS.'">Refusé</option>
						<option value="'.demandStatus::ACCEPTED_STATUS.'">Accepté</option>
						<option value="'.demandStatus::PAID_STATUS.'">Payé</option>
						<option value="'.demandStatus::PRINTED_STATUS.'">Imprimé</option>
						<option value="'.demandStatus::INVALID_PROOF_STATUS.'">Preuves invalides</option>
					</select>
					<div style="float:right">
						
						<span id="confirmStatusChangeButton" style="vertical-align:bottom; float:right" class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only" role="button" aria-disabled="false">
							<span class="ui-button-text" style="padding:4px 6px 4px 6px">Confirmer</span></div>
						</span>
					</div>
					<hr>
					<div id="sendMailCheckContainer" style="margin-top:5px">
						<input type="checkbox" id="sendMailCheck" checked> </input>
						<label> Envoyer courriel indiquant le changement d\'état</label>
					</div>
					<div id="includeDetailsCheckContainer"">
						<input type="checkbox" id="includeDetailsCheck" > </input>
						<label> Inclure les détails dans le contenu du message </label>
					</div>
					<div id="detailsTextAreaContainer">
						<label style=" margin-top:5px;"> Détails optionnels sur le changement d\'état</label>
						<textarea id="detailsTextArea" style="float:left; max-width:100%; max-height:75%; width:100%; height:50%; paddin:0px"></textarea>
					</div>
				</div>'); // End of status section
	}
	
	function generateStatusSectionContent($demande)	
	{
		print('	<div>
					<div>
						<label style="font-size:17px; width:120px; display:inline-block;" >&Eacutetat demande</label>
						<span id="currentStatusNameContainer">
                        <label id="currentStatusName" style="font-size:26px; font-weight:bold">'.util::cleanUTF8($demande->getStatus()->getName()).'</label>
							<img id="statusChangeIndicatorIcon" width="16" height="16" style="margin-left:10px; display:none" ></img>
                        <img id="loadingWheel" src="images/loading_wheel.gif" style="width:16px; height:16px;"></img>
							<label id="statusChangeMessage" ></label>
						</span>				
					</div>
					<input type="hidden" id="selectedStatus"  name="selectedStatus" value="'.$demande->getStatus()->getId().'" ></input>
  					<input type="hidden" id="matricule"  name="matricule" value="'.$demande->getUserData()->getMatricule().'" ></input>
					<div style="margin-top:5px">
						<label style="font-size:17px; width:120px; display:inline-block;" > Changer pour </label>				
					<select id="statusSelector" style="font-size:16">
						<option value="'.demandStatus::WAITING_STATUS.'" selected>En attente </option>
						<option value="'.demandStatus::PROOF_OK_STATUS.'">Preuves vérfiées</option>
						<option value="'.demandStatus::REFUSED_STATUS.'">Refusé</option>
						<option value="'.demandStatus::ACCEPTED_STATUS.'">Accepté</option>
						<option value="'.demandStatus::PAID_STATUS.'">Payé</option>
						<option value="'.demandStatus::PRINTED_STATUS.'">Imprimé</option>
						<option value="'.demandStatus::INVALID_PROOF_STATUS.'">Preuves invalides</option>
					</select>
					<div style="float:right">
					<span id="printStatusButton" style="vertical-align:bottom; float:right" class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only" role="button" aria-disabled="false">
							<span class="ui-button-text" style="padding:4px 6px 4px 6px">Imprimer</span></div>
						</span>	
						<span id="confirmStatusChangeButton" style="vertical-align:bottom; float:right" class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only" role="button" aria-disabled="false">
							<span class="ui-button-text" style="padding:4px 6px 4px 6px">Confirmer</span></div>
						</span>
					</div>
					<hr>
                    <div id="carpoolingDetailsContainer">
                       '.util::cleanUTF8($demande->getDetails()).'
                    </div>
					<div id="sendMailCheckContainer" style="margin-top:5px">
						<input type="checkbox" id="sendMailCheck" checked> </input>
						<label> Envoyer courriel indiquant le changement d\'état</label>
					</div>
					<div id="includeDetailsCheckContainer"">
						<input type="checkbox" id="includeDetailsCheck" checked > </input>
						<label> Inclure les détails dans le contenu du message </label>
					</div>
					<div id="detailsTextAreaContainer">
						<label style=" margin-top:5px;"> Détails optionnels sur le changement d\'état</label>
						<textarea id="detailsTextArea" style="float:left; max-width:100%; max-height:75%; width:100%; height:50%; paddin:0px">'.util::cleanUTF8($demande->getStatus()->getDescription()).'</textarea>
					</div>
				</div>'); // End of status section
	}	
	
	function generateProofSectionContent($demande)
	{
		$hasOptionnalCar = $demande->getSecondCar()->existsInDatabase();
		//print("<label>Status: ".$demande->getStatus()->getName()."</label>");
		
		print("<div id='proofTabs'>
				  <ul>
				  	<li><a href='#".licenseTabName."'><span>Permis</span></a></li>
				  	<li><a href='#".residentialProofTabName."'><span>Résidence</span></a></li>
				    <li><a href='#".car1TabName."'><span>V&eacutehicule 1</span></a></li>
				    ".($hasOptionnalCar ? "<li><a href='#".car2TabName."'><span>Deuxi&#232me v&eacutehicule</span></a></li>" : "")."
				  </ul>
				  <div id='".licenseTabName."'>" 
				  		.getLicenseProofImageHtml($demande).
				  "</div>
				  <div id='".residentialProofTabName."'>
				  	<div>"
				  		.getResidenceProofImageHtml($demande).
				  	"</div>	
				  </div>
				  <div id='".car1TabName."'>"
				  	.getCar1ProofImageHtml($demande)
				  	.getCarHtmlContent($demande->getFirstCar())."
				  </div>");
				  
		if($hasOptionnalCar)
		{
			print(	"<div id='".car2TabName."''>"
						.getCar2ProofImageHtml($demande)
						.getCarHtmlContent($demande->getSecondCar()).
					"</div>");
		}	  
				 		  
		print("	</div>"	); // End of proof tabs
	}
	
	function getLicenseProofImageHtml(demande $demande)
	{
		return getProofImageHtml($demande->getLicense()->getOutputLocation(), licenseTabName);
	}
	
	function getResidenceProofImageHtml(demande $demande)
	{
		return getProofImageHtml($demande->getResidenceProof()->getOutputLocation(), residentialProofTabName);
	}
	
	function getCar1ProofImageHtml(demande $demande)
	{
		return getProofImageHtml($demande->getFirstCar()->getInsurance()->getOutputLocation(), car1TabName);
	}
	
	function getCar2ProofImageHtml(demande $demande)
	{
		return getProofImageHtml($demande->getSecondCar()->getInsurance()->getOutputLocation(), car2TabName);
	}
	
	function getProofImageHtml($imgSrc, $tabName)
	{
			
		return  "	<div  id='demandVerificationImageContainer' style='height:100%' >
						<div class='demandVerificationImageFrame'>
							<img class='demandVerificationImage' id='".$tabName."VerificationImage' src='$imgSrc' data-zoom-image='$imgSrc' original-image-path='$imgSrc' > 
							</img>
						</div>
						
						<div>	
							<button class='popUpImageOverlayButton' style='padding:4x'></button>
							<a id='openImageInTabButtonLink' href='$imgSrc' target='_blank'>
								<button class='openImageInTabButton' style='padding:4x'></button>
							</a>
							<button class='rotateImageCounterClockwiseButton' style='padding:4x' ></button>
							<button class='rotateImageClockwiseButton' style='padding:4x'></button>
						</div>
					</div>";
	
	}
	
	function getCarHtmlContent(car $car)
	{
		$carIndex = $car->getIndex();
		return 	"<span id='demandVerificationInfoContainer'>	
					<label class='infoFieldTitleWithBar' >".util::cleanUTF8('Mod�le')."</label>
					<label id='car".$carIndex."Model' class='infoFieldValue' style='font-size:18px'>".$car->getModel()."</label>
				
					<label class='infoFieldTitleWithBar' >Couleur</label>
					<label id='car".$carIndex."Color' class='infoFieldValue' style='font-size:18px'>".$car->getColor()."</label> 
				
					<label class='infoFieldTitleWithBar' >".util::cleanUTF8('Ann�e')."</label>
					<label id='car".$carIndex."Year' class='infoFieldValue' style='font-size:18px'>".$car->getYear()."</label> 
				
					<label class='infoFieldTitleWithBar' >".util::cleanUTF8('Ann�e')."</label>
					<label id='car".$carIndex."License' class='infoFieldValue' style='font-size:18px'>".$car->getLicense()."</label>
				</span>";	
	}
?>

