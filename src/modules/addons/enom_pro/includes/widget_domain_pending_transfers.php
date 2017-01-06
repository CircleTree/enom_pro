<?php
/**
 * Project: enom_pro
 * Build: @BUILD_DATE@
 * Version: @VERSION@
 */
/**
 * @var $this enom_pro
 */
$transfers = $this->getTransfers();
function enom_pro_render_transfer_status_form( $domain, $status ) { ?>
	<?php switch ( $status['statusid'] ) :
		case 22: //Cancelled, domain is locked or not yet 60 days old
			?>
			<form method="GET" class="resubmit_enom_transfer ajax_submit" action="<?php echo $_SERVER['PHP_SELF'] ?>">
				<input type="hidden" name="action" value="resubmit_enom_transfer_order" />
				<input type="hidden" name="orderid" value="<?php echo $status['orderid']; ?>" />
				<input type="image" src="images/icons/import.png " class="button" title="Re-Submit Transfer Order" />
			</form>
			<?php break;
		case 9:
		case 11: //Awaiting auto-verification of transfer request ?>
			<form method="GET" class="resend_enom_activation ajax_submit" action="<?php echo $_SERVER['PHP_SELF']; ?>">
				<input type="hidden" name="action" value="resend_enom_transfer_email" />
				<input type="hidden" name="domain" value="<?php echo $domain['domain']; ?>" />
				<input type="image" src="images/icons/resendemail.png " class="button"
				       title="Re-Send Transfer Authorization E-Mail" />
			</form>
			<?php break;
		default: ?>
			<div class="alert alert-sm alert-warning">
				<p>No actions for this order status</p>
			</div>
		<?php endswitch;
}

?>

<div class="enom_pro_widget">
	<div class="enomtransfers">
		<?php if ( empty( $transfers ) ) : ?>
			<?php //Exit early if no transfers ?>
			<div class="alert alert-success">No pending transfers found in WHMCS</div>
			<?php return; ?>
		<?php endif; ?>

		<table id="enom_pro_transfers_table">
			<tr>
				<th>Domain</th>
				<th>WHMCS Domains</th>
				<th>Orders</th>
			</tr>
			<?php foreach ( $transfers as $domain ) : ?>

				<tr>
					<td>
						<?php //TODO possibly use WHMCS's whois.php if we can get a token to use on the $POST ?>
						<a class="domain_name"
						   target="_blank"
						   title="View WHOIS"
						   href="http://www.whois.net/whois/<?php echo $domain['domain']; ?>">
							<?php echo $domain['domain']; ?>
						</a>
					</td>
					<td style="text-align:center;">
						<?php ob_start(); //Keep it DRY - re-use the edit button?>
						<a
							class="btn btn-default"
							href="clientsdomains.php?userid=<?php echo $domain['userid'] ?>&id=<?php echo $domain['id'] ?>">
							Edit
						</a>
						<?php $edit_domain_button = ob_get_contents(); ?>
						<?php ob_end_flush(); ?>
					</td>
					<td>
						<?php if ( 0 == count( $domain['statuses'] ) ) : ?>
							<div class="alert alert-info">No Orders Found <?php echo $edit_domain_button; ?></div>
						<?php else: ?>

							<table class="none">
								<tr>
									<th>
										eNom Order ID
									</th>
									<th>
										Actions
									</th>
									<th class="center">
										Description
									</th>
								</tr>
								<?php //now we need to loop through the multiple order statuses
								// returned for each domain order ?>
								<?php foreach ( $domain['statuses'] as $status ) :
									$status = (array) $status; ?>

									<tr>
										<td>
											<a target="_blank"
											   title="Order Date: <?php echo $status['orderdate']; ?>"
											   href="https://www.enom.com/domains/TransferStatus.asp?transferorderid={$status['orderid']}">
												<?php echo $status['orderid']; ?>
											</a>
										</td>
										<td style="text-align:center;">
											<?php enom_pro_render_transfer_status_form( $domain, $status ); ?>
										</td>
										<td>
											<?php echo $status['statusdesc']; ?>
										</td>
									</tr>
								<?php endforeach; ?>
							</table>
						<?php endif; ?>
					</td>
				</tr>
			<?php endforeach; ?>
		</table>
	</div>
</div>

<script> $('.home-widgets-container').masonry(); </script>