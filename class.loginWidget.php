<?php
error_reporting(E_ALL);

/**
 *  - class.loginWidget.php
 *
 * @author Philippe Gref-Viau, <pgrefviau@gmail.com>
 */
require_once('class.authentification.php');
require_once('class.util.php');
 
 
 class loginWidget
 {
 	public static function getWidget($pageToRedirect, $login_success,$errorMessage="")
	 {
         $objAuth = authentification::instance();
		if ($objAuth->estIdentifie())
		{
            $widget_html = '<div class="widget">
                Vous &ecirctes pr&eacutesentement connect&eacute en tant que <b>'.$objAuth->getUsager().'</b>.</br>
                [<a href="logout.php"> D&eacuteconnexion</a>]<br>
                    </div>';
		}
		else
		{
            			
			$widget_html = '<div class="widget">
	                        <script type="text/javascript"> if (window.isMSIE55) fixalpha(); </script>
							<div class="widgettitle"> Connexion </div>';
if(!$login_success) {
    $widget_html = $widget_html.'<div class="formErrorField"> Mauvais matricule ou mot de passe </div>';
}

$widget_html = $widget_html.'<div class="widgetcontent">
                        	<form method="post" action="auth.php?redirect='.$pageToRedirect.'" name="connexion">
                                <div style="display:block">
                                	<div style="display:block">
                                		<label style="display:inline;vertical-align:middle">Nom dutilisateur</label> 
                                		<input style="width:125px;float:right;vertical-align:middle"" type="text" name="username" size="8" maxlength="7" id="username" />
									</div>
									<div style="display:block;clear:both">
										<label style="display:inline;vertical-align:middle">Mot de passe</label> 
										<input style="width:125px;float:right;vertical-align:middle" type="password" name="password" size="8" maxlength="24" id="password" />
									</div>
									<div style="display:block;margin-top:15px;clear:both">
	                                    <a style="vertical-align:bottom;" href="register.php" class="searchButton"> Cr&eacuteer un compte </a>
										<input style="vertical-align:middle;margin-left:65px" type="submit" value="Acceder" class="searchButton"/>
</br>
	                                    <a style="vertical-align:bottom;" href="accessrecovery.php" class="searchButton">Mot de passe oubli&eacute</a>
									</div>     
                                </div>
                            </form>
                        </div>
                    </div>';
        }	
        return $widget_html;
	 }
 }
