<?php
/**
 *
 * @author robertgregor
 * @codeCoverageIgnore
 */
class enom_pro_license
{
    private $license;
    private static $latest_version = false;
    const DO_UPGRADE_URL = 'addonmodules.php?module=enom_pro&action=do_upgrade';
    public function  __construct()
    {
        $license = enom_pro::get_addon_setting('license');
        //Prep return string
        $return = "";
        if ($license == "") {
            $return .= '<h1><span class="textred">No License entered:</span>
                    <a href="configaddonmods.php">Enter a License on the addon page</a></h1>';
            $return .= '<h2><a href="https://mycircletree.com/client-area/order/?gid=5" target="_blank">
                    Visit myCircleTree.com to get a license &amp; support.</a></h2>';
            throw new LicenseExeption($return);
        } elseif (!$this->checkLicense()) {
            $return .='<h1>Uh, oh! There seems to be a problem with your license</h1>';
            $support_link = "
                https://mycircletree.com/client-area/submitticket.php?step=2&deptid=7&
            subject=Product%20Support%20for:'.$this->productname.'.%20License:%20'.$license.'";
            $return .='<h2>Please <a class="btn" href="'.enom_pro::minify($support_link).'">
                    click here to open a support ticket from the Circle Tree client area</a></h2>';
            $return .='<h3>Enter a new License from the <a href="configaddonmods.php">addon page</a></h3>';
            $return .='<div class="errorbox"><b>Support Information</b><br/>';
            $return .='License Number: '.$license.'<br/>';
            if (isset($this->message)) {
                $return .='License Error: '.$this->message.'<br/>';
            }
            $return .='License Status: '.$this->status.'<br/>';
            $return .='</div>';
            throw new LicenseExeption($return);
            $this->error = true;
        } else {
            //No license err
            $this->error = FALSE;
        }
    }
    /**
     * utility to check local license, latest version, etc.
     * @return boolean true for license OK
     */
    public function checkLicense ()
    {
        $query = "SELECT `local` FROM `mod_enom_pro`";
        $local = mysql_fetch_assoc(mysql_query($query));
        $localKey = $local['local'];
        $results = $this->get_remote_license(enom_pro::get_addon_setting('license'),$localKey);
        $this->license = $results;
        $this->latest_vesion = @$results['latestversion'];
        $this->company = @$results['companyname'];
        $this->name = @$results['registeredname'];
        $this->productname = @$results['productname'];
        if ($results["status"]=="Active") {
            $this->status = "Active";
            # Allow Script to Run
            if (isset($results["localkey"])) {
                $localkeydata = $results["localkey"];
                # Save Updated Local Key to DB or File
                $query = "UPDATE `mod_enom_pro` SET `local`='".$localkeydata."' WHERE `id`=0";
                mysql_query($query);
            }

            return true;
        } elseif ($results["status"]=="Invalid") {
            $this->status = "Invalid";
            $this->message = @$results['description'];

            return false;
        } elseif ($results["status"]=="Expired") {
            $this->status = "Expired";

            return false;
        } elseif ($results["status"]=="Suspended") {
            $this->status = "Suspended";

            return false;
        }
    }
    public function  clearLicense()
    {
        mysql_query('UPDATE  `mod_enom_pro` SET  `local` =  \'\' WHERE  `mod_enom_pro`.`id` =0;');
    }
    /**
     * Checks for the latest version of the addon
     * @return bool
     */
    public static function is_update_available()
    {
        return true;
        //Compare the response from the server to the locally defined version
        if (version_compare(self::get_latest_version(), ENOM_PRO_VERSION, 'gt')) {
            //The remote is newer than local, return the string upgrade notice
            return true;
        } else {
            return false;
        }
    }
    /**
     * @deprecated 2.1
     */
    public function updateAvailable ()
    {
        enom_pro::deprecated('updateAvailable', '2.1', 'enom_pro_license::is_update_available()');
    }
    private static function _get_latest_version ()
    {
        if (true == self::$latest_version) {
            return self::$latest_version;
        }
        $version_file = ENOM_PRO_TEMP . 'version';
        if (file_exists($version_file)) {
            self::$latest_version = file_get_contents($version_file);
            return self::$latest_version;
        }
        $latest_version_xml = enom_pro::curl_get('http://mycircletree.com/versions/enom_pro_version.xml');
        $latest_version = @simplexml_load_string($latest_version_xml);
        if (is_object($latest_version)) {
            $latest_version_string = (string) $latest_version->version;
            $handle = fopen($version_file, 'w');
            fwrite($handle, $latest_version_string);
            fclose($handle);
        }  else {
            $latest_version_string = '1.0';
        }
        self::$latest_version = $latest_version_string;
        return self::$latest_version;
    }
    /**
     * Gets latest available release version number
     * @return string latest version number
     */
    public static function get_latest_version ()
    {
        return self::_get_latest_version();
    }
    /**
     * 
     * @return string Time ago
     */
    public static function get_last_checked_time_ago ()
    {
        $version_file = ENOM_PRO_TEMP . 'version';
        if (! file_exists($version_file)) {
            self::get_latest_version();
        }
        return enom_pro::time_ago(filemtime($version_file));
    }
    /**
     * Deletes cached version data;
     */
    public static function delete_latest_version ()
    {
        self::$latest_version = false;
        $version_file = ENOM_PRO_TEMP . 'version';
        unlink($version_file);
    }
    private function get_remote_license($licensekey,$localkey="")
    {
        $whmcsurl = "http://mycircletree.com/client-area/";
        $licensing_secret_key = "@SECRET@";
        $check_token = time().md5(mt_rand(1000000000,9999999999).$licensekey);
        $checkdate = date("Ymd"); # Current date
        $usersip = isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : false;
        $server_name = isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : false;
        //Dev Fallbacks
        if (! $usersip)
            $usersip = '127.0.0.1';
        if (! $server_name )
            $server_name = 'localhost';
        $localkeydays = 28; # How long the local key is valid for in between remote checks
        $allowcheckfaildays = 7; 
        $localkeyvalid = false;
        if ($localkey) {
            $localkey = str_replace("\n",'',$localkey); # Remove the line breaks
            $localdata = substr($localkey,0,strlen($localkey)-32); # Extract License Data
            $md5hash = substr($localkey,strlen($localkey)-32); # Extract MD5 Hash
            if ($md5hash==md5($localdata.$licensing_secret_key)) {
                $localdata = strrev($localdata); # Reverse the string
                $md5hash = substr($localdata,0,32); # Extract MD5 Hash
                $localdata = substr($localdata,32); # Extract License Data
                $localdata = base64_decode($localdata);
                $localkeyresults = unserialize($localdata);
                $originalcheckdate = $localkeyresults["checkdate"];
                if ($md5hash==md5($originalcheckdate.$licensing_secret_key)) {
                    $localexpiry = date("Ymd",mktime(0,0,0,date("m"),date("d")-$localkeydays,date("Y")));
                    if ($originalcheckdate>$localexpiry) {
                        $localkeyvalid = true;
                        $results = $localkeyresults;
                        $validdomains = explode(",",$results["validdomain"]);

                        if (!in_array($server_name, $validdomains)) {
                            $localkeyvalid = false;
                            $localkeyresults["status"] = "Invalid";
                            $results = array();
                        }
                        $validips = explode(",",$results["validip"]);
                        if (!in_array($usersip, $validips)) {
                            $localkeyvalid = false;
                            $localkeyresults["status"] = "Invalid";
                            $results = array();
                        }
                        if ($results["validdirectory"]!=dirname(__FILE__)) {
                            $localkeyvalid = false;
                            $localkeyresults["status"] = "Invalid";
                            $results = array();
                        }
                    }
                }
            }
        }
        if (!$localkeyvalid) {
            $postfields["licensekey"] = $licensekey;
            $postfields["domain"] = $server_name;
            $postfields["ip"] = $usersip;
            $postfields["dir"] = dirname(__FILE__);
            if ($check_token) $postfields["check_token"] = $check_token;
            if (function_exists("curl_exec")) {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $whmcsurl."modules/servers/licensing/verify.php");
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
                curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                $data = curl_exec($ch);
                curl_close($ch);
            }
            if (!$data) {
                $localexpiry = date("Ymd",mktime(0,0,0,date("m"),date("d")-($localkeydays+$allowcheckfaildays),date("Y")));
                if ($originalcheckdate > $localexpiry) {
                    $results = $localkeyresults;
                } else {
                    $results["status"] = "Invalid";
                    $results["description"] = "Remote Check Failed";

                    return $results;
                }
            } else {
                preg_match_all('/<(.*?)>([^<]+)<\/\\1>/i', $data, $matches);
                $results = array();
                foreach ($matches[1] AS $k=>$v) {
                    $results[$v] = $matches[2][$k];
                }
            }
            if (isset($results["md5hash"])) {
                if ($results["md5hash"]!=md5($licensing_secret_key.$check_token)) {
                    $results["status"] = "Invalid";
                    $results["description"] = "MD5 Checksum Verification Failed";

                    return $results;
                }
            }
            if ($results["status"]=="Active") {
                $results["checkdate"] = $checkdate;
                $results["latestversion"] = $this->get_latest_version();
                $data_encoded = serialize($results);
                $data_encoded = base64_encode($data_encoded);
                $data_encoded = md5($checkdate.$licensing_secret_key).$data_encoded;
                $data_encoded = strrev($data_encoded);
                $data_encoded = $data_encoded.md5($data_encoded.$licensing_secret_key);
                $data_encoded = wordwrap($data_encoded,80,"\n",true);
                $results["localkey"] = $data_encoded;
            }
            $results["remotecheck"] = true;
        }

        return $results;
    }

}
