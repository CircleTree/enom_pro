<?php 
/**
* eNom Pro WHMCS Addon Hooks
* @version @VERSION@
* Copyright 2012 Orion IP Ventures, LLC.
* Licenses Resold by Circle Tree, LLC. Under Reseller Licensing Agreement
*/
function enom_pro_admin_balance ($vars) {
	if (!class_exists('enom_pro')) require_once 'enom_pro.php';
	$enom = new enom_pro();
	if ($_REQUEST['checkenombalance']) {
		if (!$enom->error) {
			$enom->runTransaction('getBalance');
			$str .= '<div class="contentbox enombalance">';
			$str .= '&nbsp;Enom Credit Balance: '.$enom->getBalance()." Available: <b>".$enom->getAvailableBalance().'</b> 
			<a href="https://www.enom.com/myaccount/RefillAccount.asp" target="_blank">Refill Account</a>';
			$str .= "</div>";
			$content = $str;
		} else {
			$content = $enom->errorMessage;
		}
		echo $content;
		exit;
	}
	$content = '<div id="enombalance">'.$vars['loading'].'</div>
		<form id="refreshEnomBalance"action="'.$_SERVER['PHP_SELF'].'">
				<input type="hidden" name="checkenombalance" value="1" />
				<input type="submit" value="Refresh"/>
			</form>
	';
	if ($enom->updateAvailable()) $content .= $enom->updateAvailable();
	
	$jquerycode = '
	jQuery("#refreshEnomBalance").live("submit", function  () {
		var $elem = jQuery("#enombalance");
		$elem.html("'.addslashes($vars['loading']).'");
		jQuery.post("index.php", $(this).serialize(),
			    function(data){
			      $elem.html(data);
		    });
		    return false;
		}).trigger("submit");
	';
	return array('title'=>'eNom PRO - Reseller Balance <img src="images/icons/transactions.png" align="absmiddle" height="16px" width="16px" border="0">','content'=>$content,'jquerycode'=>$jquerycode);
}
add_hook("AdminHomeWidgets",1,"enom_pro_admin_balance");

function enom_pro_admin_transfers ($vars) {
	if (!class_exists('enom_pro')) require_once 'enom_pro.php';
		if ($_REQUEST['checkenomtransfers']) {
			$enom = new enom_pro();
			if (!$enom->error) {
				$str .= '<div class="enomtransfers">';
				$str .= ' <table class="datatable" width="100%" border="0" cellspacing="0" cellpadding="0">
				<tr>
					<th>Domain</th>
					<th>WHMCS Domains</th>
					<th>Resend</th>
					<th>Statuses</th>
				</tr>
				';
				foreach ($enom->getTransfers() as $domain) {
					//Loop through the actual domains returned from WHMCS
					$str .= '<tr>
					<td> <a target="_blank" title="View WHOIS" href="http://www.whois.net/whois/'.$domain['domain'].'">'.$domain['domain'].'</td>
					<td>
						<form method="GET" action="clientsdomains.php">
							<input type="hidden" name="userid"  value="'.$domain['userid'].'"/>
							<input type="hidden" name="id"  value="'.$domain['id'].'"/>
							<input type="submit" class="button" value="Edit"/>
						</form>
					</td>
					<td>
						<form method="GET" class="resend_enom_activation" action="'.$_SERVER['PHP_SELF'].'">
							<input type="hidden" name="action"  value="resend_enom_transfer_email"/>
							<input type="hidden" name="domain"  value="'.$domain['domain'].'"/>
							<input type="image" src="images/icons/resendemail.png "class="button" title="Re-Send Transfer Authorization E-Mail"/>
						</form>
					</td>
					<td>
						<table class="none" width="100%" border="0" cellspacing="0" cellpadding="0">
						<tr>
							<td><b>eNom Order ID</b></td>
							<td><b>Description</b></td>
						</tr>
					';
							//now we need to loop through the multiple statuses returned for each domain by the enom API
							foreach ($domain['statuses'] as $status) {
								$status = (array)$status;
								$str .= "
							<tr>
								<td><a target=\"_blank\" title=\"Order Date: {$status['orderdate']}\" href=\"http://www.enom.com/domains/TransferStatus.asp?transferorderid={$status['orderid']}\">{$status['orderid']}</a></td>
								<td>{$status['statusdesc']}</td>
							</tr>
								";
							}
							
					$str.="
						</table>
					</td>
				</tr>";
				}	
				$str .= "</table></div>";
				$content = $str;
			} else {
				$content = $enom->errorMessage;
			}
			echo $content;
			exit;
		}
		$content = '<div id="enomtransfers">'.$vars['loading'].'</div>
			<form id="refreshEnomTransfers"action="'.$_SERVER['PHP_SELF'].'">
				<input type="hidden" name="checkenomtransfers" value="1" />
				<input type="submit" value="Refresh"/>
			</form>
		';
	
		//Yes, $.ready is redundant, but since WHMCS doesnt alias $, we use it here for convenience;
		$jquerycode = '
		jQuery(document).ready(function($){
		$("#refreshEnomTransfers").live("submit", function  () {
		var $elem = $("#enomtransfers");
		$elem.html("'.addslashes($vars['loading']).'");
			$.post("index.php", $(this).serialize(),
			    function(data){
			      $elem.html(data);
			    });
			    return false;
		}).trigger("submit");
		$(".resend_enom_activation").live("submit", function  () {
		var $submit = $(this).find("input[type=submit]");
		$("#activation_loading").remove(); 
		$submit.attr("disabled","disabled");
		$(this).append("<div id=\"activation_loading\">'.addslashes($vars['loading']).'</div>");
			$.ajax({
				data: $(this).serialize(),
				success: function  (response) {
					$("#activation_loading").html(response);
					$submit.removeAttr("disabled"); 
				}
			});
		return false;
		}); 
	})';
		return array('title'=>'eNom PRO - Pending Transfers <img src="images/icons/clientlogin.png" align="absmiddle" height="16px" width="16px" border="0">','content'=>$content,'jquerycode'=>str_replace(array("\r\n", "\n", "\r","\t"), '', $jquerycode));
}
add_hook("AdminHomeWidgets",1,"enom_pro_admin_transfers");
/**
 * Admin Page for API Hooks
 */
function enom_pro_admin_page () {
	if (!class_exists('enom_pro')) require_once 'enom_pro.php';
	$enom = new enom_pro();
	if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'resend_enom_transfer_email') {
		$response = $enom->resend_activation((string)$_REQUEST['domain']);
		if (is_bool($response)) {
			echo "Sent!";
		} else {
			echo (strip_tags($response));
		}
		die();
	}
}
add_hook("AdminAreaPage",1,"enom_pro_admin_page");
/**
 * Makes the namespinner markup
 */
function enom_pro_namespinner () {
	if (!class_exists('enom_pro')) require_once 'enom_pro.php';
	$enom = new enom_pro();
	global $_LANG;
	if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'spinner') {
		//Only return the API results if this a RESTful request from the AJAX widget
		$parts = $enom->getDomainParts($_REQUEST['domain']);
		//eNom pops the SLD up to TLD in the parsed response if there is no .com
		$sld_posing_as_tld = (bool)$parts['SLD']; //So we check if the SLD result is populated
		if (!$sld_posing_as_tld) {
			$sld = $parts['TLD'];
			$tld = "com";//This doesn't matter to the enom API, as we're just looking for name spins
		} else {
			$sld = $parts['SLD'];
			$tld = $parts['TLD'];
		}
		$domain_name = $sld.'.'.$tld;
		$results = $enom->getSpinner($domain_name);
		if (count($results['domains']) > 0 ) {
			echo ' <h3>'.$_LANG['cartotherdomainsuggestions'].'</h3>';
			echo '<div class="spinner_results_wrapper">';
			foreach ($results['domains'] as $domain) {
				if (isset($results['pricing'][$domain['tld']])) {
				//Only return spin results if we have pricing defined in WHMCS
					$id = str_replace(array(".","-"), "_", $domain['domain']);
					echo ' <div class="spin_result">';
							echo '<input type="checkbox" id="'.$id.'" name="domains[]" value="'.$domain['domain'].'" />';
							echo '<label class="btn" for="'.$id.'">'.$domain['domain'];
							echo '</label>';
								echo '<select name="domainsregperiod['.$domain['domain'].']" >';
									foreach ($results['pricing'][$domain['tld']] as $year=>$price) {
										if ((int)$price > 0)
											echo '<option value="'.$year.'">'.$year.' '.$_LANG['orderyears'].' @ '.$price.'</option>';
									}
							echo '</select>';
					echo '</div>';
				} else {
					if ($enom->debug()) echo 'This TLD doesn\'t have a price defined:'.$domain['tld'].' <br/>';
				}
			}
			
			if ($enom->get_addon_setting(spinner_checkout) == "on") {
				//Only show the add to cart button if enabled
				echo '<input class="btn primary large" type="submit" value="'.$_LANG['addtocart'].'" />';
			}
			echo '</div>';
		} else {
			if ($enom->debug()) echo 'No results';
		}
		die();
	}
	
	
	global $smarty;
	$spinnercode = '';
	if ($enom->get_addon_setting("spinner_css") == "on") {
		//Only include the css if enabled
		$spinnercode .= '<link rel="stylesheet" href="modules/addons/enom_pro/spinner_style.css" />';
	}
	switch ($enom->get_addon_setting("spinner_animation")) {
		case "Off":
			$animation = '.show();';
		break;
		case "Slow":
			$animation = '.slideDown(750);';
		break;
		case "Medium":
			$animation = '.slideDown(400);';
		break;
		case "Fast":
			$animation = '.slideDown(200);';
		break;
	}
	$spinnercode .= '
	<div id="spinner_ajax_results" style="display:none"></div>
	<script>';
	if ($enom->debug()) {
		//Make sure jQuery is loaded when debugging
		$spinnercode .= '
			if (typeof(jQuery) == "undefined") alert("eNom Pro Debug\n\njQuery is not loaded. Make sure your template includes jquery javascript library in header.tpl. See jquery.org for more info.");
			'; 
	}
	if (count($_REQUEST['sld']) > 1) {
		//Check for the cart SLD array
		$domain = $_REQUEST['sld'][0].'.'.ltrim($_REQUEST['tld'][0],'.');
	} elseif (isset($_REQUEST['sld'])) {
		$domain = $_REQUEST['sld'].'.'.ltrim($_REQUEST['tld'],'.');
		//Get the first array domain item, the registration one
	} else {
		$domain = $_REQUEST['domain'];
	}
	$spinnercode .= '
	jQuery(function($) {
		$.post("'.$_SERVER['PHP_SELF'].'", {action:"spinner", domain:"'.$domain.'" }, function  (data) {
			$("#spinner_ajax_results").html(data)'.$animation.' 
		});
		$("#spinner_ajax_results INPUT").live("click", function  () {
			var $elem = $(this);
			if ($elem.is(":checked")) {
				$elem.parent("div").addClass("checked")
			} else {
				$elem.parent("div").removeClass("checked")
			}
		})
	});
	';
	$spinnercode .= '</script>';
	$smarty->assign('namespinner',$spinnercode);
}
add_hook("ClientAreaPage",1,"enom_pro_namespinner");
function enom_pro_clientarea_transfers () {
	global $smarty;
	if (!class_exists('enom_pro')) require_once 'enom_pro.php';
	$enom = new enom_pro();
	if ($enom->checkLicense()) {
		//Prep the userid of currently logged in account
		$uid = isset($_SESSION['uid']) ? $_SESSION['uid'] : 0; //Set this to 0 for security to return no results if the WHMCS uid is not set in the session
		//Prepare the query to check if the current user has any pending enom transfers
		$query = "SELECT `userid`,`type`,`domain`,`status` FROM `tbldomains` WHERE `registrar`='enom' AND `status`='Pending Transfer' AND `userid`=".$uid;
		$result = mysql_query($query);
		//Check if there are any results
		if (mysql_num_rows($result) > 0) {
			//Yes, set the Smarty tag to do the AJAX call
			$smarty->assign('enom_transfers',true);
		} else {
			//No results, ignore the enom_transfers smarty tag in the template
			$smarty->assign('enom_transfers',false);
		}
		//This is where the magic happens
		//Only do the API request asynchronously if there are transfers
		if ($_REQUEST['action'] == 'domains' && $_REQUEST['refresh'] == 'true') {
			//Set cache control headers so IE doesn't cache the response (causing support tickets when a transfer has been approved, for instance)
			header("Cache-Control: no-cache, must-revalidate");
			header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
			//Set the headers so jQuery parses the response as well formed JSON
			header("Content-type: application/json");
			//send a JSON response to the client
			echo json_encode($enom->getTransfers($uid));
			//Exit, we don't need to send WHMCS ;-)
			die();
			//The purpose of this method reduces lag by eliminating the expensive remote API calls that must be made to enom and deferring them until after the page has loaded
			//This ensures that your webserver is not waiting for the API response to send your WHMCS clientarea
		}//End AJAX
	}//End License Check
}
add_hook("ClientAreaPage",2,"enom_pro_clientarea_transfers");
?>