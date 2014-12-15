<?php

error_reporting(E_ALL);

/**
 * demandesEnLigne - class.config.php
 *
 * @author Jean-Francois Levesque, <jf.levesque@step.polymtl.ca>
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/**
 * Short description of class config
 *
 * @access public
 * @author Jean-Francois Levesque, <jf.levesque@step.polymtl.ca>
 */
class config
{
    // --- ATTRIBUTES ---
    
    const MysqlHost = 'localhost';
    const MysqlUser = '';
    const MysqlPass = '';
    const MysqlDb   = '';
    
    const PhpSessionPath = '/home/services/stationnementAEP/sessions/';
    
    const SendEmail = true;
    const CurrentYear = 2014;
    
    // --- OPERATIONS ---

} /* end of class config */

?>
