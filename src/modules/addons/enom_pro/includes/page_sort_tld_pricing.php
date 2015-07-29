<?php
/**
 * Project: enom_pro
 * Build: @BUILD_DATE@
 * Version: @VERSION@
 */
$result = mysql_query( 'SELECT * FROM `tbldomainpricing` ORDER BY `order` ASC' ); ?>
<?php if ( $result ) : ?>
	<?php if ( isset( $_GET['sorted'] ) ) : ?>
		<div class="alert alert-success">
			New order saved!
		</div>
	<?php endif; ?>
	<?php if ( ! enom_pro_controller::isDismissed( 'pricing-sortable' ) ) : ?>
		<div class="alert alert-success fade in">
			<button type="button"
			        class="close"
			        data-dismiss="alert"
			        data-alert="pricing-sortable"
			        aria-hidden="true">&times;</button>
			<p>
				<b>Drag &amp; Drop TLD pricing Reordering!</b> <br />
				Simply drag the TLD's in the order you'd like to use. <br />
				<b>Bulk TLD Sorting</b> <br />
				You can <a href="#bulk-sort">bulk sort</a> pricing alphabetically.<br />
			</p>

			<div class="alert alert-warning">
				Please keep in mind that using the <a href="#bulk-sort">bulk sorter</a>
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
	</table>
	<h3 id="bulk-sort">Bulk Re-Sort Pricing</h3>
	<div class="well">
		<form method="get" action="<?php echo enom_pro::MODULE_LINK ?>" class="form-inline row">
			<input type="hidden" name="action" value="sort_domains" />

			<div class="radio radio-inline col-xs-1">
				<label>
					<input type="radio" name="order" value="asc" checked />
					<span class="enom-pro-icon enom-pro-icon-sort-by-alpha" title="A-Z"></span>
				</label>
			</div>
			<div class="radio radio-inline col-xs-1">
				<label>
					<input type="radio" name="order" value="desc" />
					<span class="enom-pro-icon enom-pro-icon-sort-by-alpha-alt" title="Z-A"></span>
				</label>
			</div>

			<fieldset name="Ignore" title="Ignore" class="col-xs-2">
				<legend>Keep these at the top:</legend>
				<input type="checkbox" name="ignore[.com]" id="ignoreCOM" checked />
				<label for="ignoreCOM">.com</label>

				<input type="checkbox" name="ignore[.net]" id="ignoreNET" checked />
				<label for="ignoreNET">.net</label>
			</fieldset>
			<input type="submit" class="btn btn-primary col-xs-2 col-xs-push-1" value="Save new order" />
		</form>

	</div>
<?php else: ?>
	<h1>No pricing data found in WHMCS</h1>
<?php endif; ?>
