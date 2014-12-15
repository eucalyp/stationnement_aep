<?php 
error_reporting(E_ALL);
require_once("header.php");
require_once("class.util.php");

if(!util::isWebsiteOpen()) {
    print("<h3>Les demandes de stationnement sont pr&eacutesentemment ferm&eacutes. Vous receverez un courriel de l'AEP lorsque la p&eacuteriode de demande ouvrira.</h3>");
} else {
    readfile('informations.html');
}
readfile('footer.html');
function printn ($txt) { print $txt."\n"; }
?>
