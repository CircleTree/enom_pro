<?php
/**
 * Project: enom_pro
 * Build: @BUILD_DATE@
 * Version: @VERSION@
 */
$result = mysql_query( 'SELECT * FROM `tbldomainpricing` ORDER BY `order` ASC' ); ?>
<?php if ( mysql_num_rows( $result ) > 0 ) : ?>
	<?php if ( isset( $_GET['sorted'] ) ) : ?>
		<div class="alert alert-success">
			New order saved!
		</div>
	<?php endif; ?>
	<?php if ( ! enom_pro_controller::isDismissed( 'pricing-sortable-drag' ) ) : ?>
		<div class="alert alert-success fade in">
			<button type="button"
			        class="close"
			        data-dismiss="alert"
			        data-alert="pricing-sortable-drag"
			        aria-hidden="true">&times;</button>
			<h3>Drag &amp; drop TLD Sorting</h3>

			<img src="../modules/addons/enom_pro/images/tld-drag-drop-sort.jpg" class="img-rounded img-responsive"
			     style="max-height: 300px;" />

		</div>
	<?php endif; ?>
	<?php if ( ! enom_pro_controller::isDismissed( 'pricing-sortable' ) ) : ?>
		<div class="alert alert-success fade in">
			<button type="button"
			        class="close"
			        data-dismiss="alert"
			        data-alert="pricing-sortable"
			        aria-hidden="true">&times;</button>
			<h3>Bulk TLD Sorting</h3>

			<p>
				You can also
				<a class="btn btn-xs btn-default" href="#bulk-sort">bulk sort</a>
				pricing alphabetically.<br />
			</p>

			<div class="alert alert-warning">
				Please keep in mind that using the
				<a class="btn btn-default btn-xs" href="#bulk-sort">bulk sorter</a>
				will overwrite any ordering changes made here.
			</div>
		</div>
	<?php endif; ?>

	<table class="table table-hover table-condensed">
		<thead>
		<tr class="sortLoader">
			<th>
				<span class="ep_tt" title="Drag &amp; Drop to by TLDs">TLD</span>
				<div class="enom_pro_loader hidden"></div>
			</th>
		</tr>
		</thead>
		<tbody class="ep_sortable">
		<?php while ( $row = mysql_fetch_assoc( $result ) ) : ?>
			<tr id="tld_<?php echo $row['id'] ?>">
				<td>
					<span class="enom-pro-icon enom-pro-icon-sort"></span>
					<?php echo $row['extension'] ?>
				</td>
			</tr>
		<?php endwhile; ?>
		</tbody>
		<tfoot>
		<tr>
			<td>
				<span class="enom_pro_loader hidden"></span>
			</td>
		</tr>
		</tfoot>
	</table>

	<div class="btn btn-success btn-block">Save Order</div>

	<h4 id="bulk-sort">Bulk Sort Pricing</h4>
	<div class="well">
		<form method="get" action="<?php echo enom_pro::MODULE_LINK ?>" class="form-inline row">
			<input type="hidden" name="action" value="sort_domains" />

			<div class="radio radio-inline col-xs-2">
				<label title="Sort alphabetically, from A-Z" class="ep_tt col-xs-6">
					<input type="radio" name="order" value="asc" checked />
					<span class="enom-pro-icon enom-pro-icon-sort-by-alpha" title="A-Z"></span>
				</label>
				<label title="Sort reverse alphabetically, from Z-A" class="ep_tt col-xs-6">
					<input type="radio" name="order" value="desc" />
					<span class="enom-pro-icon enom-pro-icon-sort-by-alpha-alt" title="Z-A"></span>
				</label>
			</div>

			<fieldset name="Ignore" title="Ignore" class="col-xs-2">
				<h6>Keep these at the top of the list:</h6>
				<input type="checkbox" name="ignore[.com]" id="ignoreCOM" checked />
				<label for="ignoreCOM">.com</label>

				<input type="checkbox" name="ignore[.net]" id="ignoreNET" checked />
				<label for="ignoreNET">.net</label>
			</fieldset>
			<div class="col-xs-12">
				<span
					class="col-xs-3 text-center text-danger">
					<span class="enom-pro-icon-warning"></span>
					This will bulk sort ALL tlds.</span>
				<span class="clearfix"></span>
				<input type="submit" class="btn btn-danger col-xs-3" value="Re-Sort All TLDs" />
			</div>
		</form>

	</div>
	<script>
		jQuery(function ($) {
			enom_pro.initTLDSortPage();
		});
	</script>
<?php else: ?>
	<div class="alert alert-warning">
		<h1>No TLD pricing found in WHMCS</h1>

		<p>Please
			<a class="btn btn-default btn-xs"
			   href="addonmodules.php?module=enom_pro&view=pricing_import">import TLD pricing
			</a>
		   , and then come back to sort.
		</p>
	</div>
<?php endif; ?>
