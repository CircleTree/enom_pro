<?php
/**
 * Project: enom_pro
 * Build: @BUILD_DATE@
 * Version: @VERSION@
 */
/** @var $this enom_pro */

$expiring_certs = $this->getExpiringCerts(); ?>
<div class="enom_pro_widget">
	<div class="alert <?php echo( count( $expiring_certs ) > 0 ? 'alert-danger' : 'alert-success' ) ?>">
		<?php if ( count( $expiring_certs ) > 0 ) : ?>
		<table class="table table-condensed table-hover">
			<tr>
				<th>Domain</th>
				<th>Status</th>
				<th>Product</th>
				<th>Expiration Date</th>
				<th>Hide</th>
			</tr>
			<?php foreach ( $expiring_certs as $cert ) : ?>
			<tr>
				<td>
					<?php if ( count( $cert['domain'] ) > 0 ): ?>
						<?php echo rtrim( implode( ', ', array_values( $cert['domain'] ) ),
							', ' ); ?>
					<?php else: ?>
						Not Issued
					<?php endif; ?>
				</td>
				<td style="text-align:center;">
					<a href="http://www.enom.com/secure/configure-ssl-certificate.aspx?certid=<?php echo $cert['CertID'] ?>"
					   target="_blank"
					   class="btn btn-default">
						<?php echo $cert['status']; ?>
					</a>
				</td>
				<td style="text-align:center;">
					<?php echo $cert['desc']; ?>
				</td>
				<td style="text-align:center;">
					<?php echo $cert['expiration_date']; ?>
				</td>
				<td>
					<a href="index.php?action=enom_pro_hide_ssl&certid=<?php echo $cert['CertID']; ?>">[x]</a>
				</td>
				<?php endforeach; ?>
				<?php elseif ( ! isset( $_REQUEST['show_all'] ) && "" != ( $hidden = enom_pro::get_addon_setting( 'ssl_hidden' ) ) ): ?>
					<?php //May be hidden ?>
					<?php $count = count( $hidden ); ?>
					<p>No Certificates Expiring in the next <?php echo $this->get_addon_setting( 'ssl_days' ); ?> days.</p>

					<p><b>But there <?php echo $count == 1 ? 'is' : 'are' ?> <?php echo $count ?> hidden certificates</b>
						<a href="#" class="show_hidden_ssl btn btn-default">Show All</a>
					</p>
				<?php else: ?>
					<?php //No expiring certs ?>
					<p>Phew! No Certificates Expiring in the next <?php echo $this->get_addon_setting( 'ssl_days' ); ?> days.</p>
					<?php
				endif; ?>
	</div>
</div>
