<div id="bulkLeftWrap">
	<div class="well">
		<div class="row">
			<div class="col-lg-6" id="bulkPricingImportWrap">
				<div class="enom-pro-icon enom-pro-icon-cancel-circle close-bulk-editor ep_tt" title="Close Bulk Pricing editor" data-placement="bottom"></div>

				<h3 class="hidden-turbo">
			<span class="btn btn-primary btn-lg open-bulk-editor ep_pop" title="Turbo TLD Importer" data-content="Get rid of all of the distractions, and focus on selling an ever-growing list of the new TLDs.">
				<span class="enom-pro-icon enom-pro-icon-tag"></span>
				 Bulk Import <span class="label label-info">New!</span>
			</span>
				</h3>

				<form class="bulkImport form-inline"
				      role="form"
				      action="<?php echo enom_pro::MODULE_LINK ?>">
					<h4>Pricing Markup Settings</h4>

					<div class="form-group col-xs-12">
						<h5 class="ep_pop" title="Minimum Markup" data-placement="auto top" data-content="Enter the minimum acceptable markup. Use this to cover credit card processing fees, for example.">Minimum Markup</h5>

						<div class="input-group col-xs-5">
							<label for="percentMarkup" class="input-group-addon hidden-turbo">Markup</label>
							<input type="number"
							       min="0"
							       step="0.01"
							       max="500"
							       name="markup"
							       id="percentMarkup"
							       value="<?php echo enom_pro::get_addon_setting( 'min_markup_percent' ) ?>"
							       class="form-control input-sm" />
							<span class="input-group-addon">%</span>
						</div>
						<div class="input-group text-center col-xs-1">
							<label>+</label>
						</div>
						<div class="input-group col-xs-4">
							<label for="wholeMarkup" class="input-group-addon">$</label>
							<input type="number"
							       min="0.00"
							       max="500"
							       step="0.05"
							       name="markup2"
							       id="wholeMarkup"
							       placeholder="0.00"
							       value="<?php echo enom_pro::get_addon_setting( 'min_markup_whole' ) ?>"
							       class="form-control input-sm" />
						</div>
					</div>
					<div class="form-group col-xs-12">
						<h5 class="ep_pop" title="Preferred Markup" data-placement="auto top" data-content="The Markup you'd like to make, while still being protected from under-selling.">Preferred Markup</h5>

						<div class="input-group  col-xs-5">
							<label for="preferredPercentMarkup" class="input-group-addon hidden-turbo">Markup</label>
							<input type="number"
							       min="0"
							       max="500"
							       step="0.01"
							       name="markup"
							       id="preferredPercentMarkup"
							       value="<?php echo enom_pro::get_addon_setting( 'preferred_markup_percent' ) ?>"
							       class="form-control input-sm" />
							<span class="input-group-addon">%</span>
						</div>
						<div class="input-group text-center col-xs-1">
							<label>+</label>
						</div>
						<div class="input-group col-xs-4">
							<label for="preferredWholeMarkup" class="input-group-addon">$</label>
							<input type="number"
							       min="0.00"
							       max="500"
							       step="0.05"
							       name="markup2"
							       id="preferredWholeMarkup"
							       placeholder="0.00"
							       value="<?php echo enom_pro::get_addon_setting( 'preferred_markup_whole' ) ?>"
							       class="form-control input-sm" />
						</div>
					</div>

					<div class="form-group col-xs-12">
						<h5>Price Options</h5>

						<div class="input-group">
							<?php $current_rounding_option = enom_pro::get_addon_setting( 'round_to' ) ?>
							<label for="roundTo" class="input-group-addon">Round up to $</label>
							<?php $rounding_options = array(
								'-1' => 'Disabled',
								'99' => '.99',
								'98' => '.98',
								'95' => '.95',
								'50' => '.50',
								'01' => '.01',
								'00' => '.00'
							); ?>
							<select name="round" id="roundTo" class="form-control input-sm-2">
								<?php foreach ( $rounding_options as $value => $label ): ?>
									<?php $selected = ( $current_rounding_option == $value ? ' selected="selected" ' : '' ); ?>
									<option value="<?php echo $value ?>"<?php echo $selected ?>><?php echo $label ?></option>
								<?php endforeach; ?>
							</select>
						</div>
						<div class="input-group checkbox">
							<label for="overWriteWHMCS" class="input-group-addon">
								<input type="checkbox" name="overwrite" id="overWriteWHMCS" <?php echo enom_pro::get_addon_setting( 'overwrite_whmcs' ) == '1' ? 'checked="checked"' : '' ?>/>
								Overwrite Values Already in WHMCS
							</label>
						</div>
					</div>

					<div class="btn-group pull-right">
						<button type="submit" class="btn btn-primary ep_pop" data-content="Press ENTER in the form above for rapid previewing" title="Helpful Hint" data-placement="auto top" data-container="body">Preview
							<span class="enom-pro-icon-refresh-alt"></span></button>
						<button type="button" class="btn btn-success savePricing">Save
							<span class="enom-pro-icon-checkmark"></span></button>

						<div class="btn-group">
							<button type="reset" class="btn btn-danger clear_all ep_pop" data-content="Clears the pricing on this page" data-placement="top" data-toggle="popover" data-container="#bulkLeftWrap">Clear
								<span class="enom-pro-icon-warning"></span></button>
							<button type="button"
							        class="btn btn-danger dropdown-toggle"
							        data-toggle="dropdown">
								<span class="caret"></span>
								&nbsp;
								<span class="sr-only">Toggle Dropdown</span>
							</button>
							<ul class="dropdown-menu clearDropdown" role="menu">
								<li>
									<a href="#" class="deleteFromWHMCS">Delete all from WHMCS</a>
								</li>
							</ul>
						</div>
					</div>
				</form>
			</div>
			<div class="col-lg-6" id="bulkPricingMetaWrap">
				<h3 class="hidden-turbo">Meta</h3>
		<span class="text-muted">
			Pricing for <?php echo count( $this->getAllDomainsPricing() ) ?> TLDs. <br />
			Pricing data last updated <?php echo $this->get_price_cache_date(); ?>
			<a class="btn btn-sm btn-inverse"
			   href="<?php echo enom_pro::MODULE_LINK; ?>&action=clear_price_cache">
				Clear Cache <span class="enom-pro-icon-trash"></span>
			</a>
		</span>
			</div>
		</div>
	</div>
</div>