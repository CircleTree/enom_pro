<?php
/**
 * Project: enom_pro
 * Build: @BUILD_DATE@
 * Version: @VERSION@
 */
/**
 * @var $this enom_pro
 */
$stats = $this->getAccountStats();
?>
<div class="enom_pro_widget">
	<table class="table-hover">
		<tbody>
		<tr>
			<td class="enom_stat_button">
				<a class="btn btn-success <?php echo( $stats['registered'] > 0 ? '' : 'disabled' ) ?>"
					 data-tab="registered"
					 href="<?php echo enom_pro::MODULE_LINK . '&action=get_domains'; ?>"
					 title="View Domains">
					<?php echo $stats['registered']; ?>
				</a>

				<div class="enom_pro_loader small hidden"></div>
			</td>
			<td class="enom_stat_label">Registered Domains</td>
		</tr>
		<tr>
			<td colspan="2">
				<div id="enom_pro_registered"></div>
			</td>
		</tr>

		<tr>
			<td class="enom_stat_button">
				<a class="btn btn-warning <?php echo( $stats['expiring'] > 0 ? '' : 'disabled' ) ?>"
					 data-tab="expiring"
					 href="<?php echo enom_pro::MODULE_LINK . '&action=get_domains&tab=expiring'; ?>">
					<?php echo $stats['expiring']; ?>
				</a>

				<div class="enom_pro_loader small hidden"></div>
			</td>
			<td class="enom_stat_label">Expiring Domains</td>
		</tr>
		<tr>
			<td colspan="2">
				<div id="enom_pro_expiring"></div>
			</td>
		</tr>

		<tr>
			<td class="enom_stat_button">
				<a class="btn btn-danger <?php echo( $stats['expired'] > 0 ? '' : 'disabled' ); ?>"
					 data-tab="expired"
					 href="<?php echo enom_pro::MODULE_LINK . '&action=get_domains&tab=expired'; ?>">
					<?php echo $stats['expired']; ?>
				</a>

				<div class="enom_pro_loader small hidden"></div>
			</td>
			<td class="enom_stat_label">Expired Domains</td>
		</tr>
		<tr>
			<td colspan="2">
				<div id="enom_pro_expired"></div>
			</td>
		</tr>
		<tr>
			<td class="enom_stat_button">
				<a class="btn btn-info <?php echo( $stats['redemption'] > 0 ? '' : 'disabled' ); ?>"
					 data-tab="redemption"
					 href="<?php echo enom_pro::MODULE_LINK . '&action=get_domains&tab=redemption'; ?>">
					<?php echo $stats['redemption']; ?>
				</a>

				<div class="enom_pro_loader small hidden"></div>
			</td>
			<td class="enom_stat_label">Redemption Period</td>
		</tr>
		<tr>
			<td colspan="2">
				<div id="enom_pro_redemption"></div>
			</td>
		</tr>
		<tr>
			<td class="enom_stat_button">
				<a class="btn btn-inverse <?php echo( $stats['ext_redemption'] > 0 ? '' : 'disabled' ); ?>"
					 href="http://www.enom.com/domains/Domain-Manager.aspx?tab=redemption"
					 target="_blank">
					<?php echo $stats['ext_redemption']; ?>
				</a>

				<div class="enom_pro_loader small hidden"></div>
			</td>
			<td class="enom_stat_label">Extended Redemption Period</td>
		</tr>
		</tbody>
	</table>
</div>