<div class="well row">
	<button type="button"
	        class="close"
	        data-dismiss="alert"
	        aria-hidden="true">&uarr;</button>

	<div class="col-lg-6">

		<h3>Bulk Import</h3>

		<p>Import all TLDs on this page</p>

		<form class="bulkImport form-inline"
		      role="form"
		      action="<?php echo enom_pro::MODULE_LINK ?>">
			<h4>Pricing Markup Settings</h4>
			<div class="form-group col-xs-12">
				<h5 class="ep_pop" title="Minimum Markup" data-placement="auto top" data-content="Enter the minimum acceptable markup. Use this to cover credit card processing fees, for example.">Minimum Markup</h5>
				<div class="input-group">
					<label for="percentMarkup" class="input-group-addon">Markup</label>
					<input type="number"
					       min="0"
					       step="0.01"
					       max="500"
					       name="markup"
					       id="percentMarkup"
					       class="form-control input-sm"/>
					<span class="input-group-addon">%</span>
				</div>
				<div class="input-group">
					<label>+</label>
				</div>
				<div class="input-group">
					<label for="wholeMarkup" class="input-group-addon">$</label>
					<input type="number"
					       min="0.00"
					       max="500"
					       step="0.05"
					       name="markup2"
					       id="wholeMarkup"
					       placeholder="0.00"
					       class="form-control input-sm"/>
				</div>
			</div>
			<div class="form-group col-xs-12">
				<h5 class="ep_pop" title="Preferred Markup" data-placement="auto top" data-content="The Markup you'd like to make, while still being protected from under-selling.">Preferred Markup</h5>
				<div class="input-group">
					<label for="preferredPercentMarkup" class="input-group-addon">Markup</label>
					<input type="number"
					       min="0"
					       max="500"
					       step="0.01"
					       name="markup"
					       id="preferredPercentMarkup"
					       class="form-control input-sm"/>
					<span class="input-group-addon">%</span>
				</div>
				<div class="input-group">
					<label>+</label>
				</div>
				<div class="input-group">
					<label for="preferredWholeMarkup" class="input-group-addon">$</label>
					<input type="number"
					       min="0.00"
					       max="500"
					       step="0.05"
					       name="markup2"
					       id="preferredWholeMarkup"
					       placeholder="0.00"
					       class="form-control input-sm"/>
				</div>
			</div>


			<div class="form-group col-xs-12">
				<h5>Price Options</h5>
				<div class="input-group">
					<label for="roundTo" class="input-group-addon">Round up to $</label>
					<select name="round" id="roundTo" class="form-control input-sm-2">
						<option value="-1">Disabled</option>
						<option value="99">.99</option>
						<option value="98">.98</option>
						<option value="95">.95</option>
						<option value="50">.50</option>
						<option value="01">.01</option>
						<option value="00">.00</option>
					</select>
				</div>
				<div class="input-group checkbox">
					<label for="overWriteWHMCS" class="input-group-addon">
						<input type="checkbox" name="overwrite" id="overWriteWHMCS"/>
						Overwrite Values Already in WHMCS
					</label>
				</div>
			</div>

			<div class="btn-group pull-right">
				<button type="submit" class="btn btn-primary ep_pop" data-content="Press ENTER in the form above for rapid previewing" title="Helpful Hint" data-placement="auto top" data-container="body">Preview</button>
				<button type="button" class="btn btn-success savePricing">Save</button>

				<div class="btn-group">
					<button type="reset" class="btn btn-danger clear_all">Clear</button>
					<button type="button"
					        class="btn btn-danger dropdown-toggle"
					        data-toggle="dropdown">
						<span class="caret"></span>
						&nbsp;
						<span class="sr-only">Toggle Dropdown</span>
					</button>
					<ul class="dropdown-menu clearDropdown" role="menu">
						<li><a href="#" class="deleteFromWHMCS">Delete all from WHMCS</a>
						</li>
					</ul>
				</div>
			</div>
		</form>
	</div>
	<div class="col-lg-6">
		<h3>Domain Pricing Meta</h3>
		Pricing for a total of <?php echo count( $this->getAllDomainsPricing() ) ?> TLDs.
		<br/>
		Pricing data last updated <?php echo $this->get_price_cache_date(); ?>
		<a class="btn btn-default btn-info"
		   href="<?php echo enom_pro::MODULE_LINK; ?>&action=clear_price_cache">Clear Cache</a>
	</div>
</div>