<?php

/**
 * @author robertgregor
 * @codeCoverageIgnore
 */
class enom_pro_license {

	const DO_UPGRADE_URL = 'addonmodules.php?module=enom_pro&action=do_upgrade';
	private static $latest_version = false;
	/**
	 * @var
	 */
	private $license;
	private $key;
	private $updates_addon_name = "Support & Updates - eNom PRO";
	private $company;
	private $name;

	/**
	 * Checks remote license
	 * @throws LicenseException
	 */
	public function  __construct() {

		$this->key = enom_pro::get_addon_setting( 'license' );
		//Prep return string
		$return = "";
			//No license err
		$this->error = false;
	}

	public function  get_id() {

		return 0;
	}

	/**
	 * @return array 'status', duedate
	 */
	public function get_supportandUpdates() {

		$return = [];
		$return['status'] = 'active';
		$return['duedate'] = '1/1/2099';
		return $return;
	}

	/**
	 * Checks to see if we're running in beta mode
	 * @return bool
	 */
	public static function isBetaOptedIn() {

		return enom_pro::get_addon_setting( 'beta' ) == 'on' ? true : false;
	}

	/**
	 * Gets customer name
	 * @return string|false
	 */
	public function getCustomerName() {

		return $this->name;
	}

	/**
	 * utility to check local license, latest version, etc.
	 * @return boolean true for license OK
	 */
	public function checkLicense() {

		self::$latest_version = '3.0';
		$this->company        = 'My Company';
		$this->name           = isset( $results['registeredname'] ) ? $results['registeredname'] : false;
		$this->productname    = 'eNom PRO Open Source';
		$this->status = "Active";
		return true;
	}

	public static function  clearLicense() {

		$query = 'UPDATE  `mod_enom_pro` SET  `local` =  \' \' WHERE  `id` =0;';
		enom_pro::query( $query );
	}

	/**
	 * Checks for the latest version of the addon
	 * @return bool
	 */
	public static function is_update_available() {

		if ( enom_pro::isBetaBuild() || self::isBetaOptedIn() ) {
			//Compare hashes
			if ( ENOM_PRO_VERSION == self::get_latest_version() ) {
				return false;
			} else {
				return true;
			}
		} else {
			//Compare the response from the server to the locally defined version
			if ( version_compare( self::get_latest_version(),
				ENOM_PRO_VERSION,
				'gt' ) ) {
				//The remote is newer than local, return the string upgrade notice
				return true;
			} else {
				return false;
			}
		}
	}

	/**
	 * Is this a beta build that can be downgraded to a public release?
	 * @return bool
	 */
	public static function  is_downgrade_available() {

		if ( ! self::isBetaOptedIn() && enom_pro::isBetaBuild() ) {
			//Current build is BETA, and opted in
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Gets latest available release version number
	 * @return string latest version number
	 */
	public static function get_latest_version() {

		return self::_get_latest_version();
	}

	/**
	 * Last checked for updates
	 * @return string Time ago
	 */
	public static function get_last_checked_time_ago() {

		$version_timeout = self::isBetaOptedIn() ? '-1 Week' : '-1 Month';
		if ( enom_pro::cache_file_is_older_than( self::getVersionCacheFile(), $version_timeout ) ) {
			self::delete_latest_version();
		}
		$version_file = self::getVersionCacheFile();
		if ( ! file_exists( $version_file ) ) {
			self::get_latest_version();
		}

		return enom_pro::time_ago( filemtime( $version_file ), 1 );
	}

	/**
	 * Deletes cached version data;
	 */
	public static function delete_latest_version() {

		self::$latest_version = false;
		unlink( self::getVersionCacheFile() );
	}

	/**
	 * @deprecated 2.1
	 */
	public function updateAvailable() {

		enom_pro::deprecated( 'updateAvailable',
			'2.1',
			'enom_pro_license::is_update_available()' );
	}

	/**
	 * @return string
	 */
	private static function getVersionCacheFile() {

		if ( self::isBetaOptedIn() ) {
			$filename = 'beta_version';
		} else {
			$filename = 'version';
		}

		return ENOM_PRO_TEMP . $filename . '.cache';
	}

	private static function _get_latest_version() {

		$version_file = self::getVersionCacheFile();
		if ( file_exists( $version_file ) ) {
			self::$latest_version = file_get_contents( $version_file );

			return self::$latest_version;
		}
		$xml_filename       = self::isBetaOptedIn() ? 'enom_pro_version_beta.xml' : 'enom_pro_version.xml';
		$latest_version_xml = enom_pro::curl_get( "http://mycircletree.com/versions/{$xml_filename}" );
		$latest_version     = @simplexml_load_string( $latest_version_xml );
		if ( is_object( $latest_version ) ) {
			$latest_version_string = (string) $latest_version->version;
			$handle                = fopen( $version_file, 'w' );
			fwrite( $handle, $latest_version_string );
			fclose( $handle );
		} else {
			$latest_version_string = '1.0';
		}
		self::$latest_version = $latest_version_string;

		return self::$latest_version;
	}
}
