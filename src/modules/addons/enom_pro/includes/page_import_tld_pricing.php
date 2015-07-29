<?php
require_once ENOM_PRO_INCLUDES . 'pager.php';
$pricing_years_array = array_keys( array_fill( 1, enom_pro::get_addon_setting( 'pricing_years' ), '' ) );
/**
 * @var $this enom_pro
 */
/*
 ***********************
 * TLD Cache Check
 ***********************
 */
if ( $this->is_pricing_cached() ) : ?>
	<div id="enom_pro_pricing_import_page">
		<?php $allDomainsPricing = $this->getAllDomainsPricing(); ?>
		<?php $per_page = enom_pro::get_addon_setting( 'pricing_per_page' ); ?>
		<?php if ( isset( $_GET['cleared'] ) || isset( $_GET['new'] ) || isset( $_GET['updated'] ) || isset( $_GET['deleted'] ) || isset( $_GET['exchange'] ) || isset( $_GET['nochange'] ) ) :
			?>
			<div class="slideup fixed" data-timeout="3">
				<?php if ( isset( $_GET['cleared'] ) ) : ?>
					<button type="button"
					        class="close"
					        data-dismiss="alert"
					        aria-hidden="true">&times;</button>
					<div class="alert alert-info">Cache Cleared</div>
				<?php endif; ?>
				<?php if ( isset( $_GET['new'] ) ): ?>
					<div class="alert alert-success">
						<button type="button"
						        class="close"
						        data-dismiss="alert"
						        aria-hidden="true">&times;</button>
						<p>Created <?php echo (int) $_GET['new'] ?> new TLD pricing in WHMCS</p>
					</div>
				<?php endif; ?>
				<?php if ( isset( $_GET['updated'] ) ): ?>
					<div class="alert alert-success">
						<button type="button"
						        class="close"
						        data-dismiss="alert"
						        aria-hidden="true">&times;</button>
						<p>Updated <?php echo (int) $_GET['updated'] ?> TLD pricing in WHMCS</p>
					</div>
				<?php endif; ?>
				<?php if ( isset( $_GET['deleted'] ) ): ?>
					<div class="alert alert-danger">
						<button type="button"
						        class="close"
						        data-dismiss="alert"
						        aria-hidden="true">&times;</button>
						<p>Deleted <?php echo (int) $_GET['deleted'] ?> TLD pricing from WHMCS</p>
					</div>
				<?php endif; ?>
				<?php if ( isset( $_GET['nochange'] ) ): ?>
					<div class="alert alert-danger">
						<button type="button"
						        class="close"
						        data-dismiss="alert"
						        aria-hidden="true">&times;</button>
						<p>No Pricing was Selected for Update</p>
					</div>
				<?php endif; ?>
				<?php if ( isset( $_GET['exchange'] ) ): ?>
					<div class="alert alert-info">
						<button type="button"
						        class="close"
						        data-dismiss="alert"
						        aria-hidden="true">&times;</button>
						<p>Exchange Rates Updated</p>
					</div>
				<?php endif; ?>
			</div>
		<?php endif; //End Message wrap?>

		<?php
		$input_limit = ini_get( 'max_input_vars' );
		$max_input   = $per_page * enom_pro::get_addon_setting( 'pricing_years' );
		if ( $max_input >= $input_limit ) : ?>
			<div class="alert alert-danger">
				<h3>
					<span class="enom-pro-icon enom-pro-icon-error"></span> Error: Too many fields on this page. Please change the number per page, or modify your php.ini setting for
					<a href="http://php.net/manual/en/info.configuration.php#ini.max-input-vars" target="_blank">max_input_vars</a> to be greater than:
					<span class="badge"><?php echo number_format( $max_input ) ?></span></h3>

				<p>Currently, saving all of the fields on this page would result in
					<span class="label label-danger"><?php echo number_format( $max_input ) ?></span> variables, which is
					greater than PHP's maximum of
					<span class="label label-success"><?php echo number_format( $input_limit ) ?></span>.
				</p>
			</div>
		<?php endif; ?>
		<?php if ( ! enom_pro_controller::isDismissed( 'price_intro' ) ) : ?>
			<div class="alert alert-info">
				<button type="button"
				        class="close"
				        data-dismiss="alert"
				        data-alert="price_intro"
				        aria-hidden="true">&times;</button>
				<p>
					Import pricing for all 3 domain order types:
				<ul class="list-inline">
					<li>
						<span class="badge">register</span>
					</li>
					<li>
						<span class="badge">transfer</span>
					</li>
					<li>
						<span class="badge">renew</span>
					</li>
				</ul>
				Once they are imported, you can
				bulk edit all 3 pricing tiers, or you can fine tune pricing in directly in whmcs by
				clicking the TLD drop-down menu button.
				</p>
				<img src="../modules/addons/enom_pro/images/pricing-drop-down-help.jpg" height="209" width="238" alt="" />
			</div>
		<?php endif; ?>

		<?php if ( ! enom_pro_controller::isDismissed( 'order-types' ) ) : ?>
			<div class="alert alert-warning fade in">
				<button type="button"
				        class="close"
				        data-dismiss="alert"
				        data-alert="order-types"
				        aria-hidden="true">&times;</button>
					<span><b>IMPORTANT:</b> Clicking Save will overwrite any specific order type pricing that have been customized
									(IE: Different prices for register vs. transfer).<br />
									<em>If in doubt, please <a href="#"
									                           class="clear_all btn  btn-default btn-xs">Clear All Pricing</a> before saving.</em>
							</span>
			</div>
		<?php endif; ?>



		<?php require_once ENOM_PRO_INCLUDES . 'page_import_tld_pricing_currency_conversion.php'; ?>

		<?php require_once ENOM_PRO_INCLUDES . 'page_import_tld_pricing_bulk_process.php'; ?>

		<?php
		$offset = isset( $_GET['start'] ) ? (int) $_GET['start'] : 0;
		$search = isset( $_GET['s'] ) ? strip_tags( $_GET['s'] ) : false;

		$allDomainsSearched = array();
		if ( $search ) {
			foreach ( $allDomainsPricing as $tld => $domainPriceData ) {
				if ( strstr( $tld, $search ) ) {
					$allDomainsSearched[ $tld ] = $domainPriceData;
				} elseif ( strstr( $domainPriceData['price'], $search ) ) {
					$allDomainsSearched[ $tld ] = $domainPriceData;
				}
			}
			unset( $tld, $domainPriceData ); //Just for good measure
			if ( ! empty( $allDomainsSearched ) ) {
				//Search successful, overwrite our default array
				$allDomainsPricing = $allDomainsSearched;
			}
		}
		$domains = array_slice( $allDomainsPricing, $offset, $per_page ); ?>
		<div id="bulkRightWrap">

			<form method="GET" id="tldSearchForm">
				<h4 class="hidden-turbo">Search</h4>
				<input type="hidden" name="module" value="enom_pro" />
				<input type="hidden" name="view" value="pricing_import" />

				<div class="input-group <?php echo isset( $_GET['s'] ) ? ' has-success' : ''; ?>">
					<p class="input-group-addon">.</p>
					<input type="search" name="s" value="<?php echo isset( $_GET['s'] ) ? htmlentities( strip_tags( $_GET['s'] ) ) : ''; ?>" class="form-control" placeholder="tld" />
			<span class="input-group-btn">
				<button type="submit" class="btn btn-default"><span class="enom-pro-icon enom-pro-icon-search"></button>
				<?php if ( isset( $_GET['s'] ) ) : ?>
					<button type="button" class="btn btn-danger clearTLDSearch">Clear</button>
				<?php endif; ?>
			</span>
				</div>
				<?php if ( isset( $_GET['s'] ) ) : ?>
					<?php if ( empty( $allDomainsSearched ) ): ?>
					<div class="alert alert-warning"><h4>No search results. Displaying all TLDs.</h4></div>
				<?php else : ?>
					<div class="alert alert-success">Found <?php echo count( $allDomainsSearched ) ?> search results</div>
				<?php endif; ?>
					<script>
						jQuery(function ($) {
							var offset = $("#tldSearchForm").offset().top;
							$('html, body').animate({scrollTop: offset}, 350);
						})
					</script>
				<?php endif; ?>
			</form>
			<form method="POST"
			      action="<?php echo $_SERVER['PHP_SELF']; ?>?module=enom_pro&view=pricing_import"
			      id="enom_pro_pricing_import">
				<input type="hidden" name="action" value="save_domain_pricing" />
				<input type="hidden"
				       name="start"
				       value="<?php echo isset( $_GET['start'] ) ? (int) $_GET['start'] : '0'; ?>" />
				<table class="table table-bordered table-responsive"
				       id="enom_pro_pricing_table">
					<thead>
					<tr>
						<th class="tldActionsTD">
							<button class="btn btn-default btn-xs ep_tt toggleAllTLDCheckboxes" title="Toggle All">
								<span class="enom-pro-icon enom-pro-icon-checkmark"></span></button>
							Actions
						</th>
						<?php foreach ( $pricing_years_array as $key => $year ) : ?>
							<th colspan="1">
								<?php echo $year; ?> Year<?php if ( $year > 1 ): ?>s<?php endif; ?>
							</th>
						<?php endforeach; ?>
					</tr>
					</thead>
					<tbody>
					<?php
					foreach ( $domains as $tld => $domainPriceData ):
						$isInWHMCS             = false;
						$whmcs_pricing_for_tld = $this->get_whmcs_domain_pricing( $tld );
						if ( count( $whmcs_pricing_for_tld ) > 0 ) :
							$whmcs_id  = $whmcs_pricing_for_tld['id'];
							$isInWHMCS = true;
						endif; ?>
						<tr>
							<td class="tldActionsTD">
								<?php
								$btn_classes      = $action_classes = array();
								$btn_classes[] = 'btn-xs';
								$action_classes[] = 'btn-group';
								$action_classes[] = 'tldActions';
								$thisTLDError     = false;
								if ( isset( $domainPriceData['error'] ) ) {
									$thisTLDError     = $domainPriceData['error'];
									$btn_classes[]    = 'btn-warning';
									$btn_classes[]    = 'disabled';
									$action_classes[] = 'ep_pop';

								} else {
									$btn_classes[] = $isInWHMCS ? 'btn-success' : 'btn-default';

								}
								?>
								<input type="checkbox" class="tldCheck" name="<?php echo $tld ?>"<?php echo $this->is_tld_saved( $tld ) ? ' checked' : ''; ?> />

								<div class="<?php echo implode( ' ',
									$action_classes ); ?>" <?php if ( $thisTLDError ) : ?> title="Error from eNom API" data-content="<?php echo $thisTLDError ?>" <?php endif; ?>>
									<div class="btn tldAction <?php echo implode( " ",
										$btn_classes ) ?>" data-tld="<?php echo $tld ?>"<?php if ( $isInWHMCS ) : ?> data-whmcs="true"<?php endif; ?> data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
										.<?php echo $tld; ?>
									</div>
									<button type="button" class="btn btn-xs btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
										<span class="caret"></span>
										<span class="sr-only">Toggle Dropdown</span>
									</button>
									<ul class="dropdown-menu">
										<?php if ( $isInWHMCS ) : ?>
											<li>
												<a target="_blank" class="ep_lightbox"
												   data-pricing="true"
												   data-target="configdomains.php?action=editpricing&id=<?php echo $whmcs_id; ?>"
												   data-title="Pricing for .<?php echo $tld; ?>"
												   href="configdomains.php?action=editpricing&id=<?php echo $whmcs_id; ?>">Edit WHMCS Pricing</a>
											</li>
											<li>
												<a href="#"
												   data-tld="<?php echo $tld ?>"
												   class="delete_tld">
													Delete Pricing from WHMCS
												</a>
											</li>
											<li class="divider"></li>
										<?php endif; //TODO remove this?
										?>
										<li>
											<a href="#"
											   data-tld="<?php echo $tld ?>"
											   class="toggle_tld">
												Import eNom Pricing
											</a>
										</li>
										<li>
											<a href="#"
											   data-tld="<?php echo $tld ?>"
											   class="mult_row">
												Multiply 1-year price for Row
											</a>
										</li>
									</ul>
								</div>
							</td>
							<?php foreach (
								$pricing_years_array as $key => $year
							) : ?>
								<?php
								$rawEnomPrice        = number_format( ( $domainPriceData['price'] * $year ),
									2,
									'.',
									'' );
								$formattedEnomPrice  = number_format( ( $domainPriceData['price'] * $year ),
									2 );
								$whmcs_price         = isset( $whmcs_pricing_for_tld[ $year ] ) ? number_format( $whmcs_pricing_for_tld[ $year ],
									2 ) : false;
								$notEnabledForPeriod = $domainPriceData['min_period'] > $year ? true : false;
								$class               = '';
								if ( $whmcs_price && $whmcs_price > 0 ) {
									$class = 'has-success';
								} elseif ( $whmcs_price && 0 == $whmcs_price ) {
									$class = 'has-warning';
								}
								if ( $notEnabledForPeriod ) {
									$class = 'has-warning';
								}
								?>
								<td>
									<div class="input-group input-group-sm <?php echo $class ?>">
																	<span class="price ep_tt input-group-addon input-sm"
																	      title="eNom Price">
																		<?php echo $this->getDefaultCurrencyPrefix() . $formattedEnomPrice; ?>
																	</span>
										<input
											data-tld="<?php echo $tld ?>"
											data-year="<?php echo $year; ?>"
											data-price="<?php echo $rawEnomPrice; ?>"
											<?php if ( $whmcs_price ) : ?>
												data-whmcs="<?php echo $whmcs_price; ?>"
											<?php endif; ?>
											class="myPrice form-control input-sm <?php echo $notEnabledForPeriod ? 'ep_tt' : '' ?>"
											size="6"
											type="text"
											name="pricing[<?php echo $tld ?>][<?php echo $year ?>]"
											<?php if ( $notEnabledForPeriod ) : ?>
												title="Min. Registration Period is <?php echo $domainPriceData['min_period'] ?>"
											<?php endif; ?>
											value=""
											/>
										<?php if ( $whmcs_price ) : ?>
											<span class="price ep_tt input-group-addon input-sm"
											      title="WHMCS Price">
																			<?php echo $this->getDefaultCurrencyPrefix() . $whmcs_price; ?></span>
										<?php endif; ?>
									</div>
								</td>
							<?php endforeach; ?>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
				<input type="submit" value="Save" class="btn btn-block btn-success">
			</form>
			<?php pager( count( $allDomainsPricing ),
				'pricing_import',
				false,
				$per_page,
				'#enom_pro_pricing_table' ); ?>
		</div>
		<script>
			jQuery(function ($) {
				enom_pro.initPricingImport();
				$(".pager a").on('click', function () {
					var unsaved = false, $link = $(this);
					$('.myPrice').each(function (k, v) {
						var $input = $(v);
						if ($input.val() != '') {
							//Value is not blank
							if ($input.val() != $input.data('whmcs')) {
								//Value does not equal saved WHMCS value
								unsaved = true;
							}
							if (!$input.data('whmcs')) {
								//There is no WHMCS pricing for this node
								unsaved = true;
							}
						}
					});
					if (unsaved) {
						var ans = confirm('You have unsaved changes. \n\n Save them before going to the next page?');
						if (ans) {
							$("input[name=start]").val($link.data('start'));
							$("#enom_pro_pricing_import").trigger('submit');
						}
						return false; //Submit event takes care of next page load
					}
				});
			});
		</script>
	</div>
	<?php //End <div id="enom_pro_pricing_import_page">?>

	<?php
	/**
	 **************************
	 * TLD pricing un-cached
	 **************************
	 */
	?>

<?php else: ?>
	<div class="alert alert-warning" id="loading_pricing">
		<h3 class="loadingPricingTitle">Loading <?php echo enom_pro::is_retail_pricing() ? 'retail' : 'wholesale'; ?> pricing for <?php echo count( $this->getTLDs() ) ?> top level domains.
			<br /><span class="enom-pro-icon enom-pro-icon-spinner fa-spin"></span></h3>

		<p class="text-center loadedTLD"></p>

		<div class="enom_pro_loader"></div>
		<div class="progress">
			<div class="progress-bar progress-bar-success progress-bar-striped active" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;">
			</div>
		</div>
		<?php if ( $this->isModuleDebugEnabled() ) : ?>
			<div class="alert alert-danger">
				<h4>Module Logging is Enabled.</h4>

				<p class="text-center">For best performance, please only enable module logging when instructed to by support.
					<a href="systemmodulelog.php" class="btn btn-default" target="_blank">Visit this page to disable logging</a>
				</p>
			</div>
		<?php endif; ?>
		<button class="btn btn-danger btn-block stopPriceBatch">Cancel</button>
	</div>
	<!--suppress MagicNumberJS -->
	<script>
		var bulkPricingAJAX, aborted = false;
		jQuery(function ($) {
			var $cancelButton = $(".stopPriceBatch"), $title = $(".loadingPricingTitle"), $loader = $(".enom_pro_loader");

			function doPriceBatch() {
				if (aborted) {
					return false;
				}
				bulkPricingAJAX = $.ajax({
					url:     '<?php echo enom_pro::MODULE_LINK; ?>&action=get_pricing_data',
					success: function (data) {
						if (data == 'success') {
							$title.html('Import Complete. Reloading...');
							$title.closest('.alert').removeClass('alert-warning').addClass('alert-success');
							$(".progress, .stopPriceBatch, .loadedTLD, .alert-danger").hide();
							setTimeout(function () {
								window.location.reload();
							}, 1000);
						} else {
							doPriceBatch();
							/** @namespace data.total */
							var percent = Math.round((data.loaded / data.total) * 100);
							$loader.hide();
							$(".progress-bar").css('width', percent + '%').html(percent +
							                                                    "% Complete").attr('aria-valuenow',
							                                                                       percent);
							$(".loadedTLD").html('Loaded pricing for: ' + data.tld);
						}
					},
					error:   function (xhr) {
						$title.removeClass('alert-warning');
						if (xhr.statusText == 'abort') {
							$title.html('Process Cancelled').addClass('alert-success');
						} else {
							$title.html('Error: ' + xhr.responseText).addClass('alert-danger');
						}
						$('<span class="btn btn-default">Retry?</span>').on('click', function () {
							window.location.reload();
							return false;
						}).appendTo($title);
						$loader.hide();
					}
				});
			}

			doPriceBatch();
			$cancelButton.on('click', function () {
				var ans = confirm('This will abort fetching the current pricing from eNom. \n\n' +
				                  'You can restart the process later at the same point by simply coming back to this page.',
				                  "Are you sure?")
				if (ans) {
					aborted = true;
					bulkPricingAJAX.abort();
				}
			});
		})
	</script>
<?php endif; ?>