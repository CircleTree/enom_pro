<?php /** @var $this enom_pro */ ?>
<?php $defaultCurrencyPrefix = $this->getDefaultCurrencyPrefix(); ?>
<?php if ( $this->isNonUSDinWHMCS() ) : ?>
	<div class="well alert">
		<button type="button"
		        class="close"
		        data-dismiss="alert"
		        aria-hidden="true">&uarr;</button>
		<h3>Beta — Currency Conversion
			<span class="enom-pro-icon enom-pro-icon-currency"></span>
		</h3>
		<?php $defaultCurrencyCode = $this->getDefaultCurrencyCode(); ?>
		<?php if ( $this->isCustomExchangeRate() ) : ?>
			<?php $exchangeRate = $this->getCustomExchangeRate(); ?>
		<?php else: ?>
			<?php $exchangeRate = $this->get_exchange_rate_from_USD_to( $defaultCurrencyCode ); ?>
		<?php endif; ?>
		<div class="row">
			<div class="col-lg-6">
				<div class="alert alert-info">
					We have detected that your WHMCS configuration is not using USD as a base currency.
				</div>
				<?php if ( null !== $exchangeRate ) : ?>
					<div class="alert alert-warning">
						<p>
							Exchange rate
							<span class="badge"><?php echo $exchangeRate; ?></span>
							used to convert eNom's
							<span class="badge">USD</span>
							pricing into your
							<span class="label label-info">WHMCS</span>
							Default currency:
							<span class="badge"><?php echo $defaultCurrencyCode ?></span>
						</p>
					</div>
				<?php else: ?>
					<div class="alert alert-danger">
						<p>No exchange rate found. Please enter one manually, or enter an API key for updating currencies.</p>
					</div>
				<?php endif; ?>
			</div>
			<div class="col-sm-6">
				<div class="well well-sm">
					<div
						class="alert <?php if ( $this->isCustomExchangeRate() ): ?>alert-danger<?php else: ?>alert-info<?php endif; ?>">
						<?php if ( $this->isCustomExchangeRate() ): ?>
							Using Custom Exchange Rate. <br />
						<?php else: ?>
							Enter Custom Exchange Rate. <br />
						<?php endif; ?>
						<form method="post" class="form-inline">
							<input type="hidden" name="action" value="save_custom_exchange_rate" />
							<label>
								Use Exchange Rate:
								<input type="text" name="custom-exchange-rate"
								       value="<?php echo enom_pro::get_addon_setting( 'custom-exchange-rate' ); ?>" />
							</label>
							<input type="submit" value="Update" class="btn btn-primary" />
						</form>
						<?php if ( $this->isCustomExchangeRate() ) : ?>
							<form method="post" class="form-inline">
								<input type="hidden" name="action" value="save_custom_exchange_rate" />
								<input type="hidden" name="custom-exchange-rate" value="-1" />
								<input type="submit" value="Clear" class="btn btn-danger" />
							</form>
						<?php endif; ?>
					</div>
					<h3>
						<span class="badge">USD</span>
						&rarr;
						<span class="badge"><?php echo $defaultCurrencyCode ?></span>
						=
						<span class="badge">1</span> &rarr;
						<span class="badge"><?php echo $exchangeRate !== null ? $exchangeRate : '???'; ?></span>
					</h3>
					<p>Exchange rate updated
						<span class="badge"><?php echo $this->get_exchange_rate_cache_date() ?></span>
						<?php if ( $this->isUsingExchangeRateAPIKey() ) : ?>
							<span class="badge">Using API key</span><?php endif; ?>
					   from
						<span class="label label-primary"><?php echo $this->getExchangeRateProvider(); ?></span>
					</p>
					<div class="btn-group">
						<a class="btn btn-default btn-link"
						   href="<?php echo enom_pro::MODULE_LINK . '&action=clear_exchange_cache' ?>">
							<span class="enom-pro-icon enom-pro-icon-refresh-alt"></span>
							Update Exchange Rate
						</a>
						<a target="_blank" href="configcurrencies.php" class="ep_tt ep_lightbox btn btn-default"
						   data-title="WHMCS Currencies" data-width="90%" title=""
						   data-original-title="Configure currencies in WHMCS">Edit WHMCS Currency
						</a>
					</div>
				</div>
			</div>
		</div>
	</div>
	<?php if ( ! enom_pro_controller::isDismissed( 'multiple_currencies' ) ) : ?>
		<div class="alert alert-danger">
			<button type="button"
			        class="close"
			        data-dismiss="alert"
			        data-alert="multiple_currencies"
			        aria-hidden="true">&times;</button>
			<h4>More than 1 currency in WHMCS?</h4>

			<p>Once you have imported the pricing from eNom into WHMCS, you can use
				<b>WHMCS built-in product pricing currency update</b> function to convert the rest of your currencies.
			</p>
			<ol class="numbered">
				<li>Click "Edit WHMCS Currency" Above</li>
				<li>Click "Update Product Prices" on the WHMCS Currency Configuration Page</li>
				<li>Additionally, you can have WHMCS automatically update product pricing on the CRON run. See the WHMCS docs for more information.
					<a href="http://docs.whmcs.com/Automation_Settings#Currency_Auto_Update_Settings"
					   target="_blank">WHMCS Currency Auto Update
					</a>
				</li>
			</ol>
		</div>
	<?php endif; //End currency help message?>
<?php endif;//End default currency check ?>