<?php
if ( empty( $domains ) ) {
	return false;
}
/** @var enom_pro_controller $this */
$meta = $this->enom->getListMeta();
/**
 * @var $domains array
 */
$first_page = ! isset( $_GET['start'] ) ? true : false;
if ( $first_page ):
	?>
<div class="enom_pro_widget">
	<table class="table-hover domain-widget-response">
	<thead>
	<tr>
		<th>Domain</th>
		<th>Expire<?php echo ( ! isset( $_GET['tab'] ) || $tab == 'expiring' ) ? 's' : 'd'; ?></th>
		<th>Enom DNS</th>
		<th>Privacy</th>
		<th>Auto-Renew</th>
		<th colspan="2">Actions</th>
	</tr>
	</thead>
	<tbody>
<?php endif; ?>
<?php foreach ( $domains as $key => $domain ): ?>
	<tr>
		<td><?php echo $domain['sld'] . '.' . $domain['tld']; ?></td>
		<td>
			<?php $expires = @new DateTime( $domain['expiration'] );
			$now           = @new DateTime( 'now' );
			$diff          = $now->diff( $expires );
			echo $diff->format( '%R%a days' );
			?>
		</td>
		<td><span
				title="<?php echo ( $domain['enom_dns'] == 1 ) ? 'Enom DNS' : 'Self Hosted DNS'; ?>"
				class="badge ep_tt <?php echo ( $domain['enom_dns'] == 1 ) ? 'alert-success' : 'alert-danger' ?>">
                        <?php echo ( $domain['enom_dns'] == 1 ) ? 'On' : 'Off'; ?>
                    </span>
		</td>
		<td>
                    <span
	                    title="<?php echo ( $domain['privacy'] == 1 ) ? 'Privacy Enabled' : 'Privacy Disabled'; ?>"
	                    class="badge ep_tt <?php echo ( $domain['privacy'] == 1 ) ? 'alert-success' : 'alert-danger' ?>">
                        <?php echo ( $domain['privacy'] == 1 ) ? 'On' : 'Off'; ?>
                    </span>
		</td>
		<td>
                    <span
	                    title="<?php echo ( $domain['autorenew'] == 1 ) ? 'Auto-Renew Enabled' : 'Auto-Renew Disabled'; ?>"
	                    class="badge ep_tt <?php echo ( $domain['autorenew'] == 1 ) ? 'alert-success' : 'alert-danger' ?>">
                        <?php echo ( $domain['autorenew'] == 1 ) ? 'On' : 'Off'; ?>
                    </span>
		</td>
		<td>
			<?php if ( isset( $domain['userid'] ) ) : ?>
				<a class="btn btn-primary" href="clientssummary.php?userid=<?php echo $domain['userid']; ?>">User</a>
			<?php endif; ?>
		</td>
		<td>
			<?php if ( isset( $domain['domainid'] ) ) : ?>
				<a class="btn btn-primary" href="clientsdomains.php?id=<?php echo $domain['domainid']; ?>">Domain</a>
			<?php else: ?>
				<a class="btn btn-primary"
				   href="addonmodules.php?module=enom_pro&view=domain_import&s=<?php echo urlencode( $domain['sld'] . '.' . $domain['tld'] ); ?>">Import
				</a>
			<?php endif; ?>
		</td>
	</tr>
	<?php if ( ( $key == ( count( $domains ) - 1 ) ) && $meta['total_domains'] > $meta['next_start'] ) : ?>
		<tr>
			<td colspan="7">
				<a class="btn btn-block btn-default btn-xs load_more"
				   href="<?php echo enom_pro::MODULE_LINK . '&action=get_domains' . ( isset( $_GET['tab'] ) ? '&tab=' . $_GET['tab'] : '' ) . '&start=' . ( count( $domains ) + $start ); ?>">Load More
				</a>
				<div class="enom_pro_loader small hidden"></div>
			</td>
		</tr>
	<?php endif; ?>
<?php endforeach; ?>
<?php if ( $first_page ) : ?>
	</tbody>
	</table>
<?php endif; ?>
</div>
