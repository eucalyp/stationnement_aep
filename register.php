<?php 

error_reporting(E_ALL);
/**
 *  - register.php
 *
 * @author Philippe Gref-Viau, <pgrefviau@gmail.com>
 */

// Constants
define('MATRICULE_LENGTH', 7);
define('MIN_PASSWORD_LENGTH', 5);


// Imports
require_once("class.database.php");
require_once("class.config.php");
require_once("class.util.php");
require_once("class.validation.php");
require_once("class.tripInfo.php");
require_once("class.demande.php");

$database = database::instance();


$errMessagesArray = array(	"matricule"=>"", 
								"password"=>"", 
								"email" => "",
								"emailconf" => "", 
								"firstName" => "", 
								"lastName" => "", 
								"phone" => "",
								"address" => "", 
								"city" => "", 
								"postalCode" => "");
								
	$matricule = "";
	$password = "";
	$email = "";
	$emailconf = "";
	$firstName = "";
	$lastName = "";
	$phone = "";
	$address = "";
	$city = "";
	$postalCode = "";

$readonly = '';    
$submissionValue = 'register';	
$postType = util::getParam($_POST, 'submissionType');

if(!empty($postType) && ($postType=='register' || $postType=='update'))
{
	$matricule =  util::cleanup($_POST['user']);
	
	if($postType == "register")
		$password = util::cleanup($_POST['passw']);
		
	$email = util::cleanup($_POST['email']);
	$emailconf = util::cleanup($_POST['emailconf']);
	$firstName = util::cleanup($_POST['firstname']);
	$lastName = util::cleanup($_POST['lastname']);
	$phone = util::cleanup($_POST['phone']);
	$address = util::cleanup($_POST['addr']);
	$city = util::cleanup($_POST['city']);
	$postalCode = util::cleanup($_POST['postal']);
	
    $isSubmissionValid = true;
	

	// Username check
	if(empty($matricule) || strlen($matricule) != MATRICULE_LENGTH || !is_numeric($matricule))
	{
		// Must be a valid 7 digit valid number
		$errMessagesArray["matricule"] = "L'identifiant doit être un matricule valide".is_numeric($matricule);
		$isSubmissionValid = false;
	}
	else if($postType == 'register')
	{		
		$usernameCheck = $database->requete("SELECT * FROM st_authentication WHERE matricule = '".$matricule."'");
		if(mysql_num_rows($usernameCheck) != 0)
		{
			
			$errMessagesArray["matricule"] = "Le matricule existe déjà";
			$isSubmissionValid = false;
		}
	}
	
	// Password check
	if($postType=="register" && (empty($password) || strlen($password) < MIN_PASSWORD_LENGTH) )
	{
		// Password must be between x-y characters
		$errMessagesArray["password"] = "Le mot de passse doit au moins comporter ".MIN_PASSWORD_LENGTH." caractères";
		$isSubmissionValid = false;
	}
	
	// Email check
	if(empty($email) || !validation::email($email))
	{
		$errMessagesArray["email"] = "Vous devez fournir une adresse courriel valide";	
		$isSubmissionValid = false;
	}
	else
	{
		if(strcmp( $email, $emailconf ) != 0)
		{
			$errMessagesArray["emailconf"] = "Les deux adresses doivent êtres identiques";
			$isSubmissionValid = false;
		}
	}
	
	// Last name check
	if(empty($lastName))
	{
		$errMessagesArray["lastName"] = "Vous devez fournir une valeur pour ce champ";
		$isSubmissionValid = false;
	}
	
	// First name check
	if(empty($firstName))
	{
		$errMessagesArray["firstName"] = "Vous devez fournir une valeur pour ce champ";
		$isSubmissionValid = false;
	}
	// Phone number check
	if(empty($phone) || !validation::telephone($phone))
	{
		$errMessagesArray["phone"] = "Vous devez fournir un numéro de téléphone valide";
		$isSubmissionValid = false;
	}
	
	// Address check
	if(empty($address))
	{
		$errMessagesArray["address"] = "Vous devez fournir une adresse valide";
		$isSubmissionValid = false;
	}
	// City check
	if(empty($city))
	{
		$errMessagesArray["city"] = "Vous devez fournir un nom de ville";
		$isSubmissionValid = false;
	}
	// Postal check
	if(empty($postalCode) || !validation::codePostal($postalCode))
	{
		$errMessagesArray["postalCode"] = "Vous devez fournir un code postal valide";
		$isSubmissionValid = false;
	}
	
	if($isSubmissionValid)
	{
		// Put user in database
		try
		{
			$database->beginTransaction();
		    if($postType == 'register') 
		    {	
				$database->requete("INSERT INTO st_authentication 
	                                (matricule, password) 
	                                        VALUES ('".$matricule."',
											'".md5($password)."')");
									
				$database->requete(	"INSERT INTO st_user_metadata 
									(matricule,lastname,firstname,address,city,zipcode,tel_1,email)
									VALUES 
									('".$matricule."',
									'".$lastName."',
									'".$firstName."',
									'".$address."',
									'".$city."',
									'".$postalCode."',
									'".$phone."',
									'".$email."')");
										
				$database->commitTransaction();
				
				if(config::SendEmail) 
                {
                    $pregArray = array(
                        array(
                            'key' => "/@@LASTNAME@@/",
                            'value' => $lastName
                        ),
                        array(
                            'key' => "/@@FIRSTNAME@@/",
                            'value' => $firstName  
                        )
                    );
                    util::sendEmail($email, 'email.txt', $pregArray, "Stationnement AEP - Creation de compte");
		        }
		        
				// Automatic log in
		        $objAuth = authentification::instance();
		        $objAuth->verification($matricule,$password);
	            header("Location: demande.php");										
			    exit();					
	        } 
	        else 
	        {
	            $database->requete(	"UPDATE st_user_metadata 
	                				SET 
	                				lastname='".$lastName."',
	                				firstname='".$firstName."',
	                				address='".$address."',
	                				city='".$city."',
	                				zipcode='".$postalCode."',
	                				tel_1='".$phone."',
	                				email='".$email."' 
	                				WHERE matricule='".$matricule."'");
	            
	            updateTripInfo($address,$city,$postalCode,$matricule);
				    				
	            $database->commitTransaction();
	            									
	        }
		} 
		catch(Exception $e)
		{
			$database->abortTransaction();
		}
			
	}
}

require_once('header.php');
require_once('class.authentification.php');
require_once('class.userData.php');
$objAuth = authentification::instance();
$user = new userData($objAuth->getUsager());
if ($user->getCurrentUserData())
{
	$matricule = $user->getMatricule();
	$email = $user->getEmail();
	$firstName = $user->getFirstName();
	$lastName = $user->getLastName();
	$phone = $user->getPhone();
	$address = $user->getAddress();
	$city = $user->getCity();
    $postalCode = $user->getZipCode();
    $submissionValue = 'update';
    $readonly = 'readonly';
}

print('<fieldset  >
		<legend> <h3>Informations</h3> </legend>
		<div style="float:left" >
			<form method="post" name="register">
				<input type="hidden" name="submissionType" value="'.$submissionValue.'"/>');


print(createRegisterTextField('Matricule', 'user', 7, 'user', $matricule, $errMessagesArray['matricule'], $readonly));
if($submissionValue == 'register')
{
	print(createRegisterPasswordField('Choisir mot de passe', 'passw', 128, 'password', "", $errMessagesArray['password'], $readonly));
}
print(createRegisterTextField('Adresse courriel', 'email', 254, 'email', $email,$errMessagesArray['email']));
print(createRegisterTextField('Confirmer adresse courriel', 'emailconf', 254, 'email', "", $errMessagesArray['emailconf']));
print(createRegisterTextField('Nom', 'lastname', 50,'firstname', $lastName, $errMessagesArray['lastName']));
print(createRegisterTextField('Prénom', 'firstname', 50, 'lastname', $firstName, $errMessagesArray['firstName']));
print(createRegisterTextField('Numéro de téléphone', 'phone', 25, 'phonenumber', $phone, $errMessagesArray['phone']));
print(createRegisterTextField('Adresse résidentielle', 'addr', 50, 'address', $address, $errMessagesArray['address']));
print(createRegisterTextField('Ville', 'city', 50, 'city', $city, $errMessagesArray['city']));
print(createRegisterTextField('Code postal (ex: H3T1J4)', 'postal', 8, 'postalcode', $postalCode, $errMessagesArray['postalCode']));

print('		<div class="registerField" style="float: right;margin-top:8 ">
			<input class="searchButton" style="font-size: 16" size="15" value="Soumettre"  type="submit" />
		</div>
	</form>
	</div>
</fieldset>');

readfile('light_footer.html');


function createRegisterTextField($fieldText, $fieldName, $maxLength, $id, $value,$errMessage, $readonly='')
{
	return createRegisterField($fieldText, $fieldName, $maxLength, $id,'text', $value, $errMessage, $readonly);
}

function createRegisterPasswordField($fieldText, $fieldName, $maxLength, $id, $value, $errMessage, $readonly='')
{
	return createRegisterField($fieldText, $fieldName, $maxLength, $id,'password', $value, $errMessage, $readonly);
}

function createRegisterField($fieldText, $fieldName, $maxLength, $id, $type, $value, $errMessage, $readonly)
{
	$asterix = empty($errMessage) ? "" : "*";
	return '<div class="formField" >
				<label>'.$fieldText.'</label> 
				<input class = "formInput" value="'.$value.'" type="'.$type.'" name="'.$fieldName.'" size="8" maxlength="'.$maxLength.'" id="'.$id.'" '.$readonly.'/>
				<label class="formErrorField" >'.$asterix." ".$errMessage.'</label>
			</div>';
}

function updateTripInfo($address, $city, $zipCode,$matricule)
{
	$database = database::instance();
	$results = $database->requete("SELECT ".tripInfo::ID_DB_FIELD." 
								  FROM st_trip INNER JOIN st_demande 
								  WHERE st_demande.".demande::MATRICULE_DB_FIELD." = '".$matricule."' 
								  AND st_trip.".tripInfo::ID_DB_FIELD." = st_demande.".demande::TRIP_DB_FIELD."");
								  
	if(mysql_num_rows($results) == 1)
	{
		$resultsArray = mysql_fetch_array($results);
		$tripId = $resultsArray[tripInfo::ID_DB_FIELD];
		
		$tripInfo = new tripInfo($address,$city,$zipCode);
		$tripInfo->computeValues();
		$tripInfo->saveToDatabase($tripId);
	}							  		
}

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

?>
