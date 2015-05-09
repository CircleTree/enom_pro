<?php

if (!defined("WHMCS"))
    die("This file cannot be accessed directly");

function widget_open_invoices($vars) {
    global $_ADMINLANG,$currency;

    $title = $_ADMINLANG['home']['openinvoices'];

    if (!function_exists("getGatewaysArray")) require(ROOTDIR."/includes/gatewayfunctions.php");
    $gatewaysarray = getGatewaysArray();

    $content = '<table class="datatable" width="100%"><thead>
<tr><th>'.$_ADMINLANG['fields']['invoicenum'].'</th><th>'.$_ADMINLANG['fields']['clientname'].'</th><th>'.$_ADMINLANG['fields']['invoicedate'].'</th><th>'.$_ADMINLANG['fields']['duedate'].'</th><th>'.$_ADMINLANG['fields']['totaldue'].'</th><th>'.$_ADMINLANG['fields']['paymentmethod'].'</th><th width="20"></th></tr></thead><tbody>';

    $id = '';
    $query = "SELECT tblinvoices.*,tblinvoices.total-COALESCE((SELECT SUM(amountin) FROM tblaccounts WHERE tblaccounts.invoiceid=tblinvoices.id),0) AS invoicebalance,tblclients.firstname,tblclients.lastname FROM tblinvoices INNER JOIN tblclients ON tblclients.id=tblinvoices.userid WHERE tblinvoices.status='Unpaid' ORDER BY duedate,date ASC LIMIT 0,5";
    $result = full_query($query);
    while ($data = mysql_fetch_array($result)) {
        $id = $data["id"];
        $invoicenum = $data["invoicenum"];
        $userid = $data["userid"];
        $firstname = $data["firstname"];
        $lastname = $data["lastname"];
        $date = $data["date"];
        $duedate = $data["duedate"];
        $total = $data["total"];
        $invoicebalance = $data["invoicebalance"];
        $paymentmethod = $data["paymentmethod"];
        $paymentmethod = $gatewaysarray[$paymentmethod];
        $date = fromMySQLDate($date);
        $duedate = fromMySQLDate($duedate);
        $currency = getCurrency($userid);
        if (!$invoicenum) $invoicenum = $id;
        $content .= '<tr><td><a href="invoices.php?action=edit&id='.$id.'">'.$invoicenum.'</a></td><td>'.$firstname.' '.$lastname.'</td><td>'.$date.'</td><td>'.$duedate.'</td><td>'.formatCurrency($total).'</td><td>'.$paymentmethod.'</td><td><a class="button secondary tiny" href="invoices.php?action=edit&id='.$id.'"><i class="fa fa-pencil-square-o"></i></a></td></tr>';
    }
    if (!$id) $content .= '<tr><td colspan="7">'.$_ADMINLANG['global']['norecordsfound'].'</td></tr>';

    $content .= '</tbody></table>
<div class="text-right"><a class="button small" href="invoices.php?status=Unpaid">'.$_ADMINLANG['home']['viewall'].' &raquo;</a></div>';

    return array('title'=>$title,'content'=>$content);

}

add_hook("AdminHomeWidgets",1,"widget_open_invoices");

?>