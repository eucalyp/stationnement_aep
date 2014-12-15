<?php

require_once('class.util.php');
require_once('class.demande.php');
	
$auth = authentification::instance();
if(!$auth->estIdentifie() || !$auth->isUserAdmin())
{
	header("Location: index.php");
	exit(0);		
}

$selectedTab = util::getParam($_GET, 'selectedTab');
$matricule = util::getParam($_GET, 'matricule');

$response->hasError = false;

if(!isset($selectedTab) || !isset($matricule))
{
	$response->hasError = true;
	print(json_encode($response));
	exit(0);
}

$demande = new demande($matricule);
$demande->getDataGromDB($matricule);

$response->selectedTab = $selectedTab;

switch ($selectedTab) {
	case 'licenseTab': $response = loadLicenseData($demande);
		break;
	case 'residentialProofTab': $response =  loadResidentialProofData($demande);
		break;
	case 'car1Tab': $response =   loadFirstCarInsuranceData($demande);
		break;
	case 'car2Tab': $response =  loadSecondCarInsuranceData($demande);
		break;
}

print(json_encode($response));

function loadLicenseData($demande)
{
	$response->tabHtmlContent = "";
	$response->imageSource = $demande->getLicense()->getOutputLocation();
	return $response;
}

function loadResidentialProofData($demande)
{
	$response->tabHtmlContent = "";
	$response->imageSource = $demande->getResidenceProof()->getOutputLocation();
	return $response;
}

function loadFirstCarInsuranceData($demande)
{
	return loadCarInsuranceData($demande->getFirstCar());
}

function loadSecondCarInsuranceData($demande)
{
	return loadCarInsuranceData($demande->getSecondCar());
}

function loadCarInsuranceData(car $car)
{
	
	$response->model = $car->getModel();
	$response->color = $car->getColor();
	$response->year = $car->getYear();
	$response->license = $car->getLicense();
							 
	$response->imageSource = $car->getInsurance()->getOutputLocation();
	return $response;
}

?>