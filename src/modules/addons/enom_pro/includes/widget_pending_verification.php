<?php
/**
 * Project: enom_pro
 * Build: @BUILD_DATE@
 * Version: @VERSION@
 */
/**@var enom_pro $this */
$stats = $this->getDomainVerificationStats();
?>
<div class="enom_pro_widget">
	<table class="table-responsive table">
		<tr>
			<td class="text-center">
				<a class="btn btn-success verification <?php echo( $stats['pending_verification'] > 0 ? '' : 'disabled' ) ?>"
				   href="<?php echo enom_pro::MODULE_LINK . '&action=get_domain_verification&tab=pending_verification'; ?>">
					<?php echo $stats['pending_verification']; ?>
				</a>
			</td>
			<td class="enom_stat_label">Pending Verification</td>
		</tr>
		<tr>
			<td class="text-center">
				<a class="btn btn-warning verification <?php echo( $stats['pending_suspension'] > 0 ? '' : 'disabled' ) ?>"
				   href="<?php echo enom_pro::MODULE_LINK . '&action=get_domain_verification&tab=pending_suspension'; ?>">
					<?php echo $stats['pending_suspension']; ?>
				</a>
			</td>
			<td class="enom_stat_label">Pending Suspension</td>
		</tr>
		<tr>
			<td class="text-center">
				<a class="btn btn-danger verification <?php echo( $stats['suspended'] > 0 ? '' : 'disabled' ) ?>"
				   href="<?php echo enom_pro::MODULE_LINK . '&action=get_domain_verification&tab=suspended'; ?>">
					<?php echo $stats['suspended']; ?>
				</a>
			</td>
			<td class="enom_stat_label">Suspended</td>
		</tr>
		<tr>
			<td colspan="2">
				<div class="well well-sm">
					<p class="text-muted">Report Data From <?php echo $this->get_validation_cache_date(); ?>
						<?php if ( $this->isValidationCacheStale() ) : ?>
						<a href="#" class="btn btn-xs btn-inverse flushValidateCache">Flush Cache <span class="enom-pro-icon-trash"></span> </a>
					</p>
					<?php endif; ?>
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
			<?php foreach ( $stats['domains'] as $domain ) : ?>
				<tr>
					<td>
						<?php echo $domain['domainname'] ?>
					</td>
					<td>
							<span class="label <?php if ( 'suspended' == strtolower( $domain['verificationstatus'] ) ) {
								echo 'label-danger';
							} else {
								echo 'label-primary';
							} ?>">
								<?php echo $domain['verificationstatus'] ?>
							</span>
					</td>
					<td>
							<span class="ep_tt" title="Date: <?php echo $domain['suspensiondate'] ?>">
								<?php echo enom_pro::time_ago( strtotime( $domain['suspensiondate'] ), 2 ) ?>
							</span>
					</td>
					<td>
						<?php $content = '' ?>
						<?php if ( ! empty( $domain['lastemailsenttime'] ) ) : ?>
							<?php $content .= 'Last Emailed: ' . $domain['lastemailsenttime'] . '.' . PHP_EOL; ?>
						<?php endif; ?>
						<?php $content .= 'Click to resend.' ?>
						<a href="<?php echo enom_pro::MODULE_LINK ?>&action=resend_raa_email"
						   data-domain="<?php echo $domain['domainname'] ?>"
						   data-toggle="popover"
						   data-trigger="hover"
						   data-placement="left"
						   title="Authorization Email: <?php echo $domain['newemailaddress'] ?>"
						   data-content="<?php echo $content; ?>"
						   class="btn btn-default btn-xs pop resendAuth">
							Resend
							<span class="enom_pro_loader small hidden"></span>
						</a>
					</td>
				</tr>

			<?php endforeach; ?>
		</table>
	</div>
	<script>
		jQuery(function ($) {
			if (typeof(jQuery.fn.tooltip) == 'function') {
				$(".ep_tt").tooltip();
			}
			var verifyLabel = 'Click to see a full list of domains pending verification';
			var $verification = $(".verification");
			if (typeof(jQuery.fn.popover) == 'function') {
				$(".pop").popover();
				$(".verification:not('.disabled')").popover({
					title:     'View All',
					content:   verifyLabel,
					trigger:   'hover',
					container: 'body',
					placement: 'top'
				});
			} else {
				$verification.attr('title', verifyLabel);
			}
			$(".resendAuth").on('click', function () {
				var $this   = $(this),
				    $loader = $this.find('.enom_pro_loader');
				$loader.removeClass('hidden');
				$this.addClass('disabled').attr('disabled', true);
				$.ajax({
					url:     $this.attr('href'),
					data:    {'domain': $this.data('domain')},
					success: function (data) {
						$loader.addClass('hidden');
						$this.text('Re-Sent');
					},
					error:   function (xhr) {
						alert(xhr.responseText);
					}
				});
				return false;
			});
			$verification.on('click', function () {
				var $verificationDomains = $(".verificationDomains");
				$verificationDomains.removeClass('hidden');
				var offset = $verificationDomains.offset().top;
				$('html, body').animate({scrollTop: offset}, 1000);
				return false;
			});
		});
	</script>
</div>
