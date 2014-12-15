<?php

error_reporting(E_ALL);

/**
 * demandesEnLigne - class.database.php
 *
 * @author Jean-Francois Levesque, <jf.levesque@step.polymtl.ca>
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

require_once('class.config.php');
require_once('class.log.php');

/**
 * Short description of class database
 *
 * @access public
 * @author Jean-Francois Levesque, <jf.levesque@step.polymtl.ca>
 */
class database
{
    // --- ATTRIBUTES ---

    private static $instance = null;
    private $objLog = null;    
    private $connexion = null;
    
    public $dernierInsertId = null;

    // --- OPERATIONS ---

    /**
     * Short description of method instance
     *
     * @access public
     * @return void
     */
    public static function instance()
    {
        if(!database::$instance)
        {
           database::$instance = new database();
           database::$instance->init();
           database::$instance->connect();
        }
        return database::$instance; 
    }
    
    /**
     * Short description of method instance
     *
     * @access private
     * @return void
     */
    private function connect()
    {
        if (!$this->connexion)
        {
            $this->connexion = mysql_connect(config::MysqlHost, 
                                             config::MysqlUser,
                                             config::MysqlPass);
            
            if (!$this->connexion)
            {
                echo "Impossible de se connecter à la base de données : ".mysql_error();
                exit(1);
            }
            
            if (!mysql_select_db(config::MysqlDb))
            {
                echo "Impossible de sélectionner la BD ".config::MysqlDb. " : ".mysql_error();
                exit(1);
            }
        }
    }
    
    /**
     * Short description of method instance
     *
     * @access public
     * @return void
     */
    public function requete($requete,$logRequete=true,$insertId=false)
    {
        if (!$this->connexion)
        {
            $this->connect();
        }
        
        $tempsRequis = microtime(true);
            $resultat = mysql_query($requete);
        $tempsRequis = microtime(true) - $tempsRequis;
        
        if (!$resultat)
        {
            echo "Impossible de faire la requete '$requete' : ".mysql_error();
            exit(1);
        }
        
        if ($insertId)
        {
            $this->dernierInsertId = mysql_insert_id();
        }
        
        if ($logRequete)
        {
            $this->objLog->ajoutLog(LOG_NIVEAU_DEBUG, LOG_MODULE_DATABASE, '', sprintf("[%.4fs] %s",$tempsRequis,$requete));
        }
        
        return $resultat;
    }
    
	public function beginTransaction()
	{		
		$this->requete("BEGIN");
	}
	
	public function commitTransaction()
	{
		$this->requete("COMMIT");
	}
	
	public function abortTransaction()
	{
		$this->requete("ROLLBACK");
	}
	
    /**
     * Short description of method instance
     *
     * @access private
     * @return void
     */
    private function init()
    {
        $this->objLog = log::instance();
    }
    
    /**
     * Short description of method instance
     *
     * @access public
     * @return void
     */
    function __destruct()
    {
        if ($this->connexion)
        {
            mysql_close($this->connexion);
        }
    }

    /**
     * Short description of method instance
     *
     * @access public
     * @return void
     */
    function ajoutslashes($texte)
    {
        /*if (!get_magic_quotes_gpc())
        {
            return addslashes($texte);
        }*/
        
        
        
        return addslashes(stripslashes($texte));
    }
} /* end of class database */

?>
