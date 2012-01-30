<?php 
if (!class_exists('enom_pro')) require_once 'enom_pro.php';
function enom_pro_admin_balance ($vars) {
	if ($_REQUEST['checkenombalance']) {
		$enom = new enom_pro();
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
		$submit.attr("disabled","disabled");
		$(this).append("<div id=\"activation_loading\">'.addslashes($vars['loading']).'</div>");
			$.ajax({
				data: $(this).serialize(),
				success: function  (response) {
					alert(response);
					$("#activation_loading").remove();
					$submit.removeAttr("disabled"); 
				}
			});
		return false;
		}); 
	})';
		return array('title'=>'eNom PRO - Pending Transfers <img src="images/icons/clientlogin.png" align="absmiddle" height="16px" width="16px" border="0">','content'=>$content,'jquerycode'=>str_replace(array("\r\n", "\n", "\r","\t"), '', $jquerycode));
	}
	add_hook("AdminHomeWidgets",1,"enom_pro_admin_transfers");
	function enom_pro_admin_page () {
		if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'resend_enom_transfer_email') {
			$enom = new enom_pro();
			if (TRUE === ($response = $enom->resend_activation((string)$_REQUEST['domain']))) {
				echo "Sent!";
			} else {
				echo ($response);
			}
			die();
		}
	}
	add_hook("AdminAreaPage",1,"enom_pro_admin_page");
	function enom_pro_namespinner () {
		global $_LANG;
		
		
		if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'spinner') {
			$enom = new enom_pro();
			$parts = $enom->getDomainParts($_REQUEST['domain']);
			//eNom pops the SLD up to TLD in the parsed response if there is no .com
			$sld_posing_as_tld = (bool)$parts['SLD']; 
			if (!$sld_posing_as_tld) {
				$sld = $parts['TLD'];
				$tld = "com";//This doesn't matter, as we're just looking for name spins
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
								echo '<label for="'.$id.'">'.$domain['domain'].'</label>';
								echo '<select name="domainsregperiod['.$domain['domain'].']" >';
									foreach ($results['pricing'][$domain['tld']] as $year=>$price) {
										if ((int)$price > 0)
											echo '<option value="'.$year.'">'.$year.' '.$_LANG['orderyears'].' @ '.$price.'</option>';
									}
								echo '</select>';
						echo '</div>';
					} else {
						if (ENOM_PRO_DEBUG) echo 'This TLD doesn\'t have a price defined:'.$domain['tld'].' <br/>';
					}
				}
				echo '<input class="btn primary large" type="submit" value="'.$_LANG['addtocart'].'" />';
				echo '</div>';
			}
			die();
		}
		
		
		global $smarty;
		$spinnercode = '
		<div id="spinner_ajax_results" style="display:none"></div>
		<script type="text/javascript">
			jQuery.post("'.$_SERVER['PHP_SELF'].'", {action:"spinner", domain:"'.(isset($_REQUEST['sld']) ? $_REQUEST['sld'] : $_REQUEST['domain']).'" }, function  (data) {
				jQuery("#spinner_ajax_results").html(data).slideDown("slow"); 
			});
		</script>
		';
		$smarty->assign('namespinner',$spinnercode);
	}
	add_hook("ClientAreaPage",1,"enom_pro_namespinner");
?>