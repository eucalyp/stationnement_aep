<?php

error_reporting(E_ALL);

/**
 * demandesEnLigne - class.demandeListe.php
 *
 * @author Jean-Francois Levesque, <jf.levesque@step.polymtl.ca>
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

require_once('class.log.php');
require_once('class.database.php');
require_once('class.demande.php');
require_once('class.liste.php');
require_once('class.config.php');

/**
 * Short description of class vehicule
 *
 * @access public
 * @author Jean-Francois Levesque, <jf.levesque@step.polymtl.ca>
 */
class demandeListe extends liste
{
    // --- ATTRIBUTES ---
    private $objDatabase    = null;
    private $objLog         = null;
    
    // --- OPERATIONS ---
    
    public function __construct()
    {
        parent::__construct();
        $this->objDatabase    = database::instance();
        $this->objLog         = log::instance();
    }
    
    public function rechercheParAnnee($annee = config::CurrentYear,
                              $texte = '',
                              $limiteDebut = '0', 
                              $limiteNb = '20',
                              $ordrePar = 'id', 
                              $ordreSens = 'asc')
    {
        return $this->recherche($texte, $limiteDebut, $limiteNb, $ordrePar, $ordreSens, " AND (annee = '$annee') ");
    }
    
    public function rechercheParStatus($status = '0',
                              $texte = '',
                              $limiteDebut = '0', 
                              $limiteNb = '20',
                              $ordrePar = 'id', 
                              $ordreSens = 'asc')
    {
        return $this->recherche($texte, $limiteDebut, $limiteNb, $ordrePar, $ordreSens, " AND (status = '$status') ");
    }

    public function rechercheParAnneeEtStatus($annee = config::CurrentYear,
                              $status = '0',
                              $texte = '',
                              $limiteDebut = '0',
                              $limiteNb = '20',
                              $ordrePar = 'id',
                              $ordreSens = 'asc')
    {
        return $this->recherche($texte, $limiteDebut, $limiteNb, $ordrePar, $ordreSens, " AND (status = '$status') AND (annee = '$annee') ");
    }

    
    public function rechercheParEmail($email = '%',
                              $texte = '',
                              $limiteDebut = '0', 
                              $limiteNb = '20',
                              $ordrePar = 'id', 
                              $ordreSens = 'asc')
    {
        return $this->recherche($texte, $limiteDebut, $limiteNb, $ordrePar, $ordreSens, " AND (email LIKE '%$email%') ");
    }
    
    public function recherche($texte = '',
                              $limiteDebut = '0', 
                              $limiteNb = '20',
                              $ordrePar = 'matricule', 
                              $ordreSens = 'asc',
                              $whereParticulier = '')
    {
        $requeteWhere = " (matricule LIKE '$texte' OR "
                       ."payment LIKE '$texte' OR "
                       ."licenseNumber LIKE '$texte' OR "
                       ."licenseClass LIKE '$texte') ";
        
        $this->liste = array();
        
        $resultat = $this->objDatabase->requete("SELECT matricule from st_demande WHERE $requeteWhere $whereParticulier ORDER BY $ordrePar $ordreSens LIMIT $limiteDebut,$limiteNb");
        
        if (mysql_num_rows($resultat) < 1 )
        {
            return false;
        }
        else
        {
            $this->objLog->ajoutLog(LOG_NIVEAU_INFO,LOG_MODULE_DEMANDELISTE, null, "Recherche : '$texte' : ".mysql_num_rows($resultat).' resultats');
        }
        
        while ($ligne = mysql_fetch_array($resultat))
        {
            $demandeTemp = new demande();
            $demandeTemp->ouvrir($ligne['matricule']);
            array_push($this->liste, $demandeTemp);
        }
        
        $this->reset();
        
        return true;
    }

    public function getNbParStatus($status = '0', $annee = config::CurrentYear)
    {
        $resultat = $this->objDatabase->requete("SELECT count(id) as nb from st_demande WHERE status = '$status' AND annee = '$annee'");
        if ($ligne = mysql_fetch_array($resultat))
        {
            return $ligne['nb'];
        }
        return -1;
    }
    
    public function getNb($annee = config::CurrentYear)
    {
        $resultat = $this->objDatabase->requete("SELECT count(id) as nb from st_demande WHERE annee = '$annee'");
        if ($ligne = mysql_fetch_array($resultat))
        {
            return $ligne['nb'];
        }
        return -1;
    }

    public function getNbCovoiturage($annee = config::CurrentYear)
    {
        $resultat = $this->objDatabase->requete("SELECT count(id) as nb from st_demande WHERE covoiturage = 1 AND annee = '$annee'");
        if ($ligne = mysql_fetch_array($resultat))
        {
            return $ligne['nb'];
        }
        return -1;
    }

    public function getNbBanqueCovoiturage($annee = config::CurrentYear)
    {
        $resultat = $this->objDatabase->requete("SELECT count(id) as nb from st_demande WHERE banqueCovoiturage = 1 AND annee = '$annee'");
        if ($ligne = mysql_fetch_array($resultat))
        {
            return $ligne['nb'];
        }
        return -1;
    }

    public function getNbVehiculeBranchable($annee = config::CurrentYear)
    {
        $resultat = $this->objDatabase->requete("SELECT count(id) as nb from st_demande WHERE vehiculeBranchable = 1 AND annee = '$annee'");
        if ($ligne = mysql_fetch_array($resultat))
        {
            return $ligne['nb'];
        }
        return -1;
    }


} /* end of class vehicule */

?>
