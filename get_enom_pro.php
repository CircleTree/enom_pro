<?php
define( "WHMCS", true );
require( "init.php" );
if ( ! isset( $_REQUEST['key'] ) ) {
	die( 'No Key' );
}
if ( ! isset( $_REQUEST['id'] ) ) {
	die( 'Missing Product ID' );
}
/*
 * id	serviceid	licensekey	validdomain	validip	validdirectory	reissues	status	lastaccess
 */
$result = select_query( 'mod_licensing',
	'licensekey,status,validip,serviceid',
	array( 'licensekey' => $_REQUEST['key'] ) );
$data   = mysql_fetch_assoc( $result );
if ( $data ) {
	//Check parent account for at least one active addon
	$tblHosting = mysql_fetch_assoc( select_query( 'tblhosting', 'userid', array( 'id' => $data['serviceid'] ) ) );
	$userID     = $tblHosting['userid'];
	$result1    = select_query( 'tblhosting', 'id', array( 'packageid' => 31, 'userid' => (int) $userID ) );
	$hostingIDs = array();
	while ( $activeProducts = mysql_fetch_row( $result1 ) ) {
		$hostingIDs[] = reset( $activeProducts );
	}
	$foundActiveUpdates = false;
	foreach ( $hostingIDs as $thisHostingID ) {
		$tblAddons = mysql_fetch_assoc( select_query( 'tblhostingaddons',
			'status',
			array(
				'addonid'   => 7,
				'status'    => "Active",
				//Only get active status, in case there are > 1 addon (one expired, one paid today, for example)
				'hostingid' => $thisHostingID
			) ) );
		if ( $tblAddons && $tblAddons['status'] == "Active" ) {
			$foundActiveUpdates = true;
			break;
		}
	}
	if ( ! $foundActiveUpdates ) {
		header( 'Location: https://mycircletree.com/client-area/cart.php?gid=addons' );
		die( 'Support & Updates Expired' );
	}
	if ( $data['status'] == 'Active' || $data['status'] == 'Reissued' ) {
		if ( isset( $_REQUEST['beta'] ) && 1 == $_REQUEST['beta'] ) {
			$ctwpf_zip = '/home/mycircle/downloads/enom_pro_BETA.zip';
		} else {
			$ctwpf_zip = '/home/mycircle/downloads/enom_pro.zip';
		}
		if ( ! file_exists( $ctwpf_zip ) ) {
			die( 'Error reading release: ' . basename( $ctwpf_zip ) );
		}
		header( 'HTTP/1.1 200 OK' );
		header( "Content-Transfer-Encoding: binary" );
		header( 'Cache-Control: public' );
		header( 'Content-Description: File Transfer' );
		header( 'Content-Type: application/zip' );
		header( 'Content-Disposition: attachment; filename="enom_pro.zip"' );
		header( 'Content-Length: ' . filesize( $ctwpf_zip ) );
		readfile( $ctwpf_zip );
	} else {
		echo $data['status'];
	}
} else {
	header( 'HTTP/1.1 404 Not Found' );
	echo 'No license found';
}
die;
