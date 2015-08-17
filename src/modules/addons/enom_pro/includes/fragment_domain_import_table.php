<?php
require_once ENOM_PRO_INCLUDES . 'pager.php';

$doingSearch = isset( $_REQUEST['s'] ) && ! empty( $_REQUEST['s'] );
$enom        = new enom_pro();
$show_only   = ( isset( $_GET['show_only'] ) && ! empty( $_GET['show_only'] ) ) ? $_GET['show_only'] : false;
$per_page    = $enom->get_addon_setting( 'import_per_page' );
if ( $doingSearch ) {
	//Search all
	$per_page = true;
}
$start = (int) $_GET['start'];
if ( 0 === $start ) {
	$start = 1;
}
$domains_array = $enom->getDomainsWithClients( $per_page, $start, $show_only );

$list_meta = $enom->getListMeta();
if ( empty( $domains_array ) ) { ?>
	<div class="alert alert-danger">
		<p>No domains returned from eNom.</p>
	</div>
	<?php
	return;
} ?>
<?php
if ( $doingSearch ):?>
	<?php
	$results = array();
	$s       = trim( strtolower( $_REQUEST['s'] ) );
	foreach ( $domains_array as $domain ) {
		if ( strstr( $domain['sld'], $s ) ) {
			$results[] = $domain;
		}
		if ( strstr( $domain['tld'], $s ) ) {
			$results[] = $domain;
		}
		//Try removing the . for tld
		if ( strstr( $domain['tld'], ltrim( $s, '.' ) ) ) {
			$results[] = $domain;
		}
		if ( strstr( $domain['sld'] . $domain['tld'], str_replace( array( '.' ), '', $s ) ) ) {
			$results[] = $domain;
		}
		//Get unique values / deduplicate
		$results = array_map( "unserialize", array_unique( array_map( "serialize", $results ) ) );
	}
	if ( ! empty( $results ) ) {
		$domains_array = $results; ?>
		<h3>Search for
			<span class="label label-default"><?php echo htmlentities( $_REQUEST['s'] ); ?></span>
		    Found
			<span class="label label-success"><?php echo count( $domains_array ) ?></span>
		    Results
		</h3>
		<?php
	} else { ?>
		<div class="alert alert-danger">No Search Results for <?php echo htmlentities( $_REQUEST['s'] ); ?></div>
	<?php } ?>
	<a class="btn btn-block btn-inverse clear_search" href="#">Clear Search</a>
<?php endif; ?>

	<table class="table table-hover" id="import_table">
		<tr>
			<th>Domain</th>
			<th>Status</th>
		</tr>
		<?php foreach ( $domains_array as $domain ):
			$domain_name = $domain['sld'] . '.' . $domain ['tld'];
			?>
			<tr>
				<td>
					<a href="http://<?php echo $domain_name; ?>" target="_blank"><?php echo $domain_name; ?></a>
				</td>
				<td>
					<?php if ( ! isset( $domain['client'] ) ) : ?>
						<div class="alert alert-danger">
							<p>
								Domain not found in WHMCS
								<a class="btn btn-primary create_order"
								   data-domain="<?php echo $domain_name; ?>"
								   data-id-protect="<?php echo $domain['privacy'] ?>"
								   data-dns="<?php echo $domain['enom_dns'] ?>"
								   data-autorenew="<?php echo $domain['autorenew'] ?>"
									<?php $due_relative = enom_pro::get_addon_setting( 'next_due_date' ); ?>
									<?php $pretty_date_format = 'Y/m/d'; ?>
									<?php $whmcs_date_format = 'Ymd'; ?>
									<?php $expiration = $domain['expiration']; ?>
									<?php if ( 'Expiration Date' == $due_relative ) : ?>
										<?php $nextduedate = date( $whmcs_date_format, strtotime( $expiration ) ); ?>
										<?php $nextduedate_pretty = date( $pretty_date_format,
											strtotime( $expiration ) ); ?>
									<?php else: ?>
										<?php
										switch ( $due_relative ) {
											case '-1 Day':
												$nextduedate        = date( $whmcs_date_format,
													strtotime( $expiration . ' -1 days' ) );
												$nextduedate_pretty = date( $pretty_date_format,
													strtotime( $expiration . ' -1 days' ) );
												break;
											case '-3 Days':
												$nextduedate        = date( $whmcs_date_format,
													strtotime( $expiration . ' -3 days' ) );
												$nextduedate_pretty = date( $pretty_date_format,
													strtotime( $expiration . ' -3 days' ) );
												break;
											case '-5 Days':
												$nextduedate        = date( $whmcs_date_format,
													strtotime( $expiration . ' -5 days' ) );
												$nextduedate_pretty = date( $pretty_date_format,
													strtotime( $expiration . ' -5 days' ) );
												break;
											case '-7 Days':
												$nextduedate        = date( $whmcs_date_format,
													strtotime( $expiration . ' -7 days' ) );
												$nextduedate_pretty = date( $pretty_date_format,
													strtotime( $expiration . ' -7 days' ) );
												break;
											case '-14 Days':
												$nextduedate        = date( $whmcs_date_format,
													strtotime( $expiration . ' -14 days' ) );
												$nextduedate_pretty = date( $pretty_date_format,
													strtotime( $expiration . ' -14 days' ) );
												break;
										}
										?>
									<?php endif; ?>
                                   data-expiresdate="<?php echo date( $whmcs_date_format,
	                                   strtotime( $domain['expiration'] ) ); ?>"
                                   data-expiresdatelabel="<?php echo date( $pretty_date_format,
	                                   strtotime( $domain['expiration'] ) ); ?>"
                                   data-nextduedate="<?php echo $nextduedate ?>"
                                   data-nextduedatelabel="<?php echo $nextduedate_pretty ?>"
                                   href="#">
									Create Order
									<span class="enom-pro-icon enom-pro-icon-cart-plus"></span>
								</a>
							</p>
							<div class="domain_whois clearfix" data-action="get_domain_whois"
							     data-domain="<?php echo $domain_name; ?>">
								<div class="response"></div>
								<div class="enom_pro_loader small whois">Querying WHOIS</div>
							</div>
						</div>
					<?php else: ?>
						<div class="alert alert-success">
							<p>
								Associated with client:
								<a class="btn btn-default"
								   data-domain="<?php echo $domain_name; ?>"
								   target="_blank"
								   href="clientsdomains.php?userid=<?php echo $domain['client']['userid']; ?>&domainid=<?php echo $domain['whmcs_id']; ?>">
									<?php echo $domain['client']['firstname'] . ' ' . $domain['client']['lastname']; ?>
									<span class="enom-pro-icon-checkmark"></span>
								</a>
							</p>
						</div>
					<?php endif; ?>
				</td>
			</tr>
		<?php endforeach; ?>
	</table>
<?php if ( ! $doingSearch ) : ?>
	<?php
	if ( 1 == $_GET['start'] ) {
		//Find a better way of encapsulating pagination
		$_GET['start'] = 0;
	}
	pager( $list_meta['total_domains'], 'domain_import', true, $per_page, false );
	?>
<?php endif; ?>