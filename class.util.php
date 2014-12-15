<?php
/**
t1coul*  - util.php
 *
 * @author Philippe Gref-Viau, <pgrefviau@gmail.com>
 */

require_once ('class.database.php');
 
class util
{
    public static $ALLOWED_EXTENSIONS = array("gif", "jpeg", "jpg", "png");

    public static function cleanup($input)
	{
		return mysql_real_escape_string($input);	
	}
	
	public static function getParam($postData,$param)
	{

	    if (isset($postData[$param]))
	    {
	        return $postData[$param];
	    }
	   
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

   	public static function hasExistingDemandInDatabase($matricule="")
	{
		$objAuth = authentification::instance();
		if ($objAuth->estIdentifie())
		{
			if(empty($matricule))
				$matricule = $objAuth->getUsager();
			
			$database = database::instance();
			$result = $database->requete("SELECT * FROM st_demande WHERE matricule = '".$matricule."'");
			return (mysql_num_rows($result) > 0);
		}
		else
		{
			return false;
		}
	}
	
	public static function hasExistingCarInDatabase($carId)
	{
		
		$objAuth = authentification::instance();
		if ($objAuth->estIdentifie())
		{
			$database = database::instance();
			$result = $database->requete("SELECT * FROM st_car WHERE id = '".$carId."'");
			return (mysql_num_rows($result) > 0);
		}
		else
		{
			return false;
		}
	}
    
    public static function sendEmail($address, $contentFile, $pregArray, $subject) {
        $content = file_get_contents($contentFile);
        foreach($pregArray as $preg) {
            $content = preg_replace($preg['key'], $preg['value'], $content);
        }

        $header = 'From: Stationnement AEP <stationnement@aep.polymtl.ca>'."\r\n"."X-Mailer: PHP".phpversion();
       return  mail($address, $subject, $content, $header);
    }

    public static function cleanUTF8($data) {
	    return iconv('UTF-8', 'UTF-8//IGNORE', utf8_encode($data)); 
    }

    public static function UTF8toISO8859($data) {
        return iconv('UTF-8', 'ISO-8859-1', $data);
    }

    public static function getPaymentMethodNameFromId($id) {
        $paymentMethods = array(DEMANDE_TYPE_COMPTANT => 'Comptant', 
                                DEMANDE_TYPE_MANDAT => 'Mandat poste', 
                                DEMANDE_TYPE_INTERAC => 'Interac', 
                                DEMANDE_TYPE_VISA => 'Visa', 
                                DEMANDE_TYPE_MC => 'MasterCard');                                                    
        return ($id >= DEMANDE_TYPE_COMPTANT && $id <= DEMANDE_TYPE_COMPTANT)? $paymentMethods[$id] : "";
    }

    public static function isWebsiteOpen() {
        $db = database::instance();
        $result = $db->requete("SELECT isOpen FROM st_opening");
        $resultArray = mysql_fetch_array($result);
        if($resultArray['isOpen']) {
            return true;
        } else {
            return false;
        }
    }

    public static function toggleOpening() {
        if(util::isWebsiteOpen()) {
            util::closeWebsite();
        } else {
            util::openWebsite();
        } 
    }

	public static function deleteOldRequests() {
			$db = database::instance();
			$result = $db->requete("DELETE FROM st_demande");
	}
	
    public static function closeWebsite() {
        $db = database::instance();
        $result = $db->requete("UPDATE st_opening SET isOpen='0' WHERE st_opening.isOpen='1'");
    }

    public static function openWebsite() {
        $db = database::instance();
        $result = $db->requete("UPDATE st_opening SET isOpen='1' WHERE st_opening.isOpen='0'");
    }

    public static function generatePassword($length = 8) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $count = mb_strlen($chars);

    for ($i = 0, $result = ''; $i < $length; $i++) {
        $index = rand(0, $count - 1);
        $result .= mb_substr($chars, $index, 1);
    }

    return $result;
}
}
?>
