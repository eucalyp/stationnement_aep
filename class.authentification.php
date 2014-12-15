<?php

error_reporting(E_ALL);
/**
 * demandesEnLigne - class.authentification.php
 *
 * @author Jean-Francois Levesque, <jf.levesque@step.polymtl.ca>
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

require_once('class.log.php');
require_once('class.database.php');

/**
 * Short description of class authentification
 *
 * @access public
 * @author Jean-Francois Levesque, <jf.levesque@step.polymtl.ca>
 */
class authentification
{
    // --- ATTRIBUTES ---
    private $usager         = '';
    private $permsLecture   = 0;
    private $permsEcriture  = 0;
    private $permsGroupe    = 0;

	private $isUserAdmin = false;
    
    private $objDatabase    = null;
    private $objLog         = null;
    
    private static $instance = false;
	const SESSION_TIMEOUT_SECONDS = 1800;
    // --- OPERATIONS ---

    /**
     * Short description of method instance
     *
     * @access public
     * @author Jean-Francois Levesque, <jf.levesque@step.polymtl.ca>
     * @return void
     */
    public static function instance()
    {
        if(!authentification::$instance)
        {
           authentification::$instance = new authentification();
           authentification::$instance->init();
        }
        return authentification::$instance; 
    }
    
    /**
     * Short description of method instance
     *
     * @access public
     * @return bool
     */
    public function verification($u,$p)
    {
        $resultat = $this->objDatabase->requete("SELECT * from st_authentication WHERE matricule = '$u' AND password = '".md5($p)."'");
        if (mysql_num_rows($resultat) == 1)
        {
            $resultat = mysql_fetch_array($resultat);
			
            $this->usager = $u;
            $_SESSION['usager'] = $this->usager;
            $_SESSION['key'] = sha1($this->usager . $_SERVER['REMOTE_ADDR']);
			$_SESSION['isAdmin'] = $resultat['admin']; 
            $this->objLog->ajoutLog(LOG_NIVEAU_INFO, LOG_MODULE_AUTH, null, "Auth OK : $u");
            return true;
        }
        
        $this->usager = null;
        $_SESSION['usager'] = $this->usager;
        $_SESSION['key'] = '0123';
        $this->objLog->ajoutLog(LOG_NIVEAU_INFO, LOG_MODULE_AUTH, null, "Auth ERREUR : $u");
        return false;
    }
    
    /**
     * Short description of method instance
     *
     * @access public
     * @return void
     */
    public function getUsager()
    {
        return $this->usager;
    }
    
    /**
     * Short description of method instance
     *
     * @access public
     * @return void
     */
    public function estIdentifie()
    {
        if (isset($_SESSION['usager']) && isset($_SESSION['key']))
		{
			if(isset($_SESSION['lastAuthTime']))
			{
				$sessionLifetime = time() - $_SESSION['lastAuthTime']; 
				if($sessionLifetime > authentification::SESSION_TIMEOUT_SECONDS && !$this->isUserAdmin() )
				{
					session_destroy();
					header("Location: index.php");
				}
			}
			
            if (sha1($_SESSION['usager'] . $_SERVER['REMOTE_ADDR']) == $_SESSION['key'] )
            {
                $this->usager = $_SESSION['usager'];
				$_SESSION['lastAuthTime'] = time();
                return true;
            }
		}
        return false;
    }
    
	public function isUserAdmin()
	{
		return isset($_SESSION['isAdmin']) && $_SESSION['isAdmin'];
	}
	
    /**
     * Short description of method instance
     *
     * @access public
     * @return void
     */
    public function getPermsEcriture()
    {
        return true;
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
        $this->objLog = log::instance();
        
        session_save_path(config::PhpSessionPath);
        session_set_cookie_params(0);
        session_start();
        
        $this->estIdentifie();
    }

} /* end of class authentification */

?>
