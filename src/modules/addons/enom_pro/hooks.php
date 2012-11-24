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

function enom_pro_admin_ssl_certs ($vars) {
	if (!class_exists('enom_pro')) require_once 'enom_pro.php';
	$enom = new enom_pro();
	if ($_REQUEST['checkenomssl']) {
		if (!$enom->error) {
			$str .= '<div class="contentbox">';
			$expiring_certs = $enom->getExpiringCerts();
			if (count($expiring_certs) > 0 ) {
				$str .= ' <table class="datatable" width="100%" border="0" cellspacing="0" cellpadding="0">
				<tr>
				<th>Domain</th>
				<th>Status</th>
				<th>Product</th>
				<th>Expiration Date</th>
				</tr>
				';
				foreach ($expiring_certs as $cert) {
					$str .= '<tr>
					<td> ';
					if (count($cert['domain']) > 0) 
						$str .= rtrim(implode(', ', array_values($cert['domain'])), ', ');
					else 
						$str .= 'Not Issued';
					$str .='</td>
					<td style="text-align:center;"><a href="http://www.enom.com/secure/configure-ssl-certificate.aspx?certid='.$cert['OrderID'].'" target="_blank" >'.$cert['status'].'</a></td>
					<td style="text-align:center;">'.$cert['desc'].'</td>
					<td style="text-align:center;">'.$cert['expiration_date'].'</td>
					';
				}
			} else {
				//No expiring certs
				$str .= '<div class="contentbox green">Phew! No Certificates Expiring in the next '.$enom->get_addon_setting('ssl_days').' days.</div>';
			}
			$str .= "</div>";
			$content = $str;
		} else {
			$content = $enom->errorMessage;
		}
		echo $content;
		exit;
	}
	$content = '<div id="enomSSL">'.$vars['loading'].'</div>
	<form id="refreshEnomSSL" action="'.$_SERVER['PHP_SELF'].'">
		<input type="hidden" name="checkenomssl" value="1" />
		<input type="submit" value="Refresh"/>
	</form>
	';
	$jquerycode = '
	jQuery("#refreshEnomSSL").live("submit", function  () {
	var $elem = jQuery("#enomSSL");
	$elem.html("'.addslashes($vars['loading']).'");
	jQuery.post("index.php", $(this).serialize(),
	function(data){
	$elem.html(data);
});
return false;
}).trigger("submit");
';
	return array(
			'title'=>'eNom PRO - SSL Certificates <img src="images/icons/securityquestions.png" align="absmiddle" height="16px" width="16px" border="0">','content'=>$content,'jquerycode'=>$jquerycode);
}
add_hook("AdminHomeWidgets",1,"enom_pro_admin_ssl_certs");

function enom_pro_admin_expiring_domains ($vars) {
	if (!class_exists('enom_pro')) require_once 'enom_pro.php';
	$enom = new enom_pro();
	if ($_REQUEST['checkexpiring']) {
		if (!$enom->error) {
			$stats = $enom->getAccountStats();
			$str .= '<div class="contentbox">';
			$str .= '<table width="100%"><tbody>
					<tr>
						<td class="sysoverviewstat"><div class="sysoverviewbox green"><a href="http://www.enom.com/domains/Domain-Manager.aspx" target="_blank">'.$stats['registered'].'</a></div></td>
						<td class="sysoverviewlabel">Registered Domains</td>
					</tr>
					<tr>
					<td class="sysoverviewstat"><div class="sysoverviewbox gold"><a href="http://www.enom.com/domains/Domain-Manager.aspx?tab=expiring" target="_blank" >'.$stats['expiring'].'</a></div></td>
					<td class="sysoverviewlabel">Expiring Domains</td>
					</tr>
					<tr>
					<td class="sysoverviewstat"><div class="sysoverviewbox red"><a href="http://www.enom.com/domains/Domain-Manager.aspx?tab=expired" target="_blank" >'.$stats['expired'].'</a></div></td>
					<td class="sysoverviewlabel">Expired Domains</td>
					</tr>
					<tr>
					<td class="sysoverviewstat"><div class="sysoverviewbox"><a href="http://www.enom.com/domains/Domain-Manager.aspx?tab=redemption" target="_blank" >'.$stats['redemption'].'</a></div></td>
					<td class="sysoverviewlabel">Redemption Period</td>
					</tr>
					<tr>
					<td class="sysoverviewstat"><div class="sysoverviewbox"><a href="http://www.enom.com/domains/Domain-Manager.aspx?tab=redemption" target="_blank" >'.$stats['ext_redemption'].'</a></div></td>
					<td class="sysoverviewlabel">Extended Redemption Period</td>
					</tr>
			</tbody></table>';
			$content = $str;
		} else {
			$content = $enom->errorMessage;
		}
		echo $content;
		exit;
	}
	$content = '<div id="enomExpiring">'.$vars['loading'].'</div>
	<form id="refreshExpiring" action="'.$_SERVER['PHP_SELF'].'">
		<input type="hidden" name="checkexpiring" value="1" />
		<input type="submit" class="btn" value="Refresh"/>
	</form>
	';
	$jquerycode = '
	jQuery("#refreshExpiring").live("submit", function  () {
	var $elem = jQuery("#enomExpiring");
	$elem.html("'.addslashes($vars['loading']).'");
	jQuery.post("index.php", $(this).serialize(),
	function(data){
	$elem.html(data);
});
return false;
}).trigger("submit");
';
	return array(
			'title'=>'eNom PRO - Domain Stats <img src="images/icons/domains.png" align="absmiddle" height="16px" width="16px" border="0">','content'=>$content,'jquerycode'=>$jquerycode);
}
add_hook("AdminHomeWidgets",1,"enom_pro_admin_expiring_domains");

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
					<th>Orders</th>
				</tr>
				';
				foreach ($enom->getTransfers() as $domain) {
					//Loop through the actual domains returned from WHMCS
					$str .= '<tr>
					<td>
						<a style="display:inline-block;width:150px;overflow:scroll;padding:0 5px 0;" target="_blank" title="View WHOIS" href="http://www.whois.net/whois/'.$domain['domain'].'">'.$domain['domain'].'
					</td>
					<td style="text-align:center;">
						<form method="GET" action="clientsdomains.php">
							<input type="hidden" name="userid"  value="'.$domain['userid'].'"/>
							<input type="hidden" name="id"  value="'.$domain['id'].'"/>
							<input type="submit" class="button" value="Edit"/>
						</form>
					</td>
					<td>
						<table class="none" width="100%" border="0" cellspacing="0" cellpadding="0">
						<tr>
							<td><b>eNom Order ID</b></td>
							<td><b>Actions</b></td>
							<td style="text-align:center;"><b>Description</b></td>
						</tr>
					';
							//now we need to loop through the multiple statuses returned for each domain by the enom API
							foreach ($domain['statuses'] as $status) {
								$status = (array)$status;
								switch ($status['statusid']) {
									case 22:
										//Cancelled, domain is locked or not yet 60 days old
										$action = ' <form method="GET" class="resubmit_enom_transfer ajax_submit" action="'.$_SERVER['PHP_SELF'].'">
														<input type="hidden" name="action"  value="resubmit_enom_transfer_order"/>
														<input type="hidden" name="orderid"  value="'.$status['orderid'].'"/>
														<input type="image" src="images/icons/import.png "class="button" title="Re-Submit Transfer Order"/>
													</form>';
										break;
									case 9:
									case 11:
										//Awaiting auto-verification of transfer request
										$action = ' <form method="GET" class="resend_enom_activation ajax_submit" action="'.$_SERVER['PHP_SELF'].'">
														<input type="hidden" name="action"  value="resend_enom_transfer_email"/>
														<input type="hidden" name="domain"  value="'.$domain['domain'].'"/>
														<input type="image" src="images/icons/resendemail.png "class="button" title="Re-Send Transfer Authorization E-Mail"/>
													</form>';
									break;
									default:
										$action = false;	
								}
								$str .= "
							<tr>
								<td><a target=\"_blank\" title=\"Order Date: {$status['orderdate']}\" href=\"http://www.enom.com/domains/TransferStatus.asp?transferorderid={$status['orderid']}\">{$status['orderid']}</a></td>
								<td style=\"text-align:center;\" >".($action ? $action : '<input type="image" src="images/icons/disabled.png "class="button" title="No actions for this order status"/>')."</td>
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
			<form id="refreshEnomTransfers" action="'.$_SERVER['PHP_SELF'].'">
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
		
		$(".ajax_submit").live("submit", function  () {
			var $this = $(this),
				$submit = $this.find("input[type=submit]");
			$(".activation_loading", $this).remove(); 
			$submit.attr("disabled","disabled");
			$this.append("<div class=\"activation_loading\">'.addslashes($vars['loading']).'</div>");
			$.ajax({
				data: $this.serialize(),
				success: function  (response) {
					$(".activation_loading", $this).html(response);
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
 * Admin Page Action API Hooks
 */
function enom_pro_admin_page () {
	//Only load this hook if an ajax request is being run
	if (!isset($_REQUEST['action'])) return;
	//Include our class if needed
	if (!class_exists('enom_pro')) require_once 'enom_pro.php';
	//Instantiate an object
	$enom = new enom_pro();
	if ($_REQUEST['action'] == 'resend_enom_transfer_email') {
		$response = $enom->resend_activation((string)$_REQUEST['domain']);
		if (is_bool($response)) {
			echo "Sent!";
		} else {
			if (!$enom->debug())
				//Check if verbose debugging is enabled, if it is, the above method call will echo an error.
				echo (strip_tags($response));
		}
		die();
	}
	if ($_REQUEST['action'] == 'resubmit_enom_transfer_order') {
		$response = $enom->resubmit_locked((int)$_REQUEST['orderid']);
		if (is_bool($response)) {
			echo "Submitted!";
		} else {
			if (!$enom->debug())
				//Check if verbose debugging is enabled, if it is, the above method call will echo an error.
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
	$domain = addslashes($domain);
	$spinnercode .= '
	jQuery(function($) {
		$.post("'.$_SERVER['PHP_SELF'].'", {action:"spinner", domain:"'.$domain.'" }, function  (data) {
			$("#spinner_ajax_results").html(data)'.$animation.' 
		});
		$("#spinner_ajax_results INPUT").live("click", function  () {
			var $elem = $(this);
			console.log($elem);
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
	if (! class_exists('enom_pro') ) require_once 'enom_pro.php';
	$enom = new enom_pro();
	//Prep the userid of currently logged in account
	$uid = isset($_SESSION['uid']) ? (int)$_SESSION['uid'] : 0; //Set this to 0 for security to return no results if the WHMCS uid is not set in the session
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
}
add_hook("ClientAreaPage",2,"enom_pro_clientarea_transfers");
?>