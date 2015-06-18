<?php
/**
 * Project: enom_pro
 * Build: @BUILD_DATE@
 * Version: @VERSION@
 */
/** @var $this enom_pro */
$_REQUEST['show_all'] = true;
$expiring_certs = $this->getExpiringCerts(); ?>
<?php if ( count( $expiring_certs ) > 0 ) : ?>
	<table class="table table-condensed table-hover">
		<tr>
			<th>Domain</th>
			<th>Status</th>
			<th>Product</th>
			<th>Expiration Date</th>
		</tr>
		<?php foreach ( $expiring_certs as $index => $cert ) : ?>
			<tr>
				<td>
					<?php if ( count( $cert['domain'] ) > 0 && ! empty( $cert['domain'][0] ) ): ?>
						<?php $clientIdByDomain = $this->getClientIdByDomain( reset( $cert['domain'] ) ); ?>
						<?php $domainString = rtrim( implode( ', ', array_values( $cert['domain'] ) ), ', ' ); ?>
						<?php if ( false === $clientIdByDomain || false == $this->willCertificateReminderBeSent($cert)) : ?>
							<p>
								<span class="label label-danger">
									<span class="enom-pro-icon enom-pro-icon-verify-alt"></span>
								</span>
								SSL Reminder will NOT be sent.
								<?php if (false === $clientIdByDomain) : ?>
									No matching product or domain found in WHMCS for:
									<?php echo $domainString; ?>
								<?php endif;?>
								
								<?php if (false ===  $this->willCertificateReminderBeSent($cert)): ?>
									Invalid Certificate Status to send reminder (<?php echo $cert['status']; ?>. ID-<?php echo $cert['status_id'] ?>).
								<?php endif;?>
							</p>
						<?php else: ?>
							<p>
							<span class="label label-success">
								<span class="enom-pro-icon enom-pro-icon-checkmark"></span>
							</span>
								SSL Reminder email will be sent to
								<?php $clientDetails = enom_pro::whmcs_api(
									'getclientsdetails',
									array( 'clientid' => $clientIdByDomain )
								);
								echo $clientDetails['firstname'] . " " . $clientDetails['lastname'];?>
								on <span class="label label-default">
									<?php echo date('m-d-Y', strtotime($cert['expiration_date']) - (enom_pro::get_addon_setting('ssl_email_days') * 86400)) ?>
								</span>
							</p>
							<a href="<?php echo enom_pro::MODULE_LINK ?>&action=preview_ssl_email&index=<?php echo $index ?>" class="btn btn-primary ep_lightbox">Preview SSL Email</a>
						<?php endif; ?>

					<?php else: ?>
						Not Issued
					<?php endif; ?>
				</td>
				<td style="text-align:center;">
					<a href="http://www.enom.com/secure/configure-ssl-certificate.aspx?certid=<?php echo $cert['CertID'] ?>"
					   target="_blank"
					   class="btn btn-default btn-link">
						<?php echo $cert['status']; ?>
					</a>
				</td>
				<td style="text-align:center;">
					<?php echo $cert['desc']; ?>
				</td>
				<td style="text-align:center;">
					<?php echo $cert['expiration_date']; ?>
				</td>
			</tr>
		<?php endforeach; ?>
	</table>
<?php else: ?>
	<?php //No expiring certs ?>
	<p>Phew! No Certificates Expiring in the next <?php echo $this->get_addon_setting( 'ssl_days' ); ?> days.</p>
<?php endif; ?>