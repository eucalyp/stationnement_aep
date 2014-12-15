:<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr" dir="ltr">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1' />
<meta name="KEYWORDS" content="Stationnement" />
	<meta name="robots" content="index,follow" />
	<link rel="shortcut icon" href="http://www.aep.polymtl.ca/favicon.ico" />
    <title>Stationnement - AEP</title>

    <link rel="stylesheet" type="text/css" href="CSS/style.css?<?php echo time(); ?>" />
    <script>
    	function replace(hide, show)
    	{
    		document.getElementById(hide).style.display = "none";
    		document.getElementById(show).style.display = "block";	
    	}
    	
    	function logMessageSuccess(message)
    	{
    		document.getElementById('overheadMessageContainer').style.display = "block";
    		document.getElementById('overheadMessage').content = message;	
    	}
    	
    	function logMessageError($message)
    	{
    		
    	}
    	
    	function logMessage(hide, show)
    	{
    		
    	}
    	
    </script>
    <script type="text/javascript" src="http://www.aep.polymtl.ca/index.php?title=-&amp;action=raw&amp;gen=js"></script>
</head>

	<body>
    <div id="main">
        <div id="banner">
            <div id="topleft"><a href="index.php"><img src="images/BanniereStationnement.png" alt="AEP Logo" id="aeplogo"/></a></div>
        </div>

        <div id="wrapper">
        	<div id="overheadMessageContainer" style="display: none" >
        		<label id="overheadMessage"> </label>
        	</div>        	
        	<div>
	            <div id="sidebar">
	            
	                <!-- MENU NAVIGATION -->
					
						<?php 
							require_once("class.loginWidget.php");
							$login = 1;
							if(isset($_GET['login'])) 
							{
							    $login = $_GET['login'];
							}
						print(loginWidget::getWidget("http://$_SERVER[HTTP_HOST]$_SERVER[PHP_SELF]", $login));
						?>
				
	                    <div class="widget">
	                        <script type="text/javascript"> if (window.isMSIE55) fixalpha(); </script>
	                        <div class="widgettitle">Stationnement</div>
	                        <div class="widgetcontent">
	                            <ul>
	                                <li><a href="index.php">Accueil</a></li>
	                                 <li> <b>
	                                 	
	                                 	<?php 
		                                 	require_once("class.util.php");
											require_once('class.authentification.php');
											
											$objAuth = authentification::instance();
		                                 	if($objAuth->isUserAdmin())
											{
												print('<a href="gestion.php">');	
		                                		print("Gestion des demandes");
		                                		print('</a>');    
											}
											else 
											{
												print('<a href="demande.php">');	
		                                		print( !util::hasExistingDemandInDatabase() ? "Faire une demande de permis" : "Consulter votre demande de permis"); 
		                                		print('</a>');                                	
											}
	                                 	
	                                	?>
	                                </b></li>   
	                               <!-- <li><a href="informations.php">Informations</a></li>-->
	                               <!-- <li><a href="faq.php">Foire aux questions</a></li>-->
	                                <li><a href="http://www.polymtl.ca/sdi/docWeb/politiques_procedures_divers/reglement-circultion.pdf">R&eacuteglements concernant le stationnement et la circulation sur les terrains de l'&eacutecole Polytechnique.</a></li>
	                                <li><a href="http://www.polymtl.ca/sdi/docWeb/plans/PlanStationnement.pdf">Plan des parcs de stationnement.</a></li>
	                                <li><a href="http://www.aep.polymtl.ca">Retour au site web de l'AEP</a></li>
	                            </ul>
	                        </div>
	                    </div>
	                                     
	                    <!-- MENU SAVIEZ_VOUS QUE? -->
	                    <div class="widget">
	                        <div class="widgettitle">Saviez-vous que?</div>
	                        <div class="widgetcontent">
	                           Chaque ann&eacutee, il y a plus de demandes de permis que de places de stationnement disponibles.
	                        </div>
	                    </div>
	
	                    <!-- MENU COURRIEL -->
	                    <div class="widget">
	                        <div class="widgettitle">Courriel polymtl.ca</div>
	                        <div class="widgetcontent">
	                            <form method="post" action="http://www.imp.polymtl.ca/horde/imp/redirect.php" target="_blank" name="frm_courriel">
	                                <input type="hidden" name="actionID" value="105"/>
	                                <input type="hidden" name="server" value="pop3"/>
	                                <input type="hidden" name="imapuser"/>
	                                <input type="hidden" name="pass"/>
	                                <div id="saviezvousque">
	                                    <label style="width:100px">Usager:</label><input type="password" name="imapuser" size="8" maxlength="8" id="searchInputUsager"/><br/>
	                                    <label style="width:100px">Mot de passe:</label><input type="password" name="pass" size="8" maxlength="8" id="searchInputMdP"/><br/>
	                                    <input type="submit" value="Acceder" class="searchButton"/>
	                                </div>
	                            </form>
	                        </div>
	                    </div>
		
			<!-- MENU SAVIEZ_VOUS QUE? -->
			
			<!-- <h3 class="break">Date limite</h3> -->
		       <!-- <div id="saviezvousque"><p>Votre demande doit �tre compl�t�e avant le <nobr><b>6 ao�t 2012</b></nobr>.</p></div> -->
			<!--	<div id="saviezvousque"><p>Le site est maintenant ferm� pour l'ann�e.</p></div> -->
	
	                                
	            </div> <!-- Fin sidebar -->
	
	        
	            <div id="maincontent">
	                <div id="content">
	                    <div id="bodyContent">
	                        <div id="contentSub"></div>
                                                        
                        <!-- start content -->




