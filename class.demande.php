<?php

error_reporting(E_ALL);

/**
 * class.demande.php
 *
 * @author Jean-Francois Levesque, <jf.levesque@step.polymtl.ca>
 * @author Philippe Gref-Viau, <pgrefviau@gmail.com>
 */


/*
 * Definition des constantes pour la base de données.
 *
 * ATTENTION : Lorsqu'il y a ajout ou suppression de constantes, 
 *             il faut modifier les méthodes SET correspondantes.
 */
define('DEMANDE_TYPE_COMPTANT', 1);
define('DEMANDE_TYPE_CHEQUE',   2);
define('DEMANDE_TYPE_MANDAT',   3);
define('DEMANDE_TYPE_INTERAC',  4);
define('DEMANDE_TYPE_VISA',     5);
define('DEMANDE_TYPE_MC',       6);

define('DEMANDE_PERMIS_C',      0);

define('DEMANDE_GROUPE_AEP',      0);

define('DEMANDE_STATUS_ATTENTE',        0);
define('DEMANDE_STATUS_REFUSE',         1);
define('DEMANDE_STATUS_ACCEPTE',        2);
define('DEMANDE_STATUS_PAYE',           3);
define('DEMANDE_STATUS_PREUVEOK',       4);
define('DEMANDE_STATUS_ANNULE',         5);
define('DEMANDE_STATUS_IMPRIME',        6);

define('DEMANDE_STATUSETUDIANT_PLEIN',  1);
define('DEMANDE_STATUSETUDIANT_PARTIEL',2);


if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

require_once('class.config.php');
require_once('class.database.php');
require_once('class.log.php');
require_once('class.validation.php');
require_once('class.util.php');
require_once('class.file.php');
require_once('class.car.php');
require_once('class.demandStatus.php');
require_once('class.tripInfo.php');
require_once('class.userData.php');
/**
 * Short description of class demande
 *
 * @access public
 * @author Jean-Francois Levesque, <jf.levesque@step.polymtl.ca>
 */
class demande
{
	// Constants
	const MATRICULE_DB_FIELD = "matricule";
	const PAYMENT_METHOD_DB_FIELD = "payment";
	const CREATION_DATE_DB_FIELD = "dateCreation";
	const MODIF_DATE_DB_FIELD = "dateLastModif";
	const STATUS_DB_FIELD = "status";
	const STATUS_DETAILS_DB_FIELD = "statusDetails";
	const CARPOOLING_DB_FIELD = "carpooling";
	const CARPOOLING_OTHERS_DB_FIELD = "carpoolingPool";
	const DETAILS_DB_FIELD = "carpoolingDetails";
	const DRIVING_LICENSE_DB_FIELD = "drivingLicenseURL";
	const PROOF_OF_RESIDENCE_DB_FIELD = "residentialProofURL";
	const CAR1_DB_FIELD = "car1";
	const CAR2_DB_FIELD = "car2";
	const TRIP_DB_FIELD = "tripInfo";

	
	const PAYMENT_METHOD_FORM_FIELD = "form_payment";
	const CREATION_DATE_FORM_FIELD = "dateCreation";
	const MODIF_DATE_FORM_FIELD = "dateLastModif";
	const STATUS_FORM_FIELD = "demandStatus";
	const CARPOOLING_FORM_FIELD = "carpooling";
	const CARPOOLING_OTHERS_FORM_FIELD = "carpoolingWithOthers";
	const DETAILS_FORM_FIELD = "carpoolingDetails";
	const DRIVING_LICENSE_FORM_FIELD = "drivingLicense";
	const PROOF_OF_RESIDENCE_FORM_FIELD = "residenceProof";
	
    const DRIVING_LICENSE_DIRECTORY = "proofs/license/";
    const PROOF_OF_RESIDENCE_DIRECTORY = "proofs/residential/";
    // Attributes
   
   	private $userData = null;
   
	private $paymentMethod = 0;
	private $creationDate       = 0;
    private $modificationDate   = 0;
	private $carpooling = false;
	private $carpoolingOthers = false;
	private $details = "";
	private $drivingLicense;
	private $proofOfResidence;
    private $status = null;
    private $matricule = null; 
	private $car1 = null;
	private $car2 = null;
	private $tripInfo = null;

    private $isDataFromPost = false;
    private $hasExistingDemandInDatabase = false;
	
    private $objLog      = null;
    private $database    = null;
 
   // --- OPERATIONS ---
    
    public function __construct()
    {
        $a = func_get_args();
        $i = func_num_args();
        if(method_exists($this, $f='__construct'.$i)) {
            call_user_func_array(array($this, $f), $a);
        } else {
            $this->init();
	    	$this->status = new demandStatus();
        }
    }

    public function __construct1($matricule) {
        $this->matricule = $matricule;
        $this->init();
        $this->status = new demandStatus($matricule);
        $this->updateDataFromDB();
    }

    public function init() {
         $this->objLog      = log::instance();
        $this->database    = database::instance();
       
		$this->car1 = new car(1);
		$this->car2 = new car(2);
		
		$this->userData = new userData($this->matricule);
		$this->userData->getUserData($_SESSION['usager']);

    }

    public function updateDataFromDB() {
        $this->getDataGromDB($this->matricule);
    }

	public function getDataGromDB($matricule)
	{
		
		$results = $this->database->requete("SELECT * from st_demande WHERE matricule = '$matricule'");
		
		$this->drivingLicense = new demandTableFile();
		$this->proofOfResidence = new demandTableFile();
		
		$this->status->loadStatusData($matricule);
		
		if (mysql_num_rows($results) != 1)
        {
        	$this->hasExistingDemandInDatabase = false;
            return false;
        }
		
		$this->hasExistingDemandInDatabase = true;
        
        $resultsArray = mysql_fetch_array($results);
        
        $this->paymentMethod = $resultsArray[demande::PAYMENT_METHOD_DB_FIELD];
        $this->creationDate = $resultsArray[demande::CREATION_DATE_DB_FIELD];
		$this->modificationDate = $resultsArray[demande::MODIF_DATE_DB_FIELD];
		$this->carpooling = $resultsArray[demande::CARPOOLING_DB_FIELD];
		$this->carpoolingOthers = $resultsArray[demande::CARPOOLING_OTHERS_DB_FIELD];
		$this->details = $resultsArray[demande::DETAILS_DB_FIELD];
	
		$this->drivingLicense->loadFromServer($matricule, demande::DRIVING_LICENSE_DB_FIELD);
		$this->proofOfResidence->loadFromServer($matricule, demande::PROOF_OF_RESIDENCE_DB_FIELD);
		
		$this->car1->getDataFromDB($resultsArray[demande::CAR1_DB_FIELD], $matricule);
		$this->car2->getDataFromDB($resultsArray[demande::CAR2_DB_FIELD], $matricule);
		
		$this->tripInfo = new tripInfo();
		$this->tripInfo->getTripInfoFromDb($resultsArray[demande::TRIP_DB_FIELD]);
		
		$this->isDataFromPost = false;
		
		return true;
	}
	
	public function getDataFromPost($postData,$matricule)
	{
		$this->paymentMethod = util::getParam($postData, demande::PAYMENT_METHOD_FORM_FIELD);		
		$this->carpooling = util::getParam($postData, demande::CARPOOLING_FORM_FIELD);
		$this->carpoolingOthers = util::getParam($postData, demande::CARPOOLING_OTHERS_FORM_FIELD);
		$this->details = util::getParam($postData, demande::DETAILS_FORM_FIELD);
		
		$this->drivingLicense = new demandTableFile();
		$this->proofOfResidence = new demandTableFile();
				
		$this->drivingLicense->loadFromPost(demande::DRIVING_LICENSE_FORM_FIELD, 
											util::$ALLOWED_EXTENSIONS, 
											demande::DRIVING_LICENSE_DIRECTORY,
											demande::DRIVING_LICENSE_DB_FIELD,
											$matricule);
											
		$this->proofOfResidence->loadFromPost(demande::PROOF_OF_RESIDENCE_FORM_FIELD, 
											  util::$ALLOWED_EXTENSIONS, 
											  demande::PROOF_OF_RESIDENCE_DIRECTORY,
											  demande::PROOF_OF_RESIDENCE_DB_FIELD,
											  $matricule);
		
		$this->car1 = new car(1);
		$this->car2 = new car(2);
		
		$result = $this->database->requete("SELECT * FROM st_demande WHERE matricule = '".$matricule."'");
		
		if(mysql_num_rows($result) == 0)
		{
			$this->car1->getDataFromPost($postData);
			$this->car2->getDataFromPost($postData);
		}
		else 
		{
			$resultsArray = mysql_fetch_array($result);
			
			$this->car1->getDataFromPost($postData, $resultsArray[demande::CAR1_DB_FIELD]);
			$this->car2->getDataFromPost($postData, $resultsArray[demande::CAR2_DB_FIELD]);
		}

		$this->isDataFromPost = true;
	}
	
	public function saveToDatabase($matricule)
	{
		try
		{
			$this->database->beginTransaction();
			
			$result = $this->database->requete("SELECT * FROM st_demande WHERE matricule = '".$matricule."'");
			
			$hasDemandInDatabase = (mysql_num_rows($result) != 0);
			$resultsArray = mysql_fetch_array($result);
			
			// Saving first car
			$firstCarId = $resultsArray[demande::CAR1_DB_FIELD];
			$doesFirstCarExistInDatabse = isset($firstCarId);			
			$this->car1->saveToDatabase($firstCarId, $doesFirstCarExistInDatabse);

			// Saving second car
			$secondCarId = $resultsArray[demande::CAR2_DB_FIELD];
			$doesSecondCarExistInDatabse = isset($secondCarId);
			
			// Trip info
			$this->tripInfo = new tripInfo($this->userData->getAddress(), $this->userData->getCity(), $this->userData->getZipCode());
			$this->tripInfo->computeValues();
			$this->tripInfo->saveToDatabase($resultsArray[demande::TRIP_DB_FIELD]);

            $save_car2 = false;
			if($this->car2->hasValuesInAtLeastOneField()) 
			{
                $this->car2->saveToDatabase($secondCarId, $doesSecondCarExistInDatabse);
                $save_car2 = true;
            }
			
			$query = "";
			
			if (!$hasDemandInDatabase) 
			{
	        	$this->creationDate = date("Y-m-d");
				$this->modificationDate = date("Y-m-d");
				
				$this->drivingLicense->saveToServer($resultsArray[demande::DRIVING_LICENSE_DB_FIELD]);
				$this->proofOfResidence->saveToServer($resultsArray[demande::PROOF_OF_RESIDENCE_DB_FIELD]);

                if($save_car2) {
                    $car2_field = ",".demande::CAR2_DB_FIELD;
                    $car2_value = "','".util::cleanup($this->car2->getId());
                }
	        	$query = "INSERT INTO st_demande 
	        			 (".demande::MATRICULE_DB_FIELD.",
	        			  ".demande::CREATION_DATE_DB_FIELD.",
	        			  ".demande::MODIF_DATE_DB_FIELD.",
	        			  ".demande::PAYMENT_METHOD_DB_FIELD.",
	        			  ".demande::CARPOOLING_DB_FIELD.",
	        			  ".demande::DETAILS_DB_FIELD.",
	        			  ".demande::CARPOOLING_OTHERS_DB_FIELD.",
	        			  ".demande::DRIVING_LICENSE_DB_FIELD.",
	        			  ".demande::PROOF_OF_RESIDENCE_DB_FIELD.",
	        			  ".demande::TRIP_DB_FIELD.",
	        			  ".demande::CAR1_DB_FIELD
	        			  .$car2_field.") 
	        			 VALUES
	        			 ('".util::cleanup($matricule)."',
	        			  '".util::cleanup($this->creationDate)."',
	        			  '".util::cleanup($this->modificationDate)."',
	        			  '".util::cleanup($this->paymentMethod)."',
	        			  '".util::cleanup($this->carpooling)."',
	        			  '".util::cleanup($this->details)."',
						  '".util::cleanup($this->carpoolingOthers)."',
						  '".util::cleanup($this->drivingLicense->getOutputLocation())."',
						  '".util::cleanup($this->proofOfResidence->getOutputLocation())."',
						  '".util::cleanup($this->tripInfo->getId())."',
                          '".util::cleanup($this->car1->getId())
                          .$car2_value."')";
	        }
			else 
			{
				$this->modificationDate = date("Y-m-d");
				
				// Server and database URLs for this file may differ at this point, in which case we update the DB
				$licenseHasChangedOnServer = $this->drivingLicense->saveToServer($resultsArray[demande::DRIVING_LICENSE_DB_FIELD]);
				$licenseColumnString = $licenseHasChangedOnServer ? demande::DRIVING_LICENSE_DB_FIELD." = " : "";
				$licenseValuesString = $licenseHasChangedOnServer ? "'".util::cleanup($this->drivingLicense->getOutputLocation())."', " : "";
				
				// Server and database URLs for this file may differ at this point, in which case we update the DB
				$proofOfResidenceHasChangedOnServer = $this->proofOfResidence->saveToServer($resultsArray[demande::PROOF_OF_RESIDENCE_DB_FIELD]);
				$proofOfResidenceColumnString = $proofOfResidenceHasChangedOnServer ? demande::PROOF_OF_RESIDENCE_DB_FIELD." = " : "";
				$proofOfResidenceValuesString = $proofOfResidenceHasChangedOnServer ? "'".util::cleanup($this->proofOfResidence->getOutputLocation())."', " : "";
                
                if($save_car2) {
                    $car2_field = ",".demande::CAR2_DB_FIELD;
                    $car2_value = " = '".util::cleanup($this->car2->getId())."'";
                }
				$query = "UPDATE st_demande
						  SET
	        			  ".demande::MODIF_DATE_DB_FIELD." = '".util::cleanup($this->modificationDate)."',
	        			  ".demande::PAYMENT_METHOD_DB_FIELD." = '".util::cleanup($this->paymentMethod)."',
	        			  ".$licenseColumnString.$licenseValuesString."
	        			  ".$proofOfResidenceColumnString.$proofOfResidenceValuesString."
	        			  ".demande::CARPOOLING_DB_FIELD." = '".util::cleanup($this->carpooling)."',
	        			  ".demande::DETAILS_DB_FIELD." = '".util::cleanup($this->details)."',
                          ".demande::CARPOOLING_OTHERS_DB_FIELD." = '".util::cleanup($this->carpoolingOthers)."',
	        			  ".demande::CAR1_DB_FIELD." = '".util::cleanup($this->car1->getId())."'
	        			  ".$car2_field.$car2_value."  
	        			  WHERE 
	        			  matricule = '".$matricule."'";
			}
			
			$this->database->requete($query);
			$this->database->commitTransaction();
		}
		catch(Exception $e)
		{
			$this->database->abortTransaction();
			return false;
		}
		
		return true;
	}
	
	public function refreshStatus()
	{
		$this->status->loadStatusData($this->matricule);
	}

	public function cancelDemand()
	{
		$this->status->cancel();
	}
	
	public function reactivateDemand()
	{
		$this->status->reactivate();
	}
	
	public function getDetails()
	{
		return $this->details;	
	}
	
	public function getStatus()
	{
		return $this->status;
	}
	
	public function getPaymentMethod()
	{
		return $this->paymentMethod;
	}

    public function getPaymentMethodString() {
        switch($this->paymentMethod) {
            case DEMANDE_TYPE_COMPTANT: return "Comptant";
            case DEMANDE_TYPE_CHEQUE: return "Cheque";
            case DEMANDE_TYPE_MANDAT: return "Mandat";
            case DEMANDE_TYPE_INTERAC: return "Interac";
            case DEMANDE_TYPE_VISA: return "Visa";
            case DEMANDE_TYPE_MC: return "Mastercard";
            default: return "null";
        }
    }

    public function getModificationDate() {
       return $this->modificationDate;
    }

	public function getCreationDate() {
       return $this->creationDate;
    }

	public function getLicense()
	{
		return $this->drivingLicense;
	}
	
	public function getResidenceProof()
	{
		return $this->proofOfResidence;
	}
	
	public function isCarpooling()
	{
		return $this->carpooling;
	}
	
	public function isCarpoolingOthers()
	{
		return $this->carpoolingOthers;
	}
	
	public function getFirstCar()
	{
		return $this->car1;	
	}
	
	public function getSecondCar()
	{
		return $this->car2;		
	}
	
	
	public function getUserData()
	{
		return $this->userData;
	}
	
	public function getTripInfo()
	{
		return $this->tripInfo;		
	}
	
	public function hasExistingDemandInDB()
	{
		
		return $this->hasExistingDemandInDatabase;
	}
	
	public function isValid()
	{
		$isValid = true;
		$isValid &= !($this->hasLicenseError());
		$isValid &= !($this->hasPaymentError());
		$isValid &= !($this->hasResidenceError());	
		$isValid &= $this->car1->isValid();
		$isValid &= $this->car2->isValid();
		
		return $isValid;
	}
	
	public function getPaymentErrorMessage()
	{
		return ($this->hasPaymentError() && $this->isDataFromPost) ? "Veuillez choisir une méthode de paiement": "" ;
	}
	
	public function getLicenseErrorMessage()
	{
		return $this->hasLicenseError() && $this->isDataFromPost ? $this->drivingLicense->getErrorMessage() : "" ;
	}
	
	public function getResidenceErrorMessage()
	{
		return $this->hasResidenceError() && $this->isDataFromPost ?  $this->proofOfResidence->getErrorMessage() : "" ;
	}
	
	private function hasPaymentError()
	{
		return ($this->paymentMethod < 1 || $this->paymentMethod > 6);	
	}
	
	private function hasLicenseError()
	{
		return (!isset($this->drivingLicense) || !$this->drivingLicense->isValid() ) ;	
	}
	
	private function hasResidenceError()
	{
		return (!isset($this->proofOfResidence) || !$this->proofOfResidence->isValid() );	
	}

} /* end of class demande */

?>
