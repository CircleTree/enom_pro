<?php
/**
 * Project: enom_pro
 * Build: @BUILD_DATE@
 * Version: @VERSION@
 */

$filepath = ROOTDIR . '/includes/whoisservers.php';
if ( ! file_exists( $filepath ) ) {
	throw new Exception( 'WHOIS Checker - Unable to load: ' . $filepath );
}
$file_contents = file_get_contents( $filepath );
$file_array    = explode( PHP_EOL, $file_contents );
/**
 * @var array tld => server address
 */
$parsed_whois_servers = array();
foreach ( $file_array as $line ) {
	$file_line = explode( '|', $line );
	//Set response to tld => server address
	if ( ! empty( $file_line[0] ) && ! empty( $file_line[1] ) ) {
		$parsed_whois_servers[ $file_line[0] ] = $file_line[1];
	}
}
$whmcs_tlds_query = select_query( 'tbldomainpricing', 'extension' );
if ( false === mysql_num_rows( $whmcs_tlds_query ) ) {
	throw new Exception( "No TLD's found in WHMCS. Please configure TLD's before checking for WHOIS servers" );
}

$whmcs_tlds = array();
while ( $row = mysql_fetch_assoc( $whmcs_tlds_query ) ) :
	$whmcs_tlds[] = $row['extension'];
endwhile;
$parsed_whois_tlds = array_keys( $parsed_whois_servers );
$ok                = array_intersect( $whmcs_tlds, $parsed_whois_tlds );
$missing           = array_diff( $whmcs_tlds, $parsed_whois_tlds );
if ( 'ok' == enom_pro_whois_show_only() ) {
	$list = $ok;
} elseif ( 'missing' == enom_pro_whois_show_only() ) {
	$list = $missing;
} else {
	$list = array_merge( $missing, $ok );
	natsort( $list );
	if ( isset( $_GET['sort'] ) && 'desc' == $_GET['sort'] ) {
		$list = array_reverse( $list );
	}
}

/**
 * $_GET['show'] helper to sanitize and check isset
 * @return string
 */
function enom_pro_whois_show_only() {

	if ( ! isset( $_GET['show'] ) ) {
		return 'all';
	}
	if ( 'missing' == $_GET['show'] ) {
		return 'missing';
	}
	if ( 'ok' == $_GET['show'] ) {
		return 'ok';
	}
}


?>

<div class="row">
	<div class="col-xs-4">
		<a href="<?php echo enom_pro::MODULE_LINK ?>&view=whois_checker&show=ok" class="btn btn-success btn-lg btn-block ep_tt <?php echo 'ok' == enom_pro_whois_show_only() ? 'disabled' : '' ?>" title="Show TLDs with a WHOIS server">
			<?php echo count( $ok ); ?> TLDs with WHOIS
		</a>
	</div>
	<div class="col-xs-4">
		<a href="<?php echo enom_pro::MODULE_LINK ?>&view=whois_checker&show=missing" class="btn btn-danger btn-lg btn-block ep_tt <?php echo 'missing' == enom_pro_whois_show_only() ? 'disabled' : '' ?>" title="Show TLDs without WHOIS">
			<?php echo count( $missing ); ?> TLDs missing a WHOIS server
		</a>
		<a href="#" class="deleteAll btn btn-inverse hidden">Delete All Missing WHOIS Servers from WHMCS</a>
	</div>
	<div class="col-xs-4">
		<a href="<?php echo enom_pro::MODULE_LINK ?>&view=whois_checker" class="btn btn-inverse btn-lg btn-block ep_tt <?php echo 'all' == enom_pro_whois_show_only() ? 'disabled' : '' ?>" title="View all TLDs">
			<?php echo count( $list ); ?> Total
		</a>
	</div>
</div>

<div class="deleteCount alert alert-info hidden">
	<span><b></b> TLD's in Delete Queue</span>
</div>

<?php if ( empty ( $list ) ) : ?>
	<div class="col-xs-6 col-xs-push-3 alert alert-danger text-center">
		<h3><span class="enom-pro-icon-error enom-pro-icon"> No WHOIS servers found for this view.</h3>
	</div>
	<?php
	return; //Bail early for no servers
endif; ?>

<table class="table table-responsive">
	<thead>
	<tr>
		<th>
			<a href="addonmodules.php?module=enom_pro&view=whois_checker&show=<?php echo enom_pro_whois_show_only(); ?>&sort=<?php echo isset( $_GET['sort'] ) && 'desc' == $_GET['sort'] ? 'asc' : 'desc' ?>">TLD</a>
		</th>
		<th>WHOIS Server</th>
	</tr>
	</thead>
	<tbody>
	<?php foreach ( $list as $tld ) { ?>
		<?php $has_whois = array_key_exists( $tld, $parsed_whois_servers ); ?>
		<tr class="<?php echo $has_whois ? 'success' : 'danger' ?>">
			<td> <?php echo $tld; ?></td>
			<td>
				<?php if ( $has_whois ) : ?>
					<?php echo $parsed_whois_servers[ $tld ]; ?>
				<?php else : ?>
					<a class="btn btn-default btn-sm ajaxQ"
					   data-tld="<?php echo $tld ?>"
					   href="<?php echo enom_pro::MODULE_LINK ?>&action=delete_tld&tld=<?php echo $tld; ?>">
						Delete TLD from WHMCS.
						<div class="enom_pro_loader small hidden"></div>
					</a>
				<?php endif; ?>
			</td>
		</tr>
	<?php } ?>
	</tbody>
</table>

<script>
	var delete_queue_length = 0, counter_hide = false;
	jQuery(function ($) {
		var $missingTLDs = $("[data-tld]"),
		    $deleteAll   = $(".deleteAll");
		$deleteAll.on('click', function () {
			var confirmed = confirm('This will PERMANENTLY delete ALL TLD\'s from WHMCS.\n\nAre you sure?');
			if (confirmed) {
				$missingTLDs.trigger('delete');
				$(this).hide();
			}
			return false;
		});
		if ($missingTLDs.length > 0) {
			$deleteAll.removeClass('hidden');
		}
		$(".ajaxQ").on('click', function () {
			var confirmed = confirm("Are you sure you want to delete this from WHMCS?\n\nTHERE IS NO UNDO.");
			if (confirmed) {
				$(this).trigger('delete');
			}
			return false;
		}).on('delete', function () {
			var $this        = $(this),
			    thisTLD      = $this.data('tld'),
			    $loader      = $this.find('.enom_pro_loader'),
			    $counterWrap = $(".deleteCount"),
			    $counter     = $counterWrap.find('b');

			delete_queue_length++;
			enom_pro.ajaxLoadJS('jquery.ajaxq.js').success(function () {
				var delete_queue = $.ajaxq('deleteTLD', {
					url:        'addonmodules.php?module=enom_pro',
					global:     false,
					data:       {
						action: 'delete_tld',
						tld:    thisTLD
					},
					beforeSend: function () {
						$loader.removeClass('hidden');
						$counterWrap.removeClass('hidden').slideDown('fast');
						$counter.text(delete_queue_length);
						$this.addClass('disabled').prop('disabled', true);
						if (false !== counter_hide) {
							clearTimeout(counter_hide);
						}
					},
					success:    function () {
						delete_queue_length--;
						$this.addClass('btn-success').html('Deleted');
						setTimeout(function () {
							$this.closest('tr').slideUp().remove();
						}, 1000);
					}
				});

				delete_queue.complete(function () {
					$counter.text(delete_queue_length);
					$counterWrap.removeClass('alert-success alert-info');
					if (0 === delete_queue_length) {
						$counterWrap.addClass('alert-success').closest('.alert').html('Finished.');
						counter_hide = setTimeout(function () {
							$counterWrap.slideUp();
							counter_hide = false;
						}, 3000);
					} else {
						$counterWrap.addClass('alert-info');
					}
				});
			});
		});
	});
</script>