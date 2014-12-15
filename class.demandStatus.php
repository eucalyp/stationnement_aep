<?php
    
/**
 *  class.demandStatus.php
 *
 * @author Philippe Gref-Viau, <pgrefviau@gmail.com>
 */    
    
require_once ('class.database.php'); 
require_once ('class.authentification.php'); 
require_once('class.userData.php');
class demandStatus
{
	const WAITING_STATUS = 0;
	const PROOF_OK_STATUS = 1;
	const REFUSED_STATUS = 2;
	const ACCEPTED_STATUS = 3;
	const PAID_STATUS = 4;
	const PRINTED_STATUS = 5;
	const INVALID_PROOF_STATUS = 6;
	const CANCELED_STATUS = 7;
	
	const STATUS_ID_DB_FIELD = "statusId";
	const STATUS_NAME_DB_FIELD = "name";
	const STATUS_EMAIL_DB_FIELD = "email";
	const STATUS_DESC_DB_FIELD = "description";
	
	private $statusId = demandStatus::WAITING_STATUS;
	private $statusName;
	private $statusDescription;
	private $statusDetails;
	private $statusEmail;
	
	private $hasStatus = false;
	private $matricule;
	
	
    public function __construct() {
        $a = func_get_args();
        $i = func_num_args();
        if(method_exists($this, $f='__construct'.$i)) {
            call_user_func_array(array($this, $f), $a);
        } else {
            $auth = authentification::instance();
            $this->loadStatusData($auth->getUsager());
        }
    }

    public function __construct1($matricule) {
        $this->loadStatusData($matricule);
    }

	public function loadStatusData($matricule, $includeEmail = false)
	{
		$database = database::instance();
		$this->matricule = $matricule;
		//$queryParams = $includeEmail ? "*": "(".demandStatus::STATUS_NAME_DB_FIELD.",".demandStatus::STATUS_DESC_DB_FIELD.")";
		
		$results = $database->requete("SELECT * 
									  FROM st_status INNER JOIN st_demande 
									  WHERE st_demande.matricule = '$this->matricule' 
									  AND st_status.statusId = st_demande.status");
		
		if(mysql_num_rows($results) == 0)
		{
			$this->hasStatus = false;
			return;	
		}
		
		$this->hasStatus = true;
		
		$resultsArray = mysql_fetch_array($results);
		
		$this->statusId =  $resultsArray[demande::STATUS_DB_FIELD];
		$this->statusName = $resultsArray[demandStatus::STATUS_NAME_DB_FIELD];
		$this->statusDescription = $resultsArray[demandStatus::STATUS_DESC_DB_FIELD];
		$this->statusEmail = $resultsArray[demandStatus::STATUS_EMAIL_DB_FIELD];
		$this->statusDetails = $resultsArray[demande::STATUS_DETAILS_DB_FIELD];
	}
	
	public function reactivate()
    {
		if(!$this->hasStatus)
			return;
		
		$this->changeStatusTo(demandStatus::WAITING_STATUS);
	}
	
	public function cancel()
	{
		if($this->isCanceled() || !$this->hasStatus) {
            return;
        }
		
		$this->changeStatusTo(demandStatus::CANCELED_STATUS);
	}

    public function setToPrinted($details="") {
        $addDetails = false;
        if($details != "") {
            $addDetails = true;
        }
        if($this->statusId != demandStatus::PRINTED_STATUS) {
            $this->changeStatusTo(demandStatus::PRINTED_STATUS, $details, true, $addDetails);
        }
    }

	public function changeStatusTo($newStatusId, $details="", $sendMail = true, $includeDetailsInMail=false)
	{
		$database = database::instance();
		//$clearStatusQueryString = $clearDetails ? demande::STATUS_DETAILS_DB_FIELD." = '".$newStatusId."', " : "";
		$database->requete("UPDATE st_demande SET " 
							.demande::STATUS_DETAILS_DB_FIELD." = '".mysql_real_escape_string($details)."'," 
						   	.demande::STATUS_DB_FIELD." = '".$newStatusId."' 
                            WHERE ".demande::MATRICULE_DB_FIELD." = '".$this->matricule."'");
        $this->statusId = $newStatusId;
        if(!$sendMail)
			return true;
		
		if(!$includeDetailsInMail)
			$details = "";
        
        $this->loadStatusData($this->matricule);
        $user = new userData($this->matricule);
		
        if($user->getUserData($this->matricule)) {
            $preg = array(
                array(
                    'key' => '/@@FIRSTNAME@@/',
                    'value' => $user->getFirstName()
                ), 
                array(
                    'key' => '/@@LASTNAME@@/',
                    'value' => $user->getLastName()
                ),
                array(
                    'key' => '/@@STATUS@@/',
                    'value' => $this->getName()
                ),
                array(
                    'key' => '/@@DETAILS@@/',
                    'value' => $details
                )
            );
			
            return util::sendEmail($user->getEmail(), 'email_status.txt', $preg, "Changement de status de votre demande"); 
        }
	}
	
	public static function getDemandStatusNameFromId($statusId)
	{
		$database = database::instance();
		$results = $database->requete("	SELECT ".demandStatus::STATUS_NAME_DB_FIELD." 
										FROM st_status 
										WHERE  ".demandStatus::STATUS_ID_DB_FIELD." = '$statusId' ");
		$results = mysql_fetch_array($results);
		
		return($results[demandStatus::STATUS_NAME_DB_FIELD]);								
	}
	
	public function getStatusSelectorOptions()
	{
		switch ($this->statusId) 
		{
			case demandStatus::ACCEPTED_STATUS: $this->getAcceptedStatusSelectorOptions();			
				break;
			case demandStatus::PROOF_OK_STATUS: return $this->getProofValidStatusSelectorOptions();				
				break;
			case demandStatus::WAITING_STATUS: return $this->getWaitingStatusSelectorOptions();				
				break;
			case demandStatus::PAID_STATUS: return $this->getPaidStatusSelectorOptions();				
				break;
			case demandStatus::INVALID_PROOF_STATUS: return $this->getWaitingStatusSelectorOptions();				
				break;
			case demandStatus::REFUSED_STATUS: return $this->getWaitingStatusSelectorOptions();				
				break;
			
			default:
				return array(demandStatus::WAITING_STATUS);
				break;
		}
	}
	
	private function getWaitingStatusSelectorOptions()
	{
		return array(demandStatus::PROOF_OK_STATUS , demandStatus::INVALID_PROOF_STATUS, demandStatus::REFUSED_STATUS);
	}
	
	private function getProofValidStatusSelectorOptions()
	{
		return array(demandStatus::ACCEPTED_STATUS, demandStatus::REFUSED_STATUS, demandStatus::WAITING_STATUS);
	}
	
	private function getAcceptedStatusSelectorOptions()
	{
		return array(demandStatus::PAID_STATUS);
	}
	
	private function getPaidStatusSelectorOptions()
	{
		return array(demandStatus::PRINTED_STATUS);
	}

	private function getInvalidProofSelectorOptions()
	{
		return array(demandStatus::PROOF_OK_STATUS, demandStatus::REFUSED_STATUS, demandStatus::WAITING_STATUS);
	}
	
	public function hasStatus()
	{
		return $this->hasStatus;
	}	
	
	public function getName()
	{
		return $this->statusName;
	}
	
	public function getDescription()
	{
		return $this->statusDescription;
	}
	
	public function getDetails()
	{
		return $this->statusDetails;
	}
	
	public function getStatusEmail()
	{
		return $this->statusEmail;
	}
	
	public function getId()
	{
		return $this->statusId;
	}
	
	public function isCanceled()
	{
		return ($this->statusId == demandStatus::CANCELED_STATUS);
	}
	
	public function isAccepted()
	{
		return ($this->statusId == demandStatus::ACCEPTED_STATUS);
	}
	
	public function isWaitingForApproval()
	{
		return ($this->statusId == demandStatus::WAITING_STATUS);
	}
	
	public function hasInvalidProof()
	{
		return ($this->statusId == demandStatus::INVALID_PROOF_STATUS);
	}
	
	public function hasValidProof()
	{
		return ($this->statusId == demandStatus::PROOF_OK_STATUS);
	}
	
	public function isPaid()
	{
		return ($this->statusId == demandStatus::PAID_STATUS);
	}
	
	public function isPrinted()
	{
		return ($this->statusId == demandStatus::PRINTED_STATUS);
	}
	
	public function isRefused()
	{
		return ($this->statusId == demandStatus::REFUSED_STATUS);
	}
}    
?>
