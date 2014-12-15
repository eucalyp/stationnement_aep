<?php

/**
 *  class.demandStatus.php
 *	@author Jean-Francois Levesque, <jf.levesque@step.polymtl.ca>
 * 	@author Philippe Gref-Viau, <pgrefviau@gmail.com>
 */   
 
     /*****************************************************************************
    Le script suivant permet de récupérer la distance et le temps de trajet entre une information suffisament précise pour géolocaliser 
    la position (addresse, code postal, etc.) et l'école Polytechnique de Montréal.nnée 

    La requête http faite est envoyée aux serveurs de google qui répond en envoyant un fichier json avec toutes les informations de
    trajet. On récupère alors celles qui nous intéresse.

    Documentation de l'API html de Google : https://developers.google.com/maps/documentation/directions/
    Limites : le nombre de requête par jour est limité à 2500 / 4 (transit mode) = 625 requêtes / jour (voir Usage Limits dans la doc)
   *********************************************************************************/

 require_once ('class.database.php'); 
     
class tripInfo
{
	
	const ID_DB_FIELD = "id";
	const DISTANCE_DB_FIELD = "distance";
	const DURATION_DB_FIELD = "duration";
	const LONGITUDE_DB_FIELD = "longitude";
	const LATITUDE_DB_FIELD = "latitude";
 	const INFO_VALID_DB_FIELD = "infosValid";
	const SCORE_DB_FIELD = "score";
	
	const MODE_TRANSIT = "transit";
	const MODE_DRIVING = "driving";
	
 	private $address;
 	private $city;
	private $zipCode;
	private $tripId;
	private $tripInfoData = null;

	private $errMessage = "";
	
 	public function tripInfo($address =null, $city=null, $zipCode=null)
	{
		if(isset($address) && isset($city) && isset($zipCode))
		{		
			// Adresse de l'étudiant, format : 111+rue+Machin+Montréal+QC (pas d'espaces)
			$this->address = rawurlencode(mb_convert_encoding($address." ".$city." "."QC", 'UTF-8'));
			$this->zipCode = rawurlencode(mb_convert_encoding($zipCode, 'UTF-8'));
		}
	}
	public function getTripInfoFromDb($tripId)
	{
		if(!isset($tripId))
			return;
		
		$this->tripId = $tripId;
		$database = database::instance();
		$result = $database->requete("SELECT * FROM st_trip WHERE ".tripInfo::ID_DB_FIELD." = '$this->tripId' ");
		$resultsArray = mysql_fetch_array($result);
		
		$this->tripInfoData = new tripInfoData();
		$this->tripInfoData->loadFromValues($resultsArray[tripInfo::DISTANCE_DB_FIELD], 
											$resultsArray[tripInfo::DURATION_DB_FIELD], 
											$resultsArray[tripInfo::LATITUDE_DB_FIELD], 
											$resultsArray[tripInfo::LONGITUDE_DB_FIELD]);
	}
	
	public function computeValues()
	{
		$drivingTripInfo = $this->fetchMapsData(tripInfo::MODE_DRIVING, $this->address);
		$transitTripInfo = $this->fetchMapsData(tripInfo::MODE_TRANSIT, $this->address);
		
		if(!$drivingTripInfo->isValid() && !$transitTripInfo->isValid())
		{
			$drivingTripInfo = $this->fetchMapsData(tripInfo::MODE_DRIVING, $this->zipCode);
			$transitTripInfo = $this->fetchMapsData(tripInfo::MODE_TRANSIT, $this->zipCode);
		}
		
		if(!$drivingTripInfo->isValid() && !$transitTripInfo->isValid())
		{
			$this->tripInfoData = new tripInfoData();
		}
		else 
		{				
			$this->tripInfoData = $this->chooseMin($drivingTripInfo, $transitTripInfo);	
		}		

	}
	
	private function chooseMin(tripInfoData $a,tripInfoData $b)
	{
		if($a->isValid() && !$b->isValid())
		{
			return $a;	
		}
		
		if(!$a->isValid() && $b->isValid())
		{
			return $b;
		}
		
		if($a->compareTo($b) < 0)
		{
			return $a;
		}
		else
		{
			return $b;
		}
	}
	
	private function fetchMapsData($mode,$origin)
	{
		$baseQueryUrl = "http://maps.googleapis.com/maps/api/directions/json?mode=".$mode."&departure_time=1349092800&sensor=false&destination=ecole+polytechnique+de+montreal&origin=";
		$query = $baseQueryUrl.$origin;
		
	    $data = file_get_contents($query); // Données json reçues
	    $json = json_decode($data, true); // On décode les données (le résultat est donné sous forme de tableau)
		
		return new tripInfoData($json);	
	}

	public function saveToDatabase($tripId)
	{
		$database = database::instance();
		$this->tripId = $tripId;
			
		if(isset($this->tripId))
		{
		
			$database->requete(	"UPDATE st_trip 
					SET 
					".tripInfo::DISTANCE_DB_FIELD." = '".$this->tripInfoData->getDistance()."',
					".tripInfo::DURATION_DB_FIELD." = '".$this->tripInfoData->getDuration()."',
					".tripInfo::SCORE_DB_FIELD." = '".$this->tripInfoData->getScore()."',
					".tripInfo::LATITUDE_DB_FIELD." = '".$this->tripInfoData->getLatitude()."',
					".tripInfo::LONGITUDE_DB_FIELD." = '".$this->tripInfoData->getLongitude()."',
					".tripInfo::INFO_VALID_DB_FIELD." = '".$this->tripInfoData->isValid()."' 
					WHERE ".tripInfo::ID_DB_FIELD." = '".$this->tripId."' ");	
		}
		else 
		{
			$database->requete(	"INSERT INTO st_trip 
								(".tripInfo::ID_DB_FIELD.",
								 ".tripInfo::DISTANCE_DB_FIELD.",
								 ".tripInfo::DURATION_DB_FIELD.",
								 ".tripInfo::SCORE_DB_FIELD.",	 
								 ".tripInfo::LATITUDE_DB_FIELD.",
								 ".tripInfo::LONGITUDE_DB_FIELD.",
								 ".tripInfo::INFO_VALID_DB_FIELD.")
								VALUES 
								(NULL, 
								'".$this->tripInfoData->getDistance()."',
								'".$this->tripInfoData->getDuration()."',
								'".$this->tripInfoData->getScore()."',
								'".$this->tripInfoData->getLatitude()."',
								'".$this->tripInfoData->getLongitude()."',
								'".$this->tripInfoData->isValid()."')", 
								true, 
								true);
								
			$this->tripId = $database->dernierInsertId;							
		}
			
	}
	
	public function getId()
	{
		return $this->tripId;
	}
	
	public function getDistance()
	{
		return $this->tripInfoData->getDistance();
	}
	
	public function getDuration()
	{
		return $this->tripInfoData->getDuration();
	}		
	
	public function getDurationInMinutes()
	{
		return round(($this->tripInfoData->getDuration())/60);
	}	
	
	public function getLatitude()
	{
		return $this->tripInfoData->getLatitude();
	}
	
	public function getLongitude()
	{
		return $this->tripInfoData->getLongitude();
	}	
	
	public function isValid()
	{
		return $this->tripInfoData->isValid();
	}
	
	public function getErrorMessage()
	{
		return $this->errMessage;
	}
} 




class tripInfoData
{
	private $tripDuration = -1;
	private $tripDistance = -1;
	private $latitude = 0;
	private $longitude = 0;
	private $isValid = true;
	
	public function tripInfoData($json = null)
	{
		if(isset($json) && $json['status'] == "OK" )
		{
			$this->tripDuration = $json["routes"][0]["legs"][0]["duration"]["value"]; // in seconds
			$this->tripDistance  = $json["routes"][0]["legs"][0]["distance"]["value"]; // in meters
			$this->latitude = $json["routes"][0]["legs"][0]["start_location"]["lat"];
			$this->longitude = $json["routes"][0]["legs"][0]["start_location"]["lng"];
			
			$this->isValid = true;	
		}
		else 
		{
			$this->isValid = false;	
		}
	}
	
	public function loadFromValues($distance, $duration, $latitude, $longitude)
	{
		$this->tripDistance = $distance;
		$this->tripDuration = $duration;
		$this->latitude = $latitude;
		$this->longitude = $longitude;
		$this->isValid = true;
	}
	
	public function compareTo(tripInfoData $other)
	{
		return $this->getScore() - $other->getScore();
	}
	
	public function getDuration()
	{
		return $this->tripDuration;
	}
	
	public function getDistance()
	{
		return $this->tripDistance;
	}
	
	public function getLatitude()
	{
		return $this->latitude;
	}
	
	public function getLongitude()
	{
		return $this->longitude;
	}
	
	public function getScore()
	{
		return $this->tripDistance * $this->tripDuration;
	}
	
	public function isValid()
	{
		return $this->isValid;
	}
}
?>
