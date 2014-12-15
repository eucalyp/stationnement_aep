<?php

   	require_once('class.authentification.php');
   	require_once('class.util.php');
   	require_once('class.demande.php');
   	require_once('class.database.php');
		
   	$auth = authentification::instance();
   
  
   	if(!$auth->estIdentifie() || !$auth->isUserAdmin())
   	{
   		header("Location: index.php");
		exit(0);	
   	}
   
   	$status = util::getParam($_GET, 'status');
   	$matricules = util::getParam($_GET, 'matricules');
  	$details = util::getParam($_GET, 'details');
	$includeDetails = util::getParam($_GET, 'includeDetailsInMail') == 'true';
	$sendMail = util::getParam($_GET, 'sendMail') == 'true';
	
	$errorIconPath = 'images/error_icon.png';
	$successIconPath = 'images/success_icon.png';
	
	$response->statusChangeIndicatorImagePath = $successIconPath;
	$response->statusChangeMessage = "";
   	$response->hasError = false;
	
   	if(!isset($status) || !isset($matricules))
   	{
   		$response->statusChangeIndicatorImagePath = $errorIconPath;
   		$response->statusChangeMessage = "Argument manquant";
   		$response->hasError = true;
   		print json_encode($response);
   		return;
	}
	
    $response->selectedDemands = json_decode($matricules);	
	
	try
	{
		//$matricules = json_decode($matricules);
		$database = database::instance();
		$database->beginTransaction();
		
		$matricules = json_decode($matricules);
		
		foreach ($matricules as $matricule) {
			
			$demand = new demande($matricule);
			$sendSuccesful = $demand->getStatus()->changeStatusTo($status,$details,$sendMail,$includeDetails);
			if(!$sendSuccesful)
				throw new Exception("Erreur d'envoi du message");
			
			$demand->refreshStatus();
			$response->newStatus = util::cleanUTF8($demand->getStatus()->getName());
		}
		$database->commitTransaction();
		
		print json_encode($response);
	}
	catch(Exception $e)
	{
		$response->statusChangeIndicatorImagePath = $errorIconPath;
   		$response->statusChangeMessage = $e->getMessage();
   		$response->hasError = true;
		$database->abortTransaction();
		
		print json_encode($response);
	}
	
	
	
   	
?>
