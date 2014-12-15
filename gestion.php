<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr" dir="ltr">
<head>
	<meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
	<meta name="KEYWORDS" content="Stationnement" />
	<meta name="robots" content="index,follow" />
	<link rel="shortcut icon" href="http://www.aep.polymtl.ca/favicon.ico" />
    <title>Stationnement - AEP</title>
	
    <link rel="stylesheet" type="text/css" href="CSS/adminStyle.css?<?php echo time(); ?>" />
    <link rel="stylesheet" type="text/css" media="screen" href="jqgrid/css/ui.jqgrid.css?<?php echo time(); ?>" />
    <link rel="stylesheet" type="text/css" media="screen" href="jquery-ui/jquery-ui-1.9.2.custom/css/ui-lightness/jquery-ui-1.9.2.custom.css?<?php echo time(); ?>" />
    
    <script type="text/javascript" src="http://www.aep.polymtl.ca/index.php?title=-&amp;action=raw&amp;gen=js"></script>
	<script type="text/javascript" src="CSS/common/wikibits.js"></script>          
	
	<script src="jqgrid/js/jquery-1.9.0.min.js" type="text/javascript"></script>
	<script src="jqgrid/js/i18n/grid.locale-en.js" type="text/javascript"></script>
	<script src="jqgrid/js/jquery.jqGrid.min.js" type="text/javascript"></script>
	<script src="jquery-ui/jquery-ui-1.9.2.custom/js/jquery-ui-1.9.2.custom.js" type="text/javascript"></script>
	<script src="jquery/resize-animation.js" type="text/javascript"></script>
	<script src="jquery/jQueryRotate.js" type="text/javascript"></script>
	<script src="jquery/jquery.depends.min.js" type="text/javascript"></script>
	<script src='jquery/jquery.elevatezoom.js'></script>
</head>

<body>
  <?php
require_once('class.util.php');    
require_once('class.authentification.php');
	$auth = authentification::instance();

	if(!$auth->estIdentifie() || !$auth->isUserAdmin())
	{
		header("Location: index.php");
		exit(0);		
	}
	
    if(isset($_GET['toggle']) && $_GET['toggle'] == 1) {
        util::toggleOpening();
    }
    
    if (isset($_GET['deleteOldRequests']) && $_GET['deleteOldRequests']==1 ) {
		util::deleteOldRequests();
	}
?>
<div style="height:25px">
    <a style="color:orange;padding-left:30px;float:left" href="?toggle=1"><?php print(util::isWebsiteOpen() ? "Fermer les inscriptions" : "Ouvrir les inscriptions")?></a>
    <a style="color:orange;padding-left:30px;float:left;" href="javascript:deleteOldRequests()">Supprimer les anciennes demandes</a>
    <a style="color:orange;padding-right:30px;float:right" href="logout.php">Déconnexion</a>
	</div>		
		<div id="main">
			<div id="adminDemandWrapper">	
				<span id="statusSectionWrapper" >
					<div id="statusSectionContainer">
						
					</div>	
				</span>
				<div id="proofSectionWrapper"  >
					<div id="proofSectionContainer">
						<span id="initialMessage" style="opacity: 0.5; text-align:center; vertical-align: middle" >
							<label style="vertical-align:middle; font-size:22">Choisissez une demande pour débuter</label>
						</span>	
					</div>	
				</div>
				
			</div>
			
			<div id="adminGridWrapper">
                    	<div id="adminLeftSideWrapper">
                    		<div id="adminLeftSideContainer" >

<?php

require_once('class.demandsStatistics.php');

define('SELECTED_FILTER_PARAM',   'filter');

define('ALL_FILTER',   'all');
define('WAITING_FILTER',   'waiting');
define('VALID_PROOF_FILTER',   'validProof');
define('REFUSED_FILTER',   'refused');
define('ACCEPTED_FILTER',   'accepted');
define('PAID_FILTER',   'paid');
define('PRINTED_FILTER',   'printed');
define('INVALID_PROOF_FILTER',   'invalidProof');
define('CANCELED_FILTER',   'canceled');
define('CARPOOLING_FILTER',   'carpooling');
define('CARPOOLING_OTHERS_FILTER',   'carpoolingOther');
define('ELETRIC_CAR_FILTER',   'electricCar');

	$radioButtonCount = 0;

	$selectedFilter = util::getParam($_GET, SELECTED_FILTER_PARAM);
	$selectedFilter = isset($selectedFilter) ? $selectedFilter : ALL_FILTER ;
	
	print("<div style='clear:both'>");
	
	// Filter buttons	
	print('	<span >		
				<div id="filters">');
				  		createStatusFilterButton('Tout', ALL_FILTER, demandsStatistics::getOverallDemandCount(), -1);
					    createStatusFilterButton('Attente', WAITING_FILTER, demandsStatistics::getWaitingDemandsCount(), demandStatus::WAITING_STATUS);
						createStatusFilterButton('Preuves vérifiées', VALID_PROOF_FILTER, demandsStatistics::getValidatedInfosDemandsCount(), demandStatus::PROOF_OK_STATUS);
						createStatusFilterButton('Refusé', REFUSED_FILTER, demandsStatistics::getRefusedDemandsCount(), demandStatus::REFUSED_STATUS);
						createStatusFilterButton('Accepté', ACCEPTED_FILTER, demandsStatistics::getAcceptedDemandsCount(), demandStatus::ACCEPTED_STATUS);
						createStatusFilterButton('Payé', PAID_FILTER, demandsStatistics::getPaidDemandsCount(), demandStatus::PAID_STATUS);
						createStatusFilterButton('Imprimé', PRINTED_FILTER, demandsStatistics::getPrintedDemandsCount(), demandStatus::PRINTED_STATUS);
						createStatusFilterButton('Preuves invalides', INVALID_PROOF_FILTER, demandsStatistics::getInvalidInfosDemandsCount(), demandStatus::INVALID_PROOF_STATUS);
						createStatusFilterButton('Annulé', CANCELED_FILTER, demandsStatistics::getCanceledDemandsCount(), demandStatus::CANCELED_STATUS);
						createStatusFilterButton('Covoiturage', CARPOOLING_FILTER, demandsStatistics::getCarpoolingDemandsCount(), 'carpooling');
						createStatusFilterButton('Covoiturage (autres)', CARPOOLING_OTHERS_FILTER, demandsStatistics::getCarpoolingOthersDemandsCount(), 'carpoolingOthers');
						createStatusFilterButton('Voiture électrique', ELETRIC_CAR_FILTER, demandsStatistics::getElectricalCarDemandsCount(), ELETRIC_CAR_FILTER);
									
	print('		</div>
			</span>');
								
	print("</div>");
	
function createStatusFilterButton($title,$filter, $count, $statusId = null)
{
	$title .= " ($count)";
	createRadioButtonField($title,$filter,'filters', $statusId);
}


function createRadioButtonField($title,$filter, $group, $value)
{
	$baseUrlArgs ="page=0&rows=10&orderBy=matricule&sortOrder=asc";
	global $selectedFilter,$radioButtonCount;

	$name = "radio".$radioButtonCount;
	print("<input type='radio' id='$name' value='$value' name='$group'><label for='$name'>$title</label>");
	$radioButtonCount++;	
}			
?>

<div >
	<table id="datagrid"></table>
	<div id="navGrid"></div>
</div>	
	
<script language="javascript">


var angle = 0;
var isOverlayActive = false;
var currentFilterId = -1;
var currentProofImage;
var selectedRowsGlobal = {};

// Ceate grid
var statusSearchCategories = ":Tous;Attente:Attente;Preuves:Preuves validées;Refus:Refus;Acceptée:Acceptée;Payée:Payée;Imprimée:Imprimée;Preuves invalides:Preuves invalides;Annulée:Annulée";
var notesSearchCategories = ":Tous;Oui:Oui;Non:Non";
jQuery("#datagrid").jqGrid({
   	url:"gridDataLoader.php",
    datatype: "json",
    mtype: "GET",
    colNames:['Matricule', 'Status', 'Date de création', 'Distance (km)', 'Notes'],
    colModel:
    [
		{name:'matricule',index:'matricule', width:80,editable:false, sorttype:'integer'},
		{name:'status',index:'status', width:80,editable:false, stype: 'select',  searchoptions:{
			sopt:['eq'],
			value: statusSearchCategories
			}
		},
		{name:'date',index:'date', width:125,editable:false,sorttype:'date', 
		searchoptions:{dataInit:function(el){
			$(el).datepicker({ dateFormat:"yy-mm-dd" }).change(function(){
				$("#grid_id")[0].triggerToolbar();
			});
		}, 
		sopt:['lt','gt','le','ge','eq','ne']
		}},
		{name:'distance',index:'distance', width:125, align:"right",editable:false, sorttype:'integer', searchoptions:{sopt:['lt','gt','le','ge','eq','ne']}},
		{name:'details',index:'details', width:75, align:"right",editable:false, stype: 'select',  searchoptions:{
			sopt:['eq'],
			value: notesSearchCategories
			}
		}
	],
	rowNum:200,
	scroll: 1,
	rowList:[100,200,500],
	pager: '#navGrid',
	sortname: 'matricule',
	sortorder: "asc",
	viewrecords: true,
	emptyrecords: "Aucune demande",
	shrinkToFit: true,
	autowidth: true,
	height:400,
	multiselect: true,
    multiboxonly: true,
	searchOnEnter: false,
	altrows: true,
	loadonce:true,
	
	onSelectRow: function(rowId,status,e)
	{ 
		doRowSelection()
	},
	onSelectAll: function(aRowids,status)
	{
		doRowSelection()
    },
    loadComplete: function(data) {
         var i, count, $grid = $("#datagrid");
         $grid.jqGrid("resetSelection");
    	for (i = 0, count = selectedRowsGlobal.length; i < count; i+=1) {
    	    $grid.jqGrid('setSelection', selectedRowsGlobal[i], false);
        }
    }
});

jQuery("#datagrid").jqGrid('filterToolbar',{searchOperators : true, searchOnEnter:false});
jQuery("#datagrid").jqGrid('navGrid','#navGrid',{edit:false,add:false,del:false});

/*
// Resize
 $('#resize').resizable({
 	handles: 's',
 	alsoResize: '.ui-tabs-panel'
 });
*/

// Filter button
$( "#filters" ).buttonset();

// Set new filters for grid when a status button is pressed	
$("input[name='filters']").change(function(e){
    var status = $(this).val();
    getGridWithFilter(status);
});

// Hide image overlay
$("#main").click(function(){
	if(isOverlayActive)
	{
		$("#imageOverlayContainer").fadeOut();
		$("#imageZoomOverlayContainer").fadeOut();
		
		isOverlayActive = false;
		$("#imageOverlay").removeData('elevateZoom');
		$('.zoomContainer').remove();
		$('#hzImg').remove();
		$('#hzDownscaled').remove();
	}	
});

function doRowSelection()
{
	var grid = $("#datagrid");
	var selectedRows =  grid.jqGrid('getGridParam','selarrrow');

	if(selectedRows.length == 1)
	{
		var postData = {'matricules': JSON.stringify(selectedRows),'section':'status'};
		setFieldFromAjaxData(postData, 'adminDemandInfo.php','#statusSectionContainer', function(){ 
 			setUpDemandStatusSection(); 			 
 		});
 		
 		postData = {'matricules': JSON.stringify(selectedRows),'section':'proof'};
 		setFieldFromAjaxData(postData, 'adminDemandInfo.php','#proofSectionContainer', function(){ 
 			 setUpDemandProofSection(); 			 
 		});
 		
 		postData = {'matricule': selectedRows[0]};
		setFieldFromAjaxData(postData, 'adminPersonInfo.php','#adminRightSideWrapper',function(){ 
			jQuery( "#rightSideInfoTabs" ).tabs({ 
				show: { effect: "fade", duration: 300, heightStyle: "fill" } });
		 });		
	}
	else if(selectedRows.length > 1)
	{
		var postData = {'matricules': JSON.stringify(selectedRows),'section':'status', 'hasMultipleDemands': true};
		setFieldFromAjaxData(postData, 'adminDemandInfo.php','#statusSectionContainer', function(){ 
 			setUpDemandStatusSection(); 			 
 		});
 		
 		$('#adminRightSideWrapper').html("");	
 		$('#proofSectionContainer').html("");			 
		
	}	
}

function setUpDemandProofSection()
{
	// Rotation buttons
	$(".rotateImageClockwiseButton").button({icons:{primary: "ui-icon-arrowrefresh-1-w-cc" }, text: false}).click(function(){
		angle += 90;
		angle %= 360;
		
		var imagePath = currentProofImage.attr('original-image-path');
		var src = 'serverSideRotateImage.php?imagePath='+imagePath+'&angle='+angle;
		
		currentProofImage.attr('src',src);
		currentProofImage.attr('data-zoom-image',src);
	});
	
	$(".rotateImageCounterClockwiseButton").button({icons:{primary: "ui-icon-arrowrefresh-1-e" }, text: false}).click(function(){
		angle -= 90;
		angle %= 360;
		
		var imagePath = currentProofImage.attr('original-image-path');
		var src = 'serverSideRotateImage.php?imagePath='+imagePath+'&angle='+angle;
		
		currentProofImage.attr('src',src);
		currentProofImage.attr('data-zoom-image',src);
		
	});
	$(".popUpImageOverlayButton").button({icons:{primary: "ui-icon-arrow-4-diag" }, text: false}).click(function(){
		showOverlay();
	});

	// Open image in another tab
	$(".openImageInTabButton").button({icons:{primary: "ui-icon-extlink" }, text: false});
	
	// Show overlay on image frame click
	$(".demandVerificationImageFrame").click(function(){
		showOverlay();
	});
	
	currentProofImage = $(document.getElementById('licenseTabVerificationImage'));
	
	// Proof tabs
	jQuery( "#proofTabs" ).tabs({ 
					show: { effect: "fade", 
					duration: 300},
					heightStyle: 'fill',
					beforeActivate: function(event, ui){
						
						
						var activeTab = ui.newPanel.attr('id');
						var matricule = $("#matricule").val();
						var postData = {'matricule': matricule,
										'selectedTab':activeTab };
										
						currentProofImage = $(document.getElementById(activeTab+'VerificationImage'));
										
						setFieldFromAjaxData(postData, "proofDataLoader.php",'', function(data){
							var proofInfos = $.parseJSON(data);
							if(!proofInfos.hasError)
							{
								//$("#"+proofInfos.selectedTab).html(proofInfos.tabHtmlContent);
								currentProofImage.attr('src',proofInfos.imageSource);
								currentProofImage.attr('original-image-path', proofInfos.imageSource);
								$("#openImageInTabLink").attr('href', proofInfos.imageSource);
								angle = 0;
							}
						});  
					} 
				});
}

function showOverlay()
{
	
	
	var source = currentProofImage.attr('src');
	var zoom_image = currentProofImage.attr('data-zoom-image');
	var margin = 10;
	var zoomWindowWidth = ($("#imageZoomOverlayContainer").width())-margin;
	var zoomWindowHeight = ($("#imageZoomOverlayContainer").height())-margin;
	
	$("#imageOverlay").attr('src',source);

	$.removeData(currentProofImage, 'elevateZoom');
	$('.zoomContainer').remove();
	$("#imageOverlay").elevateZoom({tint:true, 
									tintColour:'#F90', 
									tintOpacity:0.5, 
									zoomWindowPosition:"imageZoomOverlayContainer", 
									zoomWindowHeight: zoomWindowHeight, 
									zoomWindowWidth:zoomWindowWidth,
									scrollZoom: true});
	
	$("#imageZoomOverlayContainer").fadeIn('fast');
	$("#imageOverlayContainer").fadeIn('fast',function(){
		isOverlayActive = true;	
	});
}

function addTextToDescription() {
    $();
}

function updateStatusDescription() {
   		var postData = {'statusId': $("#statusSelector").val()};
        setFieldFromAjaxData(postData, 'getStatusDescription.php','#detailsTextArea', function(data) {
            $("#detailsTextArea").val(data);
        });
}

function setUpDemandStatusSection()
{
	
	$('#statusSelector').change( function() {
	  $("#confirmStatusChangeButton").show();
	  $("#sendMailCheckContainer").show();
		$("#includeDetailsCheckContainer").show();
		$("#detailsTextAreaContainer").show();
        $("#carpoolingDetailsContainer").hide();
        updateStatusDescription();
	});
	
	$("#confirmStatusChangeButton").hide();
	$("#sendMailCheckContainer").hide();
	$("#includeDetailsCheckContainer").hide();
	$("#detailsTextAreaContainer").hide();
	$("#loadingWheel").hide();
	
    $("#printStatusButton").click(function(){
        var matricule = $("#matricule").val();
        var url = "print.php?matricule="+matricule;
        window.open(url);
    });

	$("#confirmStatusChangeButton").click(function(){
		changeDemandStatus();
	});
	
	var currentStatusId = $("#selectedStatus").val();
	$("#statusSelector option[value='"+currentStatusId+"']").each(function() {
	    $(this).remove();
	});	
	//$("#sendMailCheck").dependsOn({'#sendMailCheck input[name="xtraCheese"]': {checked: true}});
}

function changeDemandStatus()
{
		var grid = $("#datagrid");
		var selectedRows = grid.jqGrid('getGridParam','selarrrow');
		
		var matricules;
		if(selectedRows.length <= 1)
			matricules = new Array($("#matricule").val());
		else
			matricules = selectedRows;
		
		var newStatus = $('#statusSelector').val();
		var details = $("#detailsTextArea").val();
		var sendEmail = $("#sendMailCheck").is(':checked')
		var includeDetails = $("#includeDetailsCheck").is(':checked');
		var postData = {'matricules': JSON.stringify(matricules), 
						'status': newStatus,
						'details': details,
						'includeDetailsInMail': includeDetails,
						'sendMail': sendEmail};
		

		$("#loadingWheel").show();
        $("#confirmStatusChangeButton").attr("disabled", true).addClass("ui-state-disabled");
		// AJAX				
		setFieldFromAjaxData(postData,"statusChange.php",'', function(data){
			var statusInfo = $.parseJSON(data);
			
		    $("#loadingWheel").hide();
            $("#confirmStatusChangeButton").attr("disabled", false).removeClass("ui-state-disabled");
			$("#statusChangeIndicatorIcon").attr("src", statusInfo.statusChangeIndicatorImagePath).show();
			if(statusInfo.hasError)
			{
				$("#statusChangeMessage").html(statusInfo.statusChangeMessage).show();
			}
			else
			{
				$("#currentStatusName").html(statusInfo.newStatus).fadeIn();
				$("#demandStateLabel").show();
				$("#confirmStatusChangeButton").hide();
			  	$("#sendMailCheckContainer").hide();
				$("#includeDetailsCheckContainer").hide();
				$("#detailsTextAreaContainer").hide();
		        $("#carpoolingDetailsContainer").show();	
				
                selectedRowsGlobal = statusInfo.selectedDemands;	
				refreshGrid(statusInfo.selectedDemands);	
               	
			}	
			$("#statusChangeIndicatorIcon").delay(1300).fadeOut();
			$("#statusChangeMessage").delay(1300).fadeOut();
        }, function() {
		    $("#loadingWheel").hide();
            $("#confirmStatusChangeButton").attr("disabled", false).removeClass("ui-state-disabled");
        });	

}

function deleteOldRequests() {
	var r = confirm("Est-ce que vous êtes sur de vouloir supprimer les anciennes demandes");
	if (r == true) {
		window.location='?deleteOldRequests=1';
	}
}

function getGridWithFilter(statusId)
{
	var params = {datatype:'json', postData: {status: statusId}}; 	
	$("#datagrid").setGridParam(params).trigger("reloadGrid");
	currentFilterId = statusId;
	
}
		
function refreshGrid()
{
	getGridWithFilter(currentFilterId);
}		
			
function setFieldFromAjaxData(postData,url,target, successCallback, errorCallback){
			$.ajax({
			      url: url,
			      type: 'post',
			      data: postData,
			      success: function(data, status) {	      
			        $(target).html(data); 
			        successCallback(data);   
			      },
			      error: function(xhr, desc, err) {
			      	//alert("AJAX error. Don't panic...");
			      	if ( $.isFunction(errorCallback) ) errorCallback();
			      }
		    }); // end ajax call							
		}
							
</script>

            </div>   
	       </div> <!-- Fin left container -->
				       
	       <div id="adminRightSideWrapper" >
	       		<div id="adminRightSideContainer">
	       		</div>
	       </div> <!-- Fin right container -->
	       
		</div> <!-- Fin wrapper -->
	   
	</div><!-- Fin main -->

	
</div >	

<div id="imageOverlayContainer" style="display:none">
	<div>
		<label style="color:rgba(255, 255, 255, 0.5);font-size: 12px; margin-bottom: 2px;" >Utiliser la roue de la souris pour zoomer sur l'image.</label>
	</div>	
		<img id="imageOverlay" ></img>
	
</div>
<div id="imageZoomOverlayContainer" style="display:none">
	
</div>
</body>
</html>
