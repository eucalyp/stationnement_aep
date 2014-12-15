<?php

error_reporting(E_ALL);

/**
 * demandesEnLigne - class.validation.php
 *
 * @author Jean-Francois Levesque, <jf.levesque@step.polymtl.ca>
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/**
 * Short description of class validation
 *
 * @access public
 * @author Jean-Francois Levesque, <jf.levesque@step.polymtl.ca>
 */
class validation
{
    // --- ATTRIBUTES ---

    private static $instance = false;

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
        if(!validation::$instance)
        {
           validation::$instance = new validation();
        }
        return validation::$instance; 
    }

    public static function license ($plaque)
    {
        if (preg_match("/^[A-za-z0-9\ ]+$/",$plaque) && !empty($plaque))
        {
            return true;
        }
        
        return false;
    }
    
    public static function annee ($annee)
    {
        if (preg_match("/^[0-9]{2,4}$/",$annee))
        {
            return true;
        }
        
        return false;
    }
    
    public static function date ($date)
    {
        if (preg_match("/^[0-9]+$/",$date))
        {
            return true;
        }
        
        return false;
    }
    
    public static function int ($value)
    {
        if (preg_match("/^-?[0-9]+$/",$value))
        {
            return true;
        }
        
        return false;
    }
    
    public static function matricule ($value)
    {
        if (preg_match("/^[0-9]+$/",$value) && strlen($value) >= 5)
        {
            return true;
        }
        
        return false;
    }
    
    public static function codePostal ($value)
    {
    	$value = str_replace(' ', '', $value);
        if (preg_match("/^[A-Za-z][0-9][A-Za-z][0-9][A-Za-z][0-9]$/",$value))
        {
            return true;
        }
        
        return false;
    }
    
    public static function telephone ($value)
    {
        $value = preg_replace("/ /","",$value);
        $value = preg_replace("/\(/","",$value);
        $value = preg_replace("/\)/","",$value);
        $value = preg_replace("/\-/","",$value);
        if (preg_match("/^[0-9]{10}$/",$value))
        {
            return true;
        }
        
        return false;
    }

    public static function email ($value)
    {
        if (preg_match('/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/',$value))
        {
            return true;
        }
        
        return false;
    }

} /* end of class validation */

?>
