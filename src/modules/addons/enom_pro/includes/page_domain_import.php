<?php
/**
 * @var $this enom_pro
 */
?>
<div id="enom_pro_import_page">
	<?php if ( isset( $_GET['cleared'] ) ) : ?>
		<div class="alert alert-info slideup">
			<h3>Cache Cleared</h3>
		</div>
	<?php endif; ?>
	<div class="enom_pro_loader" id="top_loader"></div>
	<table id="meta">
		<tr>
			<td>
				<form method="GET" action="<?php echo $_SERVER['PHP_SELF']; ?>" id="filter_form">
					<?php $options = array( 'All', 'Imported', 'Unimported' ); ?>
					<label for="filter">Filter</label>
					<select name="show_only" id="filter">
						<?php foreach ( $options as $option ) : ?>
							<option value="<?php echo strtolower( $option ); ?>"
								<?php if ( isset( $_GET['show_only'] ) && $_GET['show_only'] == strtolower( $option ) ): ?> selected<?php endif ?>>
								<?php echo $option; ?>
							</option>
						<?php endforeach; ?>
					</select>
					<input type="hidden" name="module" value="enom_pro" />
					<input type="hidden" name="view" value="import" />
					<input type="submit" value="Go" class="no-js" />
				</form>
			</td>
			<td>
				<form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>" id="per_page_form">
					<?php
					$config   = enom_pro_config();
					$options  = $config['fields'];
					$per_page = explode( ',', $options['import_per_page']['Options'] );
					?>
					<label for="per_page">Per Page</label>
					<select name="per_page" id="per_page">
						<?php foreach ( $per_page as $num ) : ?>
							<option value="<?php echo $num ?>"
								<?php if ( enom_pro::get_addon_setting( 'import_per_page' ) == $num ): ?> selected<?php endif ?>>
								<?php echo $num ?>
							</option>
						<?php endforeach; ?>
					</select>
					<input type="hidden" name="action" value="set_results_per_page" />
					<input type="submit" value="Go" class="no-js" />
				</form>
			</td>
			<td>
				<form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>" id="search_form">
					<input type="text" name="s" placeholder="Search"
					       value="<?php echo isset( $_GET['s'] ) ? htmlentities( $_GET['s'] ) : ''; ?>" />
					<input type="submit" value="Go" />
				</form>
			</td>
		</tr>
	</table>
	<div id="import_ajax_messages" class="alert alert-danger hidden"></div>
	<form method="POST" id="import_table_form">
		<input type="hidden" name="action" value="render_import_table" />
		<input type="hidden" name="start" value="<?php echo isset( $_GET['start'] ) ? (int) $_GET['start'] : 1; ?>" />
		<input type="hidden" name="s" value="<?php echo isset( $_GET['s'] ) ? htmlentities( $_GET['s'] ) : ''; ?>" />
		<input type="hidden" name="per_page" value="<?php echo enom_pro::get_addon_setting( 'import_per_page' ) ?>" />
		<input type="hidden" name="show_only" value="<?php if ( in_array( $_GET['show_only'],
			array( 'imported', 'unimported' ) ) ): echo $_GET['show_only']; endif; ?>" />
		<?php if ( isset( $_GET['domain'] ) ) : ?>
			<input type="hidden" name="domain" value="<?php echo htmlentities( $_GET['domain'] ); ?>" />
		<?php endif; ?>
		<div id="domains_target">
		</div>
	</form>
	<div id="create_order_dialog" title="Create Order">
		<form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>" id="create_order_form" class="container-fluid">
			<input type="hidden" name="action" value="add_enom_pro_domain_order" />
			<input type="hidden" name="domaintype" value="register" />
			<input type="hidden" name="domain" value="" id="domain_field2" />

			<div class="row">
				<div id="ajax_messages" class="alert col-xs-12" style="display: none;"></div>
				<div class="enom_pro_loader col-xs-6 col-xs-push-3" style="display: none;"></div>
			</div>
			<div id="order_process">
				<div class="row">
					<div class="alert alert-warning col-xs-12" id="auto-renew-warning" style="display: none;">
						<p> Auto-Renew is enabled for this domain.
						    Make sure to disable it after it has been imported into WHMCS,
						    to avoid double billing.
						</p>
					</div>
					<div class="col-xs-12">
						<label>
							Domain<br />
							<input type="text" name="domain_display" value="" id="domain_field"
							       disabled="disabled" readonly="readonly" size="60" class="col-xs-12" />
						</label>
					</div>
				</div>
				<div class="row">

					<label class="col-xs-8">
						Client
						<select name="clientid" id="client_select" style="width: 100%">
							<option value="default">Select...</option>
						</select>
					</label>
					<label class="col-xs-4">
						Years<br />
						<select name="regperiod" id="register_years" class="form-control">
							<?php for ( $i = 1; $i <= 10; $i ++ ) {
								echo '<option value="' . $i . '">' . $i . '</option>';
							} ?>
						</select>
					</label>
				</div>

				<div class="row">
					<div class="col-xs-6">
						<label for="expiresdate">Expires</label>
						<input id="expiresdate" type="text" name="expiresdatelabel" value="" readonly disabled
						       class="form-control" tabindex="-1" />
						<input type="hidden" name="expiresdate" value="" readonly />
					</div>
					<div class="col-xs-6">
						<label for="nextduedate">Next Due</label>
						<input id="nextduedate" type="text" name="nextduedatelabel" value="" readonly disabled
						       class="form-control" tabindex="-1" />
						<input type="hidden" name="nextduedate" value="" readonly />
					</div>
					<div class="col-xs-12">
						<span class="help-block">Change relative due dates in settings.</span>
					</div>
				</div>

				<div class="row">

					<fieldset class="col-xs-4">
						<legend>Domain Options</legend>
						<div class="checkbox">

							<label for="dnsmanagement" class="btn btn-xs btn-default">
								<input type="checkbox" name="dnsmanagement" id="dnsmanagement" />
								DNS Management
							</label>
						</div>

						<div class="checkbox">

							<label for="idprotection" class="btn btn-xs btn-default">
								<input type="checkbox" name="idprotection" id="idprotection" />
								ID Protect
							</label>
						</div>
					</fieldset>

					<fieldset class="col-xs-7 col-xs-push-1">
						<legend>Order Options</legend>
						<div class="form-group">
							<label for="payment_gateway" class="col-xs-12">Payment gateway</label>
							<?php try {
								$methods = enom_pro::whmcs_api( 'getpaymentmethods', array() ); ?>
								<select name="paymentmethod" id="payment_gateway" class="form-control">
									<?php foreach ( $methods['paymentmethods']['paymentmethod'] as $gateway ) {
										echo '<option value="' . $gateway['module'] . '">' . $gateway['displayname'] . '</option>';
									}
									?>
								</select>
							<?php } catch ( Exception $e ) { ?>
								<div class="alert alert-danger">No Payment Gateways Found</div>
							<?php } ?>
						</div>
						<div class="checkbox">
							<label for="orderemail" class="btn btn-xs btn-default">
								<input type="checkbox" name="noemail" id="orderemail" />
								Send order confirmation email
							</label>
						</div>
						<div class="row">
							<div class="checkbox">
								<label for="generateinvoice" class="btn btn-xs btn-default">
									<input type="checkbox" name="noinvoice" id="generateinvoice" />
									Generate Invoice
								</label>
							</div>
							<div id="invoice_email" style="display: none;" class="checkbox">
								<label for="noinvoiceemail" class="btn btn-xs btn-default">
									<input type="checkbox" name="noinvoiceemail" id="noinvoiceemail" />
									Send Invoice Notification Email
								</label>
							</div>
						</div>
						<div class="row">
							<div class="checkbox">
								<label for="free_domain" class="btn btn-xs btn-default">
									<input type="checkbox" name="free_domain" id="free_domain" />
									Free Domain
								</label>
							</div>
						</div>
					</fieldset>
				</div>
				<div class="center-block">
					<input type="submit" value="Create Order" class="btn btn-success btn-block createOrder" />
				</div>
			</div> <?php //end #order_process ?>

			<div class="btn btn-block btn-success" id="import_next_button" style="display: none;">
				Import Next &rarr;
			</div>
		</form>
	</div>
	<table id="domain_caches">
		<tr>
			<td>
				<a class="btn btn-inverse btn-xs btn-block"
				   href="addonmodules.php?module=enom_pro&action=clear_cache">Clear Cache
				</a>
				<br />
				Domains Cached from
				<span class="domains_cache_time"><?php echo $this->get_domain_cache_date(); ?></span>
			</td>
			<td id="local_storage">
			</td>
		</tr>
	</table>
	<div class="enom_pro_loader hidden" id="loader_bottom"></div>
</div>
<script>
	jQuery(function ($) {
		enom_pro.initDomainImport();
	});
</script>