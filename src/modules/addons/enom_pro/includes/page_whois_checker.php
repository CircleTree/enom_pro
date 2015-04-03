<?php
/**
 * Project: enom_pro
 * Build: @BUILD_DATE@
 * Version: @VERSION@
 */

$filepath = ROOTDIR . '/includes/whoisservers.php';
if (! file_exists($filepath)) {
	throw new Exception('Error loading ' . $filepath);
}
$file_contents = file_get_contents( $filepath );
$file_array = explode(PHP_EOL, $file_contents);
/**
 * @var array tld => server address
 */
$parsed_whois_servers = array();
foreach ($file_array as $line) {
	$file_line = explode('|', $line);
	//Set response to tld => server address
	if (! empty($file_line[0]) && ! empty($file_line[1])) {
		$parsed_whois_servers[$file_line[0]] = $file_line[1];
	}
}
$result = select_query('tbldomainpricing', 'extension');
if (false === mysql_num_rows($result)) {
	throw new Exception("No TLD's found in WHMCS. Please configure TLD's before checking for WHOIS servers");
}
function enom_pro_whois_show_only (){
	if (! isset($_GET['show'])) {
		return 'all';
	}
	if ('missing' == $_GET['show']) {
		return 'missing';
	}
	if ('ok' == $_GET['show']) {
		return 'ok';
	}
}
function enom_pro_whois_render_row ($whoisOK, $message){ ?>
	<div class="col-xs-3">
		<div class="alert <?php echo $whoisOK ? 'alert-success' : 'alert-danger' ?>">
			<?php echo $message; ?>
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
<div class="row">
	<?php while ($row = mysql_fetch_assoc($result)) :
		$thisTLD = $row['extension'];
		$whoisOK = false;
		if (array_key_exists( $thisTLD, $parsed_whois_servers)) {
			$message = 'Using Server <code>'.$parsed_whois_servers[$thisTLD].'</code> for TLD <code>'.$thisTLD.'</code>';
			$whoisOK = true;
		} else {
			$message = 'Missing WHOIS server for: ' . $thisTLD;
		}
		if ('all' == enom_pro_whois_show_only()) {
			enom_pro_whois_render_row($whoisOK, $message);
		} elseif ('missing' == enom_pro_whois_show_only() && false === $whoisOK) {
			enom_pro_whois_render_row($whoisOK, $message);
		} elseif ('ok' == enom_pro_whois_show_only() && true == $whoisOK) {
			enom_pro_whois_render_row($whoisOK, $message);
		}
	?>

	<?php endwhile; ?>
</div>