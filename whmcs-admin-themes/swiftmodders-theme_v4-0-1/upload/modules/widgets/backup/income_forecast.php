<?php

if (!defined("WHMCS"))
    die("This file cannot be accessed directly");

function widget_income_forecast($vars) {
    global $whmcs,$_ADMINLANG,$currency,$currencytotal,$data;

    $title = $_ADMINLANG['home']['incomeforecast'];

function ah_formatstat($billingcycle,$stat) {
    global $data,$currency,$currencytotal;
    $value = array_key_exists($billingcycle,$data) ? $data[$billingcycle][$stat] : '';
    if (!$value) $value = 0;
    if ($stat=="sum") {
        if ($billingcycle=="Monthly") {
            $currencytotal += $value*12;
        } elseif ($billingcycle=="Quarterly") {
            $currencytotal += $value*4;
        } elseif ($billingcycle=="Semi-Annually") {
            $currencytotal += $value*2;
        } elseif ($billingcycle=="Annually") {
            $currencytotal += $value;
        } elseif ($billingcycle=="Biennially") {
            $currencytotal += $value/2;
        } elseif ($billingcycle=="Triennially") {
            $currencytotal += $value/3;
        }
        $value = formatCurrency($value);
    }
    return $value;
}
$incomestats = array();
$result = select_query("tblhosting,tblclients", "currency,billingcycle,COUNT(*),SUM(amount)", "tblclients.id = tblhosting.userid AND (domainstatus = 'Active' OR domainstatus = 'Suspended') GROUP BY currency, billingcycle");
while ($data = mysql_fetch_array($result)) {
    $incomestats[$data['currency']][$data['billingcycle']]["count"] = $data[2];
    $incomestats[$data['currency']][$data['billingcycle']]["sum"] = $data[3];
}
$result = select_query("tblhostingaddons,tblhosting,tblclients", "currency,tblhostingaddons.billingcycle,COUNT(*),SUM(recurring)", "tblhostingaddons.hostingid=tblhosting.id AND tblclients.id=tblhosting.userid AND (tblhostingaddons.status='Active' OR tblhostingaddons.status='Suspended') GROUP BY currency, tblhostingaddons.billingcycle");
while ($data = mysql_fetch_array($result)) {
    if (isset($incomestats[$data['currency']][$data['billingcycle']]["count"])) $incomestats[$data['currency']][$data['billingcycle']]["count"] += $data[2];
    else $incomestats[$data['currency']][$data['billingcycle']]["count"] = $data[2];
    if (isset($incomestats[$data['currency']][$data['billingcycle']]["sum"])) $incomestats[$data['currency']][$data['billingcycle']]["sum"] += $data[3];
    else $incomestats[$data['currency']][$data['billingcycle']]["sum"] = $data[3];
}
$result = select_query("tbldomains,tblclients", "currency,COUNT(*),SUM(recurringamount/registrationperiod)", "tblclients.id=tbldomains.userid AND tbldomains.status='Active' GROUP BY currency");
while ($data = mysql_fetch_array($result)) {
    if (isset($incomestats[$data['currency']]["Annually"]["count"])) $incomestats[$data['currency']]["Annually"]["count"] += $data[1];
    else $incomestats[$data['currency']]["Annually"]["count"] = $data[1];
    if (isset($incomestats[$data['currency']]["Annually"]["sum"])) $incomestats[$data['currency']]["Annually"]["sum"] += $data[2];
    else $incomestats[$data['currency']]["Annually"]["sum"] = $data[2];
}

$content = '';
if (count($incomestats)) {
    foreach ($incomestats AS $currency=>$data) {
        $currency = getCurrency("",$currency);
        $currencytotal = 0;
        $content .= "<div style=\"float:left;margin:10px 0 10px 0;".((count($incomestats)>1)?'width:50%;':'width:100%;')."text-align:center;\"><span class=\"textred\"><b>{$currency['code']} ".$_ADMINLANG['currencies']['currency']."</b></span><br />
    ".$_ADMINLANG['billingcycles']['monthly'].": ".ah_formatstat('Monthly','sum')." (".ah_formatstat('Monthly','count').")<br />
    ".$_ADMINLANG['billingcycles']['quarterly'].": ".ah_formatstat('Quarterly','sum')." (".ah_formatstat('Quarterly','count').")<br />
    ".$_ADMINLANG['billingcycles']['semiannually'].": ".ah_formatstat('Semi-Annually','sum')." (".ah_formatstat('Semi-Annually','count').")<br />
    ".$_ADMINLANG['billingcycles']['annually'].": ".ah_formatstat('Annually','sum')." (".ah_formatstat('Annually','count').")<br />
    ".$_ADMINLANG['billingcycles']['biennially'].": ".ah_formatstat('Biennially','sum')." (".ah_formatstat('Biennially','count').")<br />
    ".$_ADMINLANG['billingcycles']['triennially'].": ".ah_formatstat('Triennially','sum')." (".ah_formatstat('Triennially','count').")<br />
    <span class=\"textgreen\"><b>".$_ADMINLANG['billing']['annualestimate'].": ".formatCurrency($currencytotal)."</b></span></div>";
    }
} else {
    $content = '<div align="center">No Active or Suspended Products/Services Found to build Forecast</div>';
}

    $content = '<div id="incomeforecast">'.$content.'</div>';

    return array('title'=>$title,'content'=>$content);

}

add_hook("AdminHomeWidgets",1,"widget_income_forecast");

?>