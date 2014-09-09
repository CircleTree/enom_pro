<?php
/**
 * Project: enom_pro
 * Build: @BUILD_DATE@
 * Version: @VERSION@
 */
$result = mysql_query('SELECT * from `tbldomainpricing` ORDER BY `order` ASC'); ?>
<?php if ($result) :?>
	<?php if (isset($_GET['sorted'])) :?>
		<div class="alert alert-success">
			New order saved!
		</div>
	<?php endif;?>
	<form method="get" action="<?php echo enom_pro::MODULE_LINK ?>" class="well form-inline row">
		<input type="hidden" name="action" value="sort_domains" />
			<div class="radio radio-inline col-xs-1">
				<label>
					<input type="radio" name="order" value="asc" checked/>
					<span class="enom-pro-icon enom-pro-icon-sort-by-alpha" title="A-Z"></span>
				</label>
			</div>
			<div class="radio radio-inline col-xs-1">
				<label>
					<input type="radio" name="order"  value="desc"/>
						<span class="enom-pro-icon enom-pro-icon-sort-by-alpha-alt" title="Z-A"></span>
				</label>
			</div>

			<fieldset name="Ignore" title="Ignore" class="col-xs-2">
				<legend>Keep these at the top:</legend>
				<input type="checkbox" name="ignore[.com]" id="ignoreCOM" checked/>
				<label for="ignoreCOM">.com</label>

				<input type="checkbox" name="ignore[.net]" id="ignoreNET" checked/>
				<label for="ignoreNET">.net</label>
			</fieldset>
		<input type="submit" class="btn btn-primary col-xs-2 col-xs-push-1" value="Save new order"/>
	</form>
	<h2>Current TLD Pricing Order</h2>
	<table class="table table-hover table-condensed">
		<tr>
			<th>TLD</th>
			<th>Order <span class="enom-pro-icon enom-pro-icon-sort"></span></th>
		</tr>
		<?php while ($row = mysql_fetch_assoc($result)) : ?>
			<tr>
				<td><?php echo $row['extension'] ?></td>
				<td><?php echo $row['order'] ?></td>
			</tr>
		<?php endwhile; ?>
	</table>

<?php else: ?>
	<h1>No pricing data found in WHMCS</h1>
<?php endif;?>
