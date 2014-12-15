<?php
/**
 * demandesEnLigne - class.log.php
 *
 * @author Jean-Francois Levesque, <jf.levesque@step.polymtl.ca>
 */

/*
 * Definition des constantes pour la base de données.
 */
define('LOG_MODULE_DATABASE', 1);
define('LOG_MODULE_DEMANDE',  2);
define('LOG_MODULE_USAGER',   3);
define('LOG_MODULE_VEHICULE', 4);
define('LOG_MODULE_AUTH',     5);
define('LOG_MODULE_DEMANDELISTE',  6);

define('LOG_NIVEAU_ALERTE',   1);
define('LOG_NIVEAU_ERREUR',   2);
define('LOG_NIVEAU_INFO',     3);
define('LOG_NIVEAU_DEBUG',    4);

define('LOG_OBJET_USAGER',    'u_');
define('LOG_OBJET_DEMANDE',   'd_');
define('LOG_OBJET_VEHICULE',  'v_');

define('LOG_INTERVENANT_ADMIN',  'a_');
define('LOG_INTERVENANT_USAGER', 'u_');

// Chaque media a un bit different.
define('LOG_MEDIA_MYSQL',  1);


error_reporting(E_ALL);

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

require_once('class.config.php');
require_once('class.authentification.php');
require_once('class.database.php');

/**
 * Short description of class log
 *
 * @access public
 * @author Jean-Francois Levesque, <jf.levesque@step.polymtl.ca>
 */
class log
{
    // --- ATTRIBUTES ---

    private static $instance = false;
    private $objDatabase = null;
    private $objAuthentification = null;

    // --- OPERATIONS ---

    /**
     * Short description of method instance
     *
     * @access public
     * @author Jean-Francois Levesque, <jf.levesque@step.polymtl.ca>
     * @return void
     */
    public static function &instance()
    {
        if(!log::$instance)
        {
           log::$instance = new log();
           log::$instance->init();
        }
        return log::$instance; 
    }
    
    // Prevent users to clone the instance
   public function __clone()
   {
       trigger_error('Clone is not allowed.', E_USER_ERROR);
   }
    
    private function __construct() {}
    
    /**
     * Short description of method instance
     *
     * @access public
     * @author Jean-Francois Levesque, <jf.levesque@step.polymtl.ca>
     * @return void
     */
    public function ajoutLog($niveau, $module, $objet, $msg)
    {
        
        //echo "[".$this->getNiveau($niveau)."] : $module ($objet) |".$this->objAuthentification->getUsager()."| => $msg <br>";
        $this->objDatabase->requete("INSERT LOW_PRIORITY INTO st_log SET "
                                ."date = UNIX_TIMESTAMP(), "
                                ."module = '$module', "
                                ."niveau = '$niveau', "
                                ."ip = '".$_SERVER["REMOTE_ADDR"]."', "
                                ."objet = '$objet', "
                                ."intervenant = '".$this->objAuthentification->getUsager()."', "
                                ."msg = '".addslashes($msg)."'",false);
    }

    /**
     * Short description of method instance
     *
     * @access public
     * @author Jean-Francois Levesque, <jf.levesque@step.polymtl.ca>
     * @return void
     */
    public function getNiveau($niveau)
    {
        $niveauText = array(LOG_NIVEAU_ALERTE => "Alerte",
                            LOG_NIVEAU_ERREUR => "Erreur",
                            LOG_NIVEAU_INFO   => "Info",
                            LOG_NIVEAU_DEBUG  => "Debug");
        return $niveauText[$niveau];
    }
    
    /**
     * Short description of method instance
     *
     * @access private
     * @return void
     */
    private function init()
    {
        $this->objDatabase = database::instance();
        $this->objAuthentification = authentification::instance();
    }

} /* end of class log */

?>
