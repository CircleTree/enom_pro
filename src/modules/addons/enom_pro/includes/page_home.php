<?php
/**
 * Project: enom_pro
 * @license GPL v2
 * Build: @BUILD_DATE@
 * Version: @VERSION@
 */
?>
<div class="well well-sm">
	<h2>Welcome to eNom PRO!</h2>
	<a class="btn btn-success large"
	   href="<?php echo enom_pro::MODULE_LINK . '&view=domain_import' ?>">Import
	                                                                      Domains
		<span class="enom-pro-icon enom-pro-icon-domains"></span>
	</a>
	<a class="btn btn-success large"
	   href="<?php echo enom_pro::MODULE_LINK . '&view=pricing_import' ?>">Import TLD Pricing
		<span class="enom-pro-icon enom-pro-icon-tag"></span>
	</a>
	<a class="btn btn-primary large"
	   href="<?php echo enom_pro::MODULE_LINK . '&view=help' ?>">View Help
		<span class="enom-pro-icon enom-pro-icon-question"></span>
	</a>

</div>
<div id="enom_pro_admin_widgets" class="row">
	<div class="col-xs-6">
		<?php enom_pro::render_admin_widget( 'enom_pro_admin_balance' ); ?>
		<?php enom_pro::render_admin_widget( 'enom_pro_admin_expiring_domains' ); ?>
		<?php enom_pro::render_admin_widget( 'enom_pro_admin_pending_domain_verification' ); ?>
	</div>
	<div class="col-xs-6">
		<?php enom_pro::render_admin_widget( 'enom_pro_admin_transfers' ); ?>
		<?php enom_pro::render_admin_widget( 'enom_pro_admin_ssl_certs' ); ?>
	</div>
</div>
