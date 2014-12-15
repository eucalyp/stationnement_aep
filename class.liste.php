<?php

error_reporting(E_ALL);

/**
 * demandesEnLigne - class.liste.php
 *
 * @author Jean-Francois Levesque, <jf.levesque@step.polymtl.ca>
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/**
 * Short description of class liste
 *
 * @access public
 * @author Jean-Francois Levesque, <jf.levesque@step.polymtl.ca>
 */
class liste
{
    // --- ATTRIBUTES ---
    protected $liste = null;
    
    // --- OPERATIONS ---
    
    public function __construct()
    {
        $this->liste = array();
    }
    
    public function next()
    {
        return next($this->liste);
    }
    
    public function prev()
    {
        return prev($this->liste);
    }
    
    public function end()
    {
        return end($this->liste);
    }
    
    public function reset()
    {
        return reset($this->liste);
    }
    
    public function current()
    {
        return current($this->liste);
    }
    

} /* end of class liste */

?>
