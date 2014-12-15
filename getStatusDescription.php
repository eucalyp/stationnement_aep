<?php

error_reporting(E_ALL);

require_once('class.authentification.php');
require_once('class.database.php');
require_once('class.util.php');

$statusId = util::getParam($_POST, 'statusId'); 
    
$db = database::instance();
$result = $db->requete("SELECT description FROM st_status WHERE st_status.statusId=$statusId");
$resultArray = mysql_fetch_array($result);

print(util::cleanUTF8($resultArray['description']));
?>
