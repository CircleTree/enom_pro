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
?>
<div class="row">
	<?php while ($row = mysql_fetch_assoc($result)) :
		$thisTLD = $row['extension'];
		$whoisOK = false;
		if (array_key_exists( $thisTLD, $parsed_whois_servers)) {
			$message = 'Whois OK for:' . $thisTLD;
			$whoisOK = true;
		} else {
			$message = 'Missing WHOIS server for: ' . $thisTLD;
		}
	?>
		<div class="col-xs-3">
			<div class="alert <?php echo $whoisOK ? 'alert-success' : 'alert-danger' ?>">
				<?php echo $message; ?>
			</div>
		</div>
	<?php endwhile; ?>
</div>