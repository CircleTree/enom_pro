<?php

if (!defined("WHMCS"))
    die("This file cannot be accessed directly");

function widget_income_overview($vars) {
    global $_ADMINLANG,$chart;

    $title = $_ADMINLANG['home']['incomeoverview'];

    $args = array();
    $args['colors'] = '#F9D88C,#1E78BB';
    $args['legendpos'] = 'top';
    $args['xlabel'] = 'Day of the Month';
    $args['ylabel'] = 'Default Currency';
    $args['chartarea'] = '80,40,85%,70%';

    $content = $chart->drawChart('Area',chartdata_income(),$args,'300px');

    return array('title'=>$title,'content'=>$content);

}

function chartdata_income() {
    global $currency;
    $currency = getCurrency();
    $chartdata = array();
    $chartdata['cols'][] = array('label'=>'Day','type'=>'string');
    $chartdata['cols'][] = array('label'=>'Income','type'=>'number');
    $chartdata['cols'][] = array('label'=>'Expenditure/Refunds','type'=>'number');
    for ($i = 14; $i >= 0; $i--) {
        $date = mktime(0,0,0,date("m"),date("d")-$i,date("Y"));
        $data = get_query_vals("tblaccounts","SUM(amountin/rate),SUM(amountout/rate)","date LIKE '".date("Y-m-d",$date)."%'");
        if (!$data[0]) $data[0]=0;
        if (!$data[1]) $data[1]=0;
        $chartdata['rows'][] = array('c'=>array(array('v'=>date("dS",$date)),array('v'=>(int)$data[0],'f'=>formatCurrency($data[0])),array('v'=>(int)$data[1],'f'=>formatCurrency($data[1]))));
    }
    return $chartdata;
}

add_hook("AdminHomeWidgets",1,"widget_income_overview");

?>