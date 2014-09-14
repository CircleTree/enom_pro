<?php
/**
 * Project: enom_pro
 * Build: @BUILD_DATE@
 * Version: @VERSION@
 */
/**@var enom_pro $this */
$stats = $this->getDomainVerificationStats();
?>
<table class="table-responsive table">
	<tr>
		<td class="enom_stat_button">
			<a class="btn btn-success verification <?php echo( $stats['pending_verification'] > 0 ? '' : 'disabled' ) ?>"
				 href="#">
				<?php echo $stats['pending_verification']; ?>
			</a>
		</td>
		<td class="enom_stat_label">Pending Verification</td>
	</tr>
	<tr>
		<td class="enom_stat_button">
			<a class="btn btn-warning verification <?php echo( $stats['pending_suspension'] > 0 ? '' : 'disabled' ) ?>"
				 href="#">
				<?php echo $stats['pending_suspension']; ?>
			</a>
		</td>
		<td class="enom_stat_label">Pending Suspension</td>
	</tr>
	<tr>
		<td class="enom_stat_button">
			<a class="btn btn-danger verification <?php echo( $stats['suspended'] > 0 ? '' : 'disabled' ) ?>"
				 href="#">
				<?php echo $stats['suspended']; ?>
			</a>
		</td>
		<td class="enom_stat_label">Suspended</td>
	</tr>
</table>
	<div class="well hidden verificationDomains">
		<table class="table table-bordered">
			<thead>
				<tr>
					<th>Domain</th>
					<th>Status</th>
					<th>Suspension Date</th>
					<th>Resend</th>
				</tr>
			</thead>
			<?php foreach ($stats['domains'] as $domain) : ?>
				<tr>
					<td>
						<?php echo $domain['domainname'] ?>
					</td>
					<td>
						<span class="label <?php if ('suspended' == strtolower($domain['verificationstatus'])) { echo 'label-danger'; } else { echo 'label-primary'; }?>">
							<?php echo $domain['verificationstatus'] ?>
						</span>
					</td>
					<td>
						<span class="ep_tt" title="Date: <?php echo $domain['suspensiondate'] ?>">
							<?php echo enom_pro::time_ago(strtotime($domain['suspensiondate']), 2) ?>
						</span>
					</td>
					<td>

						<a href="#"
							 data-domain="<?php echo $domain['domainname'] ?>"
							 data-toggle="popover"
							 data-trigger="hover"
							 data-placement="left"
							 title="Authorization Email: <?php echo $domain['newemailaddress'] ?>"
							 data-content="Last Emailed: <?php echo $domain['lastemailsenttime']; ?>. Click to resend."
							 class="btn btn-primary btn-xs pop">Resend</a>
					</td>
				</tr>

			<?php endforeach; ?>
		</table>
	</div>
<script>jQuery(function($) {
		$(".ep_tt").tooltip();
		$(".pop").popover();
		$(".verification:not('.disabled')").popover({
			title: 'View All',
			content: 'Click to see a full list of domains pending verification',
			trigger: 'hover',
			placement: 'top'
		})
		$(".verification").on('click', function  (){
			$(".verificationDomains").removeClass('hidden');
			return false;
		});
	})
</script>