<?php

require_once ('class.database.php');
require_once ('class.demandStatus.php');
require_once ('class.demande.php');

class demandsStatistics
{

	public static function getOverallDemandCount()
	{
		return self::getSpecifiedCount(demande::STATUS_DB_FIELD, null);
	} 
	
	public static function getWaitingDemandsCount()
	{
		return self::getSpecifiedCount(demande::STATUS_DB_FIELD, demandStatus::WAITING_STATUS);
	}
	
	public static function getAcceptedDemandsCount()
	{
		return self::getSpecifiedCount(demande::STATUS_DB_FIELD, demandStatus::ACCEPTED_STATUS);
	}
	
	public static function getRefusedDemandsCount()
	{
		return self::getSpecifiedCount(demande::STATUS_DB_FIELD, demandStatus::REFUSED_STATUS);
	}
	
	public static function getCanceledDemandsCount()
	{
		return self::getSpecifiedCount(demande::STATUS_DB_FIELD, demandStatus::CANCELED_STATUS);
	}
	
	public static function getPaidDemandsCount()
	{
		return self::getSpecifiedCount(demande::STATUS_DB_FIELD, demandStatus::PAID_STATUS);
	}
	
	public static function getPrintedDemandsCount()
	{
		return self::getSpecifiedCount(demande::STATUS_DB_FIELD, demandStatus::PRINTED_STATUS);
	}
	
	public static function getValidatedInfosDemandsCount()
	{
		return self::getSpecifiedCount(demande::STATUS_DB_FIELD, demandStatus::PROOF_OK_STATUS);
	}
	
	public static function getInvalidInfosDemandsCount()
	{
		return self::getSpecifiedCount(demande::STATUS_DB_FIELD, demandStatus::INVALID_PROOF_STATUS);
	}
	
	public static function getCarpoolingDemandsCount()
	{
		return self::getSpecifiedCount(demande::CARPOOLING_DB_FIELD, true);
	}
	
	public static function getCarpoolingOthersDemandsCount()
	{
		return self::getSpecifiedCount(demande::CARPOOLING_OTHERS_DB_FIELD, true);
	}
	
	public static function getElectricalCarDemandsCount()
	{
		$database = database::instance();
		$result = $database->requete("SELECT count(".demande::MATRICULE_DB_FIELD.") as nb 
									 FROM st_demande INNER JOIN st_car
									 ON (st_demande.".demande::CAR1_DB_FIELD." = st_car.".car::CAR_ID_DB_FIELD." 
									 OR st_demande.".demande::CAR2_DB_FIELD." = st_car.".car::CAR_ID_DB_FIELD.")
									 WHERE st_car.".car::ELECTRIC_DB_FIELD." = 1");
		$result = mysql_fetch_array($result);
		return $result['nb'];
	}
	
	private static function getSpecifiedCount($field, $value)
	{
		$database = database::instance();
		$whereClause = !isset($value) ? "" : "WHERE $field = '$value'";
		$result = $database->requete("SELECT count(".demande::MATRICULE_DB_FIELD.") as nb FROM st_demande $whereClause");
		$result = mysql_fetch_array($result);
		return $result['nb'];
	}
}

?>
