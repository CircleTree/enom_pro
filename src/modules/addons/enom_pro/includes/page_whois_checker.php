<?php
/**
 * Project: enom_pro
 * Build: @BUILD_DATE@
 * Version: @VERSION@
 */

$filepath = ROOTDIR . '/includes/whoisservers.php';
if ( ! file_exists( $filepath ) ) {
	throw new Exception( 'Error loading ' . $filepath );
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
$result = select_query( 'tbldomainpricing', 'extension' );
if ( false === mysql_num_rows( $result ) ) {
	throw new Exception( "No TLD's found in WHMCS. Please configure TLD's before checking for WHOIS servers" );
}
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

function enom_pro_whois_render_row( $whoisOK, $message, $thisTLD ) { ?>
	<div class="col-xs-3">
		<div class="alert <?php echo $whoisOK ? 'alert-success' : 'alert-danger' ?>">
			<?php echo $message; ?>
			<?php if ( ! $whoisOK ) : ?>
				<a class="btn btn-default btn-sm ajaxQ"
				   data-tld="<?php echo $thisTLD ?>"
				   href="<?php echo enom_pro::MODULE_LINK ?>&action=delete_tld&tld=<?php echo $thisTLD ?>">
					Delete TLD from WHMCS.
				</a>
				<div class="enom_pro_loader small hidden"></div>
			<?php endif;?>
		</div>
	</div>
<?php
}

?>
<a
	class="btn btn-default <?php echo 'missing' == enom_pro_whois_show_only() ? 'active' : '' ?>"
	href="<?php echo enom_pro::MODULE_LINK ?>&view=whois_checker&show=missing">Only Show Missing</a>
<a
	class="btn btn-default <?php echo 'ok' == enom_pro_whois_show_only() ? 'active' : '' ?>"
	href="<?php echo enom_pro::MODULE_LINK ?>&view=whois_checker&show=ok">Only Show OK</a>
<a
	class="btn btn-default <?php echo 'all' == enom_pro_whois_show_only() ? 'active' : '' ?>"
	href="<?php echo enom_pro::MODULE_LINK ?>&view=whois_checker">Show All</a>
<?php if ('missing' == enom_pro_whois_show_only()) :?>
	<a href="#" class="deleteAll btn btn-danger">Delete All Missing WHOIS Servers from WHMCS</a>
<?php endif;?>
<div class="deleteCount alert alert-info hidden">
	<span><b></b> TLD's in Delete Queue</span>
</div>
<div class="row">
	<?php ob_start(); ?>
	<?php while ( $row = mysql_fetch_assoc( $result ) ) :
		$thisTLD = $row['extension'];
		$whoisOK = false;
		if ( array_key_exists( $thisTLD, $parsed_whois_servers ) ) {
			$message = 'Using Server <code>' . $parsed_whois_servers[ $thisTLD ] . '</code> for TLD <code>' . $thisTLD . '</code>';
			$whoisOK = true;
		} else {
			$message = 'Missing WHOIS server for: ' . $thisTLD;
		}
		if ( 'all' == enom_pro_whois_show_only() ) {
			enom_pro_whois_render_row( $whoisOK, $message, $thisTLD );
		} elseif ( 'missing' == enom_pro_whois_show_only() && false === $whoisOK ) {
			enom_pro_whois_render_row( $whoisOK, $message, $thisTLD );
		} elseif ( 'ok' == enom_pro_whois_show_only() && true == $whoisOK ) {
			enom_pro_whois_render_row( $whoisOK, $message, $thisTLD );
		}
		endwhile;

		if (5 > ob_get_length()) {
			echo '<div class="alert alert-info"><h3>No TLD\'s found for this view.</h3></div>';
		}
	ob_end_flush();
 ?>

</div>

<script>
	var delete_queue_length = 0, counter_hide = false;
jQuery(function ($) {
	$(".deleteAll").on('click', function  (){
		var confirmed = confirm('This will PERMANENTLY delete ALL TLD\'s from WHMCS.\n\nAre you sure?');
		if (confirmed) {
			$("[data-tld]").trigger('delete');
			$(this).hide();
		}
		return false;
	});
	$(".ajaxQ").on('click', function  () {
		var confirmed = confirm("Are you sure you want to delete this from WHMCS?\n\nTHERE IS NO UNDO.")
		if (confirmed) {
			$(this).trigger('delete');
		}
		return false;
	}).on('delete', function  (){
		var thisTLD = $(this).data('tld'),
		    $loader = $(this).find('.enom_pro_loader'),
		    $counterWrap = $(".deleteCount"),
		    $counter = $counterWrap.find('b');
		delete_queue_length++;
		var delete_queue = $.ajaxq('deleteTLD', {
			url       : 'addonmodules.php?module=enom_pro',
			global    : false,
			data      : {
				action: 'delete_tld',
				tld: thisTLD
			},
			beforeSend: function  (){
				$loader.removeClass('hidden');
				$counterWrap.removeClass('hidden').slideDown('fast');
				$counter.text(delete_queue_length);
				if (false !== counter_hide) {
					clearTimeout(counter_hide);
				}
			},
			success: function  (){
				delete_queue_length--;
				$("[data-tld='"+thisTLD+"']").closest('.col-xs-3').animate({height: 'hide'}, 350);
			}
		});
		delete_queue.complete(function  (){
			$counter.text(delete_queue_length);
			$counterWrap.removeClass('alert-success alert-info');
			if (0 == delete_queue_length) {
				$counterWrap.addClass('alert-success');
				counter_hide = setTimeout(function  (){
					$counterWrap.slideUp();
					counter_hide = false;
				}, 2000);
			} else {
				$counterWrap.addClass('alert-info');
			}
		});
	});
});
</script>