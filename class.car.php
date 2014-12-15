<?php

require_once('class.util.php');
require_once('class.database.php');
require_once('class.file.php');
/**
 *  class.car.php
 *
 * @author Philippe Gref-Viau, <pgrefviau@gmail.com>
 */

class car 
{

	const CAR_ID_DB_FIELD = "id";
	const MODEL_DB_FIELD = "brand";
	const COLOR_DB_FIELD = "color";
	const YEAR_DB_FIELD = "year";
	const LICENSE_DB_FIELD = "license_plate";
	const INSURANCE_DB_FIELD = "insurance_url";
    const ELECTRIC_DB_FIELD = "electric";

	const MODEL_FORM_FIELD = "brand";
	const COLOR_FORM_FIELD = "color";
	const YEAR_FORM_FIELD = "year";
	const LICENSE_FORM_FIELD = "license_plate";
	const INSURANCE_FORM_FIELD = "insurance_file";
    const ELECTRIC_FORM_FIELD = "electric";

	const MODEL_TAG = "Marque";	
	const COLOR_TAG = "Couleur";
	const YEAR_TAG = "Année";
	const LICENSE_TAG = "Preuve plaque immatriculation";
	const INSURANCE_TAG = "Preuve d'assurance";
	const ELECTRIC_TAG = "Voiture hybride ou électrique branchable";
	const OPTIONNAL_CAR_INDEX = 2;
    
    const INSURANCE_DIRECTORY = "proofs/insurance/";    

	private $index;
	
	private $id = null;
	private $model = "";
	private $color = "";
	private $year = "";
	private $license = "";
	private $insurance = null;
    private $isDataFromPost = false;
    private $isElectric = false;
	private $existsInDb = false;
	
	public function car($index)
	{
		$this->index = $index;
		$this->insurance = new carTableFile();			
	}
	
	// Use thi version if the car is KNOWN to EXIST in the DB (if we have its id)
	// Use this version if the car is KNOWN to be NONEXISTENT in the DB (if we don't have an id yet)
	public function getDataFromPost($postData, $carId = null)
	{
		$this->isDataFromPost = true;	
		
		if(isset($carId))
			$this->id = $carId;
		
		$this->model = util::getParam($postData, $this->getModelParamName());		
		$this->color = util::getParam($postData, $this->getColorParamName());		
		$this->year = util::getParam($postData, $this->getYearParamName());		
		$this->license = util::getParam($postData, $this->getLicenseParamName());			
		
		if(!$this->isOptionnalCar() || ($this->isOptionnalCar() && $this->hasValuesInAtLeastOneField()))
		{
			$this->insurance->loadFromPost($this->getInsuranceParamName(), 
										   util::$ALLOWED_EXTENSIONS, 
										   car::INSURANCE_DIRECTORY,
										   car::INSURANCE_DB_FIELD,
										   $carId);
        }
        $this->isElectric = util::getParam($postData, $this->getElectricParamName());
	}

	
	public function getDataFromDB($id,$matricule)
	{
		$this->isDataFromPost = false;
		
		if(!isset($id))
			return;
		
		$this->id = $id;
		
		$database = database::instance();
		$results = $database->requete(	"SELECT * FROM st_car WHERE ".car::CAR_ID_DB_FIELD." = '".$this->id."'");
		
		if(mysql_num_rows($results) == 0 )
			return;
		
		$resultsArray = mysql_fetch_array($results);
		$this->existsInDb = true;
		
		$this->model = $resultsArray[car::MODEL_DB_FIELD];		
		$this->color = $resultsArray[car::COLOR_DB_FIELD];	
		$this->year = $resultsArray[car::YEAR_DB_FIELD];
		$this->license = $resultsArray[car::LICENSE_DB_FIELD];
        $this->insurance->loadFromServer($this->id,car::INSURANCE_DB_FIELD);
        $this->isElectric = $resultsArray[car::ELECTRIC_DB_FIELD];
	}
	
	public function isValid()
	{
		$isValid = true;
		// We only do the error checking if at least one field is set and if this is the second (optionnal) car	
		if($this->index !=  car::OPTIONNAL_CAR_INDEX|| ($this->index == car::OPTIONNAL_CAR_INDEX && $this->hasValuesInAtLeastOneField()))
		{
			$isValid &= !($this->hasModelError());
			$isValid &= !($this->hasColorError());
			$isValid &= !($this->hasYearError());
			$isValid &= !($this->hasLicenseError());
			$isValid &= !($this->hasInsuranceError());
		}
		return $isValid;
	}
     
	public function isOptionnalCar()
	{
		return $this->index == car::OPTIONNAL_CAR_INDEX;
	}
	
	public function hasValuesInAtLeastOneField()
    {
		return 	!empty($this->model) || 
				!empty($this->color) || 
				!empty($this->year) || 
				!empty($this->license);
	}
	
	public function saveToDatabase($id, $doesCarExistInDatabase)
	{
		$database = database::instance();
	
		if($doesCarExistInDatabase) 
		{
			$this->id = $id;
			
			// TODO: POTENTIALLY PUT THIS IN FILE SUBCLASS METHOD SAVETOSERVER	
			$results =  $database->requete("SELECT * FROM st_car WHERE ".car::CAR_ID_DB_FIELD." = '".$this->id."'");
			$resultsArray = mysql_fetch_array($results);
			
			// Server and database URLs may differ at this point, in which case we update the DB
			$insuranceHasChangedOnServer = $this->insurance->saveToServer($resultsArray[car::INSURANCE_DB_FIELD]);
			$insuranceColumnString = $insuranceHasChangedOnServer ? car::INSURANCE_DB_FIELD." = " : "";
			$insuranceValuesString = $insuranceHasChangedOnServer ? "'".util::cleanup($this->insurance->getOutputLocation())."', " : "";

			$database->requete("UPDATE st_car
							  SET  			  
		        			  ".car::MODEL_DB_FIELD." = '".util::cleanup($this->model)."',
		        			  ".car::COLOR_DB_FIELD." = '".util::cleanup($this->color)."',
		        			  ".car::YEAR_DB_FIELD." = '".util::cleanup($this->year)."',
		        			   ".$insuranceColumnString.$insuranceValuesString."
                               ".car::LICENSE_DB_FIELD." = '".util::cleanup($this->license)."',
                             ".car::ELECTRIC_DB_FIELD." = '".util::cleanup($this->isElectric)."' 
		        			  WHERE 
		        			  ".car::CAR_ID_DB_FIELD." = '".$this->id."'");
		}
		else 
		{
			
			$this->insurance->saveToServer(car::INSURANCE_DB_FIELD);
			
			$database->requete("INSERT INTO st_car 
								(".car::CAR_ID_DB_FIELD.",
								".car::MODEL_DB_FIELD.",
								".car::COLOR_DB_FIELD.",
								".car::YEAR_DB_FIELD.",
								".car::INSURANCE_DB_FIELD.",
                                ".car::LICENSE_DB_FIELD.",
                                ".car::ELECTRIC_DB_FIELD.") 
								VALUES
								(NULL, 
								 '".$this->model."',
								 '".$this->color."', 
								 '".$this->year."',
								 '".$this->insurance->getOutputLocation()."',
                                 '".$this->license."',
                                 '".$this->isElectric."')",
								 true, 
								 true);
				
			$this->id = $database->dernierInsertId;	
		}
				 
		return $this->id;					 
	}
	
	private function hasModelError()
	{
		$error = !isset($this->model) || empty($this->model) ;
		return $this->isOptionnalCar() ? $error && $this->hasValuesInAtLeastOneField() : $error;  
	}
	
	private function hasColorError()
	{
		$error = !isset($this->color) || empty($this->color) ;
		return $this->isOptionnalCar() ? $error && $this->hasValuesInAtLeastOneField() : $error;  		
	}
	
	private function hasYearError()
	{
		$error = (empty($this->year) || strlen($this->year) != 4 || !is_numeric($this->year));
		return $this->isOptionnalCar() ? $error && $this->hasValuesInAtLeastOneField() : $error;  		
	}
	private function hasLicenseError()
	{
		$error = !isset($this->license) || !validation::license($this->license);
		return $this->isOptionnalCar() ? ($error && $this->hasValuesInAtLeastOneField()) : $error;
	}
	
	private function hasInsuranceError()
	{
		$error = !isset($this->insurance) || !$this->insurance->isValid();
		return $this->isOptionnalCar() ? ($error && $this->hasValuesInAtLeastOneField()) : $error;
	}

    public function getIsElectric() {
        return $this->isElectric;
    }

	public function getModel()
	{
		return $this->model;
	}
	
	public function getColor()
	{
		return $this->color;
	}
	
	public function getYear()
	{
		return $this->year;
	}
	
	public function getLicense()
	{
		return $this->license;
	}
	
	public function getInsurance()
	{
		return $this->insurance;
	}
	
	public function getId()
	{
			return $this->id;
	}
	
	public function getIndex()
	{
			return $this->index;
	}

	public function existsInDatabase()
	{
		return $this->existsInDb;
	}
	public function getModelErrorMessage()
	{
		return $this->hasModelError() && $this->isDataFromPost ? "Vous devez fournir un modèle" : "";
	}

	public function getColorErrorMessage()
	{
		return $this->hasColorError() && $this->isDataFromPost ? "Vous devez fournir une couleur" : "";
	}
	
	public function getYearErrorMessage()
	{
		return $this->hasYearError() && $this->isDataFromPost ? "Vous devez fournir une année valide" : "";
	}
	
	public function getLicenseErrorMessage()
	{
		return $this->hasLicenseError() && $this->isDataFromPost ? "Vous devez fournir un numéro de license valide" : "";
	}
	
	public function getInsuranceErrorMessage()
	{
		return $this->hasInsuranceError() && $this->isDataFromPost ? $this->insurance->getErrorMessage() : "";
	}
	
	public function getModelParamName()
	{
		return car::MODEL_FORM_FIELD.$this->index;
	}
	
	public function getColorParamName()
	{
		return car::COLOR_FORM_FIELD.$this->index;
	}
	
	public function getYearParamName()
	{
		return car::YEAR_FORM_FIELD.$this->index;
	}
	
	public function getLicenseParamName()
	{
		return car::LICENSE_FORM_FIELD.$this->index;
	}
	
	public function getInsuranceParamName()
	{
		return car::INSURANCE_FORM_FIELD.$this->index;
    }

    public function getElectricParamName() {
        return car::ELECTRIC_FORM_FIELD.$this->index;
    }
}
?>
