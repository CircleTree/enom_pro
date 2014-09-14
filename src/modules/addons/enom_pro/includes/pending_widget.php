<?php
/**
 * Project: enom_pro
 * Build: @BUILD_DATE@
 * Version: @VERSION@
 */
/**@var enom_pro $this */
$stats = $this->getDomainVerificationStats();
echo '<pre>';
print_r($stats);
echo '</pre>';
?>
<tr>
	<td class="enom_stat_button">
		<a class="btn btn-danger <?php echo( $stats['verification'] > 0 ? '' : 'disabled' ) ?>"
			 data-tab="verification"
			 href="<?php echo enom_pro::MODULE_LINK . '&action=get_domains&tab=verification'; ?>">
			<?php echo $stats['verification']; ?>
		</a>

		<div class="enom_pro_loader small hidden"></div>
	</td>
	<td class="enom_stat_label">Pending Verification</td>
</tr>
<tr>
	<td colspan="2">
		<div id="enom_pro_expiring"></div>
	</td>
</tr>