<?php
	require_once ('class.database.php');
	require_once ('class.demande.php');
	require_once ('class.tripInfo.php');
	require_once ('class.util.php');
	
	$database = database::instance();
	
	$page = $_GET['page']; 	// get the requested page
	$limit = $_GET['rows']; // get how many rows we want to have into the grid
	$orderBy = $_GET['sidx']; // get index row - i.e. user click to sort
	$sortOrder = $_GET['sord']; 	// get the direction
	
	$status = util::getParam($_GET, 'status');
	
	
	$result = $database->requete("SELECT COUNT(*) AS count FROM st_demande");
	$row = mysql_fetch_array($result);
	$count = $row['count'];
	
	$totalPages = ($count > 0 && $limit > 0)? ceil($count/$limit) : 0;
	
	if ($page > $totalPages) 
	{
		$page=$totalPages;
	}
	$baseStart = $limit*$page - $limit; 
	$start = ($baseStart < 0) ? 0: $baseStart; 
	
	$statusQueryClause = "";
	if(is_numeric($status))
	{
		$statusQueryClause = (!isset($status) || $status<0) ? " " : "AND st_demande.".demande::STATUS_DB_FIELD." = '".$status."' ";
	}
	else if(isset($status))
	{
		switch ($status) {
			case 'carpooling':	$statusQueryClause = "AND st_demande.".demande::CARPOOLING_DB_FIELD." = 1";		
				break;
			case 'carpoolingOthers': $statusQueryClause = "AND st_demande.".demande::CARPOOLING_OTHERS_DB_FIELD." = 1";			
				break;
			case 'electricCar':	$statusQueryClause = " AND st_car.".car::ELECTRIC_DB_FIELD." = 1";	
				break;	
		}	
	}

	$query = "SELECT * FROM st_demande INNER JOIN st_trip,st_status, st_car
			  WHERE st_demande.".demande::TRIP_DB_FIELD." = st_trip.".tripInfo::ID_DB_FIELD."  
			  AND  	st_demande.".demande::STATUS_DB_FIELD." = st_status.".demandStatus::STATUS_ID_DB_FIELD."
			  AND st_demande.".demande::CAR1_DB_FIELD." = st_car.".car::CAR_ID_DB_FIELD." 
              $statusQueryClause 
              ORDER BY $orderBy $sortOrder LIMIT $start, $limit";
			  
	$result = $database->requete($query);
	
	$responce = new StdClass;
	$responce->page = $page;
	$responce->total = strval($totalPages);
	$responce->records = $count;
	$i=0;
	
	while($resultsArray = mysql_fetch_array($result,MYSQL_ASSOC)) 
	{
	    $responce->rows[$i]['id']=$resultsArray[demande::MATRICULE_DB_FIELD];
	    $responce->rows[$i]['cell']=array(	$resultsArray[demande::MATRICULE_DB_FIELD],
	    									cleanUTF8($resultsArray[demandStatus::STATUS_NAME_DB_FIELD]),
	    									$resultsArray[demande::CREATION_DATE_DB_FIELD],
	    									floatval($resultsArray[tripInfo::DISTANCE_DB_FIELD])/1000.0,
	    									empty($resultsArray[demande::DETAILS_DB_FIELD]) ? "Non" : "Oui");
	    									//iconv('UTF-8', 'UTF-8//IGNORE', utf8_encode($resultsArray[demande::DETAILS_DB_FIELD])));
	    $i++;
    }
    echo json_encode($responce);

    function cleanUTF8($data) {
	    	return iconv('UTF-8', 'UTF-8//IGNORE', utf8_encode($data)); 
    }
?>
