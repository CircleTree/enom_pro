<?php 
class enom_pro_controller {
    private $enom;
    function __construct() {
        if (method_exists(__CLASS__, $_GET['action'])) {
            $this->enom = new enom_pro();
            call_user_func(array(__CLASS__, $_GET['action']));
        } else {
            throw new InvalidArgumentException('Unknown action: ' . $_GET['action']);
        }
    }
    private function resend_enom_transfer_email ()
    {
        $response = $this->enom->resend_activation((string) $_REQUEST['domain']);
        if (is_bool($response)) {
            echo "Sent!";
        }
    }
    private function do_upgrade ()
    {
        $manual_files = $this->enom->do_upgrade();
        $_SESSION['manual_files'] = $manual_files;
        header('Location: ' . enom_pro::MODULE_LINK .  '&upgraded');
    }
    private function dismiss_manual_upgrade ()
    {
        unset($_SESSION['manual_files']);
        header('Location: ' . enom_pro::MODULE_LINK .  '&dismissed');
    }
    private function do_upgrade_check ()
    {
        enom_pro_license::delete_latest_version();
        header('Location: ' . enom_pro::MODULE_LINK .  '&checked');
    }
    private function resubmit_enom_transfer_order ()
    {
        $response = $this->enom->resubmit_locked((int) $_REQUEST['orderid']);
        if (is_bool($response)) {
            echo "Submitted!";
        }       
    }
    private function install_ssl_template ()
    {
        $return = $this->enom->install_ssl_email();
        header('Location: ' . enom_pro::MODULE_LINK . '&ssl_email='.$return);
    }
    private function set_results_per_page ()
    {
        $per_page = (int) $_REQUEST['per_page'];
        if ($per_page > 100 || $per_page < 0) {
            $per_page = 25;
        }
        enom_pro::set_addon_setting('import_per_page', $per_page);
        echo 'set';
    }
    private function get_domains ()
    {
        if (isset($_GET['tab'])) {
            switch ($_GET['tab']) {
            	case 'redemption':
            	    $tab = 'RGP';
            	    break;
            	case 'expiring':
            	    $tab = 'ExpiringNames';
            	    break;
            	case 'expired':
            	    $tab = 'ExpiredDomains';
            	    break;
            }
        } else {
            $tab = 'IOwn';
        }
        $start = isset($_GET['start']) ? $_GET['start'] : 1;
        $domains = $this->enom->getDomainsTab($tab, enom_pro::get_addon_setting('import_per_page'), $start);
        require_once ENOM_PRO_INCLUDES . 'domain_widget_response.php';
    }
    private function render_import_table ()
    {
        ob_start();
        require_once ENOM_PRO_INCLUDES . 'domain_import_table.php';
        $contents = ob_get_contents();
        ob_end_clean();
        header('Content-Type: application/json');
        header('Content-Encoding: gzip');
        echo gzencode(json_encode(array(
                'html'=>$contents,
                'cache_date' => $this->enom->get_domain_cache_date(),
        )), 9);
    }
    private function get_domain_whois ()
    {
        $whois = $this->enom->getWHOIS($_REQUEST['domain']);
        $response = array(
                'email' => $whois['registrant']['emailaddress'],
        );
        header('Content-Type: application/json');
        echo json_encode($response);
    }
    private function clear_cache ()
    {
        $this->enom->clear_domains_cache();
        header('Location: addonmodules.php?module=enom_pro&view=domain_import&cleared');
    }
    private function clear_price_cache ()
    {
        $this->enom->clear_price_cache();
        header('Location: addonmodules.php?module=enom_pro&view=pricing_import&cleared');
    }
    private function get_pricing_data ()
    {
        $this->enom->getAllDomainsPricing();
        echo 'success';
    }
    private function add_enom_pro_domain_order ()
    {
        $data = array(
                'clientid' => $_REQUEST['clientid'],
                'domaintype' => array('register'),
                'domain'	=> array( $_REQUEST['domain'] ),
                'paymentmethod' => $_REQUEST['paymentmethod']
        );
        if (isset($_REQUEST['regperiod'])) {
            $data['regperiod'] = array( $_REQUEST['regperiod'] );
        }
        if (isset($_REQUEST['dnsmanagement'])) {
            $data['dnsmanagement'] = array('on');
        }
        if (isset($_REQUEST['idprotection'])) {
            $data['idprotection'] = array('on');
        }
        if (!isset($_REQUEST['noemail'])) {
            $data['noemail'] = true;
        }
        if (!isset($_REQUEST['noinvoice'])) {
            $data['noinvoice'] = true;
        }
        if (!isset($_REQUEST['noinvoiceemail'])) {
            $data['noinvoiceemail'] = true;
        }
        //We have to set this by default because WHMCS stops execution if there is a domain configuration issue
        header("HTTP/1.0 404 Not Found");
        $response = localapi('addorder', $data);
        header('Content-Type: text/html');
        $success = 'success' == $response['result'] ? true : false;
        if ($success) {
            //Here we replace the error header :-)
            header("HTTP/1.0 200 Ok", true);
            $message = 'Created new order #'.$response['orderid'];
            if (! empty($response['invoiceid']))
                $message .= PHP_EOL . 'Created new invoice #'.$response['invoiceid'];
        } else {
            $message = 'Error: '. $response['message'];
        }
        header('Content-Type: application/json', true);
        echo json_encode( array(
                'success' => $success,
                'message' => $message
        )
        );
    }
    private function save_domain_pricing ()
    {
        if (isset($_POST['pricing'])) {
            $validated_data = array();
            $tlds = $this->enom->getAllDomainsPricing();
            foreach ($_POST['pricing'] as $tld => $years) {
                $tld_pricing = array();
                foreach ($years as $year => $price) {
                    $validated_year = (int) $year;
                    if ($validated_year > 10 || $validated_year <= 0) {
                        $validated_year = false;
                    }
                    if ($validated_year) {
                        $tld_pricing[$validated_year] = (double) $price;
                    }
                }
                $validated_tld = (string) $tld;
                if (in_array($validated_tld, $tlds)) {
                    $validated_data[$validated_tld] = $tld_pricing;
                }
            }
        }
        $updated = $new = $deleted = 0;
        foreach ($validated_data as $tld => $pricing) {
            $pricing_data = array(
                    'msetupfee'     => $pricing[1],
                    'qsetupfee'     => $pricing[2],
                    'ssetupfee'     => $pricing[3],
                    'asetupfee'     => $pricing[4],
                    'bsetupfee'     => $pricing[5],
                    'monthly'       => $pricing[6],
                    'quarterly'     => $pricing[7],
                    'semiannually'  => $pricing[8],
                    'annually'      => $pricing[9],
                    'biennially'    => $pricing[10],
                    'currency'      => 1,
            );
            $registration_types = array('domainregister', 'domainrenew', 'domaintransfer');
            $existing_pricing = $this->enom->get_whmcs_domain_pricing($tld);
            if (! empty($existing_pricing)) {
                //Update
                $result = mysql_fetch_assoc(select_query('tbldomainpricing', 'id', array('extension' => '.' . $tld)));
                $relid = $result['id'];
                $total_minus_1 = 0;
                foreach ($pricing_data as $key => $price) {
                    if ($price == '-1.00') {
                        $total_minus_1++;
                    }
                }
                if ($total_minus_1 == 10 ) {
                    $sql = 'DELETE FROM `tblpricing` WHERE `relid`="'.$relid.'"';
                    mysql_query($sql);
                    $sql = 'DELETE FROM `tbldomainpricing` WHERE `id` = "'.$relid.'"';
                    mysql_query($sql);
                    $deleted++;
                    //delete
                } else {
                    foreach ($registration_types as $type) {
                        $where = array('type' => $type, 'relid' => $relid);
                        update_query('tblpricing', $pricing_data, $where);
                    }
                    $updated++;
                }
            } else {
                //Insert
                $relid = insert_query('tbldomainpricing', array('extension' => '.' . $tld));
                $pricing_data['relid'] = $relid;
                foreach ($registration_types as $type) {
                    $this_pricing_data = $pricing_data;
                    $this_pricing_data['type'] = $type;
                    insert_query('tblpricing', $this_pricing_data);
                }
                $new++;
            }
        }
        $url = enom_pro::MODULE_LINK . '&view=pricing_import';
        if (isset($_POST['start']) && $_POST['start'] > 0) {
            $url .= '&start='.(int) $_POST['start'];
        }
        if ($new > 0) {
            $url .= '&new='.$new;
        }
        if ($updated > 0) {
            $url .= '&updated='.$updated;
        }
        if ($deleted > 0) {
            $url .= '&deleted='.$deleted;
        }
        if ($updated == 0 && $new == 0 && 0 == $deleted) {
            $url .= '&nochange';
        }
        header('Location: '.$url);
    }
}