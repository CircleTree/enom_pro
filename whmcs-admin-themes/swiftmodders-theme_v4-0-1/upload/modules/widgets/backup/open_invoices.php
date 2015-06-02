<?php

if (!defined("WHMCS"))
    die("This file cannot be accessed directly");

function widget_open_invoices($vars) {
    global $_ADMINLANG,$currency;

    $title = $_ADMINLANG['home']['openinvoices'];

    if (!function_exists("getGatewaysArray")) require(ROOTDIR."/includes/gatewayfunctions.php");
    $gatewaysarray = getGatewaysArray();

    $content = '<table width="100%" bgcolor="#cccccc" cellspacing="1">
<tr style="background-color:#efefef;font-weight:bold;text-align:center"><td>'.$_ADMINLANG['fields']['invoicenum'].'</td><td>'.$_ADMINLANG['fields']['clientname'].'</td><td>'.$_ADMINLANG['fields']['invoicedate'].'</td><td>'.$_ADMINLANG['fields']['duedate'].'</td><td>'.$_ADMINLANG['fields']['totaldue'].'</td><td>'.$_ADMINLANG['fields']['paymentmethod'].'</td><td width="20"></td></tr>
';

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
        $content .= '<tr bgcolor="#ffffff" style="text-align:center;"><td><a href="invoices.php?action=edit&id='.$id.'">'.$invoicenum.'</a></td><td>'.$firstname.' '.$lastname.'</td><td>'.$date.'</td><td>'.$duedate.'</td><td>'.formatCurrency($total).'</td><td>'.$paymentmethod.'</td><td><a href="invoices.php?action=edit&id='.$id.'"><img src="images/edit.gif" border="0" /></a></td></tr>';
    }
    if (!$id) $content .= '<tr bgcolor="#ffffff" style="text-align:center;"><td colspan="7">'.$_ADMINLANG['global']['norecordsfound'].'</td></tr>';

    $content .= '</table>
<div align="right" style="padding-top:5px;"><a href="invoices.php?status=Unpaid">'.$_ADMINLANG['home']['viewall'].' &raquo;</a></div>';

    return array('title'=>$title,'content'=>$content);

}

add_hook("AdminHomeWidgets",1,"widget_open_invoices");

?>