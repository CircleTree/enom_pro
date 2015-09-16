<?php
define( "CLIENTAREA", true );
require 'init.php';
//require_once ROOTDIR . '/modules/addons/enom_pro/enom_pro.php';
global $_LANG;
if ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'spinner' ) {
	try {
		$enom = new enom_pro();
		//Only return the API results if this a RESTful request from the AJAX widget
		$parts = $enom->getDomainParts( $_REQUEST['domain'] );
		//eNom pops the SLD up to TLD in the parsed response if there is no .com
		$sld_posing_as_tld = (bool) $parts['SLD']; //So we check if the SLD result is populated
		if ( ! $sld_posing_as_tld ) {
			$sld = $parts['TLD'];
			$tld = "com";//This doesn't matter to the enom API, as we're just looking for name spins
		} else {
			$sld = $parts['SLD'];
			$tld = $parts['TLD'];
		}
		$domain_name = $sld . '.' . $tld;
		$results     = $enom->getSpinner( $domain_name );
		if ( count( $results['domains'] ) > 0 ) {
			echo ' <h3>' . $_LANG['cartotherdomainsuggestions'] . '</h3>';
			echo '<div class="spinner_results_wrapper col_' . (int) $enom->get_addon_setting( 'spinner_columns' ) . '">';
			foreach ( $results['domains'] as $domain ) {
				if ( isset( $results['pricing'][ $domain['tld'] ] ) ) {
					//Only return spin results if we have pricing defined in WHMCS
					$id = str_replace( array( ".", "-" ), "_", $domain['domain'] );
					echo ' <div class="spin_result">';
					echo '<input type="checkbox" id="' . $id . '" name="domains[]" value="' . $domain['domain'] . '" />';
					echo '<label class="btn btn-default" for="' . $id . '">' . $domain['domain'];
					echo '</label>';
					echo '<select name="domainsregperiod[' . $domain['domain'] . ']" >';
					foreach ( $results['pricing'][ $domain['tld'] ] as $year => $price ) {
						if ( (int) $price > 0 && 'id' !== $year ) {
							echo '<option value="' . $year . '">' . $year . ' ' . $_LANG['orderyears'] . ' @ ' . $price . '</option>';
						}
					}
					echo '</select>';
					echo '</div>';
				} else {
					if ( $enom->debug() ) {
						echo 'This TLD doesn\'t have a price defined:' . $domain['tld'] . ' <br/>';
					}
				}
			}

			if ( $enom->get_addon_setting( 'spinner_checkout' ) == "on" ) {
				//Only show the add to cart button if enabled

				$css_class = enom_pro::get_addon_setting( 'cart_css_class' );
				if ( enom_pro::get_addon_setting( 'custom_cart_css_class' ) !== "" ) {
					$css_class = enom_pro::get_addon_setting( 'custom_cart_css_class' );
				}
				if ( is_null( $css_class ) ) {
					$css_class = 'btn btn-primary';
				}
				echo '<input class="' . $css_class . '" type="submit" value="' . $_LANG['addtocart'] . '" />';
			}
			echo '</div>';
		} else {
			if ( $enom->debug() ) {
				echo 'No results';
			}
		}
	} catch ( Exception $e ) {
		if ( $enom->debug() ) {
			echo $e->getMessage();
		}
	}
	die();
}
//TODO remove this & verify with WHMCS Beta2+
// Go to enom_srv.php to throw invalid class exception
if ( ! class_exists( 'WHMCS_ClientArea' ) ) {
	class WHMCS_ClientArea extends \WHMCS\ClientArea {

	}
}
$ca = new WHMCS_ClientArea();
$ca->setPageTitle( 'SRV Records' );
$ca->initPage();
$ca->requireLogin();
//die;
if ( isset( $_REQUEST['action'] ) ) {
	switch ( $_REQUEST['action'] ) {
		case 'save_srv':
			$domain = mysql_fetch_assoc( mysql_query( 'SELECT * FROM `tbldomains` WHERE `id`= ' . (int) $_REQUEST['id'] . ' AND `userid` = ' . $_SESSION['uid'] ) );
			$enom   = new enom_pro();
			$enom->setDomain( $domain['domain'] );

			$records = $enom->get_SRV_records();
			if ( isset( $_REQUEST['records'] ) ) {
				foreach ( $_REQUEST['records'] as $index => $record ) {
					foreach ( $record as $k => $v ) {
						//Hostid is not empty, allow edits
						if ( $k == 'hostid' && trim( $v ) != "" ) {
							break;
						}
						//Don't allow empty
						if ( trim( $v ) == "" && $k != 'hostid' ) {
							//Except hostid, which will be empty on new
							unset( $_REQUEST['records'][ $index ] );
							break;
						}
					}
				}
				/*
				*/
				if ( ! empty( $_REQUEST['records'] ) ) {
					try {
						$enom->set_SRV_Records( $_REQUEST['records'] );
					} catch ( EnomException $e ) {
						header( "HTTP/1.0 400 Bad Request" );
						echo 'Error: ' . implode( ', ', $e->get_errors() );
						die;
					}
					$records = $enom->get_SRV_records();
				}
			}
			header( 'Content-type: application/json' );
			echo json_encode( $records );
			break;
	}
	die;
}
if ( ! isset( $_GET['id'] ) ) {
	$query         = 'SELECT * FROM `tbldomains` WHERE `registrar` = \'enom\'  AND `userid` = ' . $_SESSION['uid'];
	$mysql_result  = mysql_query( $query );
	$results_array = array();
	while ( $result = mysql_fetch_assoc( $mysql_result ) ) {
		$results_array[ $result['id'] ] = $result;
	}
	$ca->assign( 'domains', $results_array );
} else {
	$domain = mysql_fetch_assoc( mysql_query( 'SELECT * FROM `tbldomains` WHERE `id`= ' . (int) $_REQUEST['id'] . ' AND `userid` = ' . $_SESSION['uid'] ) );
	$ca->assign( 'domain', $domain );
}
$ca->assign( 'id', ( isset( $_GET['id'] ) ? $_GET['id'] : false ) );
$number_of_rows = 5;
$records_array  = array_keys( array_fill( 1, $number_of_rows, 0 ) );
$ca->assign( 'records', $records_array );
$ca->setTemplate( 'enom_srv' );
$ca->output();
