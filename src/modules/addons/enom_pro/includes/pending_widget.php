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
	<tr>
		<td colspan="2">
			<div class="well well-sm">
				<p class="text-muted">Report Data From <?php echo $this->get_validation_cache_date(); ?>
					<?php if ($this->isValidationCacheStale()) :?>
						<a href="#" class="btn btn-xs btn-info flushValidateCache">Flush Cache</a></p>
					<?php endif;?>
			</div>
		</td>
	</tr>
</table>
	<div class="well hidden verificationDomains">
		<table class="table table-bordered">
			<thead>
				<tr>
					<th>Domain</th>
					<th>Status</th>
					<th>Suspension Date</th>
					<th>Authorization Email</th>
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
						<a href="<?php echo enom_pro::MODULE_LINK ?>&action=resend_raa_email"
							 data-domain="<?php echo $domain['domainname'] ?>"
							 data-toggle="popover"
							 data-trigger="hover"
							 data-placement="left"
							 title="Authorization Email: <?php echo $domain['newemailaddress'] ?>"
							 data-content="Last Emailed: <?php echo $domain['lastemailsenttime']; ?>. Click to resend."
							 class="btn btn-default btn-xs pop resendAuth">
							Resend
							<span class="enom_pro_loader small hidden"></span>
						</a>
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
		});
		$(".resendAuth").on('click', function  (){
			var $this = $(this),
				$loader = $this.find('.enom_pro_loader');
			$loader.removeClass('hidden');
			$this.addClass('disabled').attr('disabled', true);
			$.ajax({
				url: $this.attr('href'),
				data: {'domain' : $this.data('domain')},
				success: function  (data){
					$loader.addClass('hidden');
					$this.text('Re-Sent');
				},
				error: function  (xhr){
					alert(xhr.responseText);
				}
			});
			return false;
		});
		$(".verification").on('click', function  (){
			$(".verificationDomains").removeClass('hidden');
			return false;
		});
	})
</script>