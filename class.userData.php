<?php

error_reporting(E_ALL);

/**
 *  class.userData.php
 *
 * @author Philippe Gref-Viau, <pgrefviau@gmail.com>
 */

    require_once("class.database.php");
	require_once("class.authentification.php");
    require_once("class.util.php");
	
class userData
{
	private $matricule = null;
	private $database = null;
	private $auth = null;
	
	const MATRICULE_DB_FIELD = "matricule";
	const EMAIL_DB_FIELD = "email";
	const FIRST_NAME_DB_FIELD = "firstname";
	const LAST_NAME_DB_FIELD = "lastname";
	const PHONE_DB_FIELD = "tel_1";
	const ADDRESS_DB_FIELD = "address";
	const CITY__DB_FIELD = "city";
	const ZIP_CODE_DB_FIELD = "zipcode";
	
	const EMAIL_TAG = "Adresse courriel";
	const FIRST_NAME_TAG = "Prénom";
	const LAST_NAME_TAG = "Nom";
	const PHONE_TAG = "Numéro de téléphone";
	const ADDRESS_TAG = "Adresse";
	const CITY_TAG = "Ville";
	const ZIP_CODE_TAG = "Code postal";

	private $email = "";
	private $firstName = "";
	private $lastName = "";
	private $phone = "";
	private $address = "";
	private $city = "";
	private $zipCode = "";
	private $exist = false;
	
	public function userData($matricule)
	{
		$this->database = database::instance();	
		$this->auth = authentification::instance();
        $this->matricule = $matricule;
        $this->getUserData();
	}
	
	public function getUserData()
	{		
		if(empty($this->database) ) {
			$this->exist = false;
			return $this->exist;
		}
		
		if(empty($this->matricule)) {
			$this->exist = false;
			return $this->exist;
		}
		
		$result = $this->database->requete("SELECT * FROM st_user_metadata WHERE matricule = '$this->matricule'");
		$resultDataArray = mysql_fetch_array($result);

		$this->email = $resultDataArray[userData::EMAIL_DB_FIELD];
		$this->firstName = $resultDataArray[userData::FIRST_NAME_DB_FIELD];
		$this->lastName = $resultDataArray[userData::LAST_NAME_DB_FIELD];
		$this->phone = $resultDataArray[userData::PHONE_DB_FIELD];
		$this->address = $resultDataArray[userData::ADDRESS_DB_FIELD];
		$this->city = $resultDataArray[userData::CITY__DB_FIELD];
		$this->zipCode = $resultDataArray[userData::ZIP_CODE_DB_FIELD];
		 
		$this->exist = true;
		return $this->exist;			
	}
    
	public function getCurrentUserData()
	{		
		if(!isset($this->auth) )
			return false;
		
    	$this->matricule =  $this->auth->getUsager();
		return $this->getUserData();
	}
	
	public function getExist() {
		return $this->exist;
	}
 
    public function getMatricule() {
        return $this->matricule;
    }

	public function getEmail()
	{
		return $this->email;
	}
	
	public function getFirstName()
	{     
		return util::cleanUTF8($this->firstName);
	}
	public function getLastName()
	{
		return util::cleanUTF8($this->lastName);
	}
	public function getPhone()
	{
		return $this->phone;
	}
	public function getAddress()
	{
		return util::cleanUTF8($this->address);
	}
	public function getCity()
	{
		return util::cleanUTF8($this->city);
	}
	public function getZipCode()
	{
		return $this->zipCode;
	}
	
}	
	
?>
