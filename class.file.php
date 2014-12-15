<?php

/**
 *  class.car.php
 *
 * @author Philippe Gref-Viau, <pgrefviau@gmail.com>
 */


require_once("class.database.php");

// CLASS file 
////////////////////////////////////////////
abstract class file
{
	// Indicates if the file can be uploaded in its current state
	protected $isValid = true;
	
	// If true, was loaded form a server url. 
	// If false, no existingfile (with content) was loaded
	protected $wasLoadedFromUrl = false;
	
	protected $isModified = true;	
	protected $extension;
	protected $type;
	protected $errorMessage;
	protected $output_location;
	
	
	public function file()
	{
		 $this->isValid = false;
	}
	

	abstract protected function loadFromServer($fileEntryId, $fileDbFieldName);
	
	abstract protected function hasExistingEntryInDatabase($fileEntryRowId);
	
	protected function loadFromUrl($url)
	{
		// Check if file exists	
		if(isset($url) && file_exists(util::cleanUTF8($url)))
		{
			$this->wasLoadedFromUrl = true;
			$this->output_location = $url;
		}
		else
		{
			$this->wasLoadedFromUrl = false;
		}
		
		 $this->isValid = true;	
	}
	
	
	// 
	public function loadFromPost($input_name, $allowed_exts, $output_directory, $fileDbFieldName, $fileEntryRowId)
	{
		$this->isValid = true;
		$this->input_name = $input_name;
		
		if(!isset($_FILES[$this->input_name]["name"]) || empty($_FILES[$this->input_name]["name"]))
		{
			// This is a demand update and the file wasn't modified
			// We load up the file from the server instead
			if($this->hasExistingEntryInDatabase($fileEntryRowId))
			{
				$this->isModified = false;
				$this->loadFromServer($fileEntryRowId, $fileDbFieldName);
				return;	
			}
			else
			{
				$this->registerError("Vous devez obligatoirement fournir un fichier pour ce champ");
				return;
			}
		}
		
        $temp = explode(".", $_FILES[$this->input_name]["name"]);
        $this->extension = end($temp);
		$this->type = $_FILES[$this->input_name]["type"];
		
        if ((( $this->type == "image/gif")
            || ($this->type == "image/jpeg")
            || ($this->type == "image/jpg")
            || ($this->type == "image/pjpeg")
            || ($this->type == "image/x-png")
            || ($this->type == "image/png")))
	      {
	            if ($_FILES[$this->input_name]["error"] > 0) 
	            {
	            	$this->registerError("Erreur lors de l'envoi du fichier.");
	                return ;
                } else if($_FILES[$this->input_name]["size"] > (1024*1024*7)) {
                    $this->registerError("Le fichier est trop grand");
                    return;
          }
	            else 
	            {
	                $uniq_id = uniqid();
	                $output_name = $uniq_id.$_FILES[$this->input_name]["name"];
	                $this->output_location = $output_directory.$output_name;
	                 if (file_exists($this->output_location)) 
	                 {
	                 	$this->registerError("Le fichier existe déjà");
	                     return;
	                 }				
	            }
	      } 
          else 
          {
				$errMessage = "Seuls les fichiers d'extension";
				
          		foreach ($allowed_exts as $key => $value) 
					$errMessage .= " ".$value.","; 	  
					
				$errMessage .= " sont permis";
				
				$this->registerError($errMessage);
              
              return;
          }
	}
	
	
	public function saveToServer($currentFileUrl)
	{
		if(!$this->isValid)
			return false;
			
		// No modif: no need to change file on server	
		if(!$this->isModified)	
			return false;
		
		//Delete previous file (if any)
		$currentFileUrl = realpath($currentFileUrl);	
		if(is_readable($currentFileUrl))
		{
			unlink($currentFileUrl);
		}
				
		move_uploaded_file($_FILES[$this->input_name]["tmp_name"], util::cleanUTF8($this->output_location));	
		
		return true;
	}
	
	public function isLoadedFromUrl()
	{
		return $this->wasLoadedFromUrl;
	}
	
	public function getOutputLocation()
	{
		return $this->output_location;
	}

	private function registerError($err)
	{
		$this->errorMessage = $err;
		$this->isValid = false;
	}
	
	public function isValid()
	{
		return $this->isValid;
	}
	
	public function getErrorMessage()
	{
		return $this->isValid ? "" : $this->errorMessage;	
	}
}

// CLASS carTableFile
////////////////////////////////////////////
class carTableFile extends file
{
	public function hasExistingEntryInDatabase($carId)
	{
		return isset($carId) && util::hasExistingCarInDatabase($carId);	
	}
	
	public function loadFromServer($carId, $fileDbFieldName)
	{
		$database = database::instance();
		$results = $database->requete("SELECT * from st_car WHERE id = '$carId'");
		$resultsArray = mysql_fetch_array($results);
		
		$url = $resultsArray[$fileDbFieldName];
		$this->loadFromUrl($url);	
	}
}

// CLASS demandTableFile
////////////////////////////////////////////
class demandTableFile extends file
{
	public function hasExistingEntryInDatabase($matricule)
	{
		return isset($matricule) && util::hasExistingDemandInDatabase();	
	}
	
	public function loadFromServer($matricule, $fileDbFieldName)
	{
		$database = database::instance();
		$results = $database->requete("SELECT * from st_demande WHERE matricule = '$matricule'");
		$resultsArray = mysql_fetch_array($results);
			
		$url = $resultsArray[$fileDbFieldName];
		$this->loadFromUrl($url);	
	}
}

?>
