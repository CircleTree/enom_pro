<?php
require_once 'init.php';
$ca = new WHMCS_clientarea();
$ca->setPageTitle('SRV Records');
$ca->initPage();
$ca->requireLogin();
require_once ROOTDIR . '/modules/addons/enom_pro/enom_pro.php';
if (isset($_REQUEST['action'])) {
    switch ($_REQUEST['action']) {
        case 'save_srv':
            $domain = mysql_fetch_assoc(mysql_query('SELECT * from `tbldomains` WHERE `id`= '.(int) $_REQUEST['id'].' AND `userid` = '.$_SESSION['uid']));
            $enom = new enom_pro();
            $enom->setDomain($domain['domain']);

            $records = $enom->get_SRV_records();
            if (isset($_REQUEST['records'])) {
                foreach ($_REQUEST['records'] as $index => $record) {
                    foreach ($record as $k => $v) {
                        //Hostid is not empty, allow edits
                        if ($k == 'hostid' && trim($v) != "") {
                            break;
                        }
                        //Don't allow empty
                        if (trim($v) == "" && $k != 'hostid') {
                            //Except hostid, which will be empty on new
                            unset($_REQUEST['records'][$index]);
                            break;
                        }
                    }
                }
                /*
                */
                if (! empty($_REQUEST['records']) ) {
                    $enom->set_SRV_Records($_REQUEST['records']);
                    echo '<pre>';
                    print_r($enom);
                    echo '</pre>';
                    if ($enom->is_error()) {
                        header("HTTP/1.0 400 Bad Request");
                        echo 'Error: ' . implode(', ', $enom->get_errors_array());
                        die;
                    }
                    $records = $enom->get_SRV_records();
                }
            }
            header('Content-type: application/json');
            echo json_encode($records);
        break;
    }
    die;
}

if (! isset($_GET['id'])) {
    $query = 'SELECT * from `tbldomains` WHERE `registrar` = \'enom\'  AND `userid` = ' . $_SESSION['uid'];
    $mysql_result = mysql_query($query);
    $results_array = array();
    while ($result = mysql_fetch_assoc($mysql_result)) {
        $results_array[ $result['id'] ] = $result;
    }
    $ca->assign('domains', $results_array);
} else {
    $domain = mysql_fetch_assoc(mysql_query('SELECT * from `tbldomains` WHERE `id`= '.(int) $_REQUEST['id'].' AND `userid` = '.$_SESSION['uid']));
    $ca->assign('domain', $domain);
}
$ca->assign('id', (isset($_GET['id']) ? $_GET['id'] : false)  );
$number_of_rows = 5;
$records_array = array_keys(array_fill(1, $number_of_rows, 0));
$ca->assign('records', $records_array);
$ca->setTemplate('enom_srv');
$ca->output();
