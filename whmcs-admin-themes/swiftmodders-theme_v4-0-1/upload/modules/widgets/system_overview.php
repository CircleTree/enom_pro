<?php

if (!defined("WHMCS"))
    die("This file cannot be accessed directly");

function widget_system_overview($vars) {
    global $whmcs,$_ADMINLANG;

    $title = $_ADMINLANG['home']['sysoverview'];

    if ($whmcs->get_req_var('getsystemoverview')) {

        $activeclients = get_query_val("tblclients","COUNT(id)","status='Active'");
        $totalclients = get_query_val("tblclients","COUNT(id)","");
        $clientsactive = ($activeclients==0 || $totalclients==0) ? '0' : round((($activeclients/$totalclients)*100),0);

        $activeservices = get_query_val("tblhosting","COUNT(id)","domainstatus='Active'");
        $totalservices = get_query_val("tblhosting","COUNT(id)","");
        $servicesactive = ($activeservices==0 || $totalservices==0) ? '0' : round((($activeservices/$totalservices)*100),0);

        $unpaidinvoices = get_query_val("tblinvoices","COUNT(id)","status='Unpaid'");
        $overdueinvoices = get_query_val("tblinvoices","COUNT(id)","status='Unpaid' AND duedate<'".date("Ymd")."'");
        $overduestatus = ($overdueinvoices==0 || $unpaidinvoices==0) ? '0' : round((($overdueinvoices/$unpaidinvoices)*100),0);

        echo '<div class="row">
		  <div class="small-12 large-4 columns"><strong>Clients</strong></div>
		  <div class="small-8 large-6 columns"><div class="percentbar"><div class="active" style="width:'.$clientsactive.'%">'.$clientsactive.'% Active</div></div></div>
		  <div class="small-4 large-2 columns text-center">'.$totalclients.'</div>
		</div>
		<div class="row">
		  <div class="small-12 large-4 columns"><strong>Services</strong></div>
		  <div class="small-8 large-6 columns"><div class="percentbar"><div class="active" style="width:'.$servicesactive.'%">'.$servicesactive.'% Active</div></div></div>
		  <div class="small-4 large-2 columns text-center">'.$totalservices.'</div>
		</div>
		<div class="row">
		  <div class="small-12 large-4 columns"><strong>Unpaid Invoices</strong></div>
		  <div class="small-8 large-6 columns"><div class="percentbar"><div class="overdue" style="width:'.$overduestatus.'%">'.$overduestatus.'% Overdue</div></div></div>
		  <div class="small-4 large-2 columns text-center">'.$unpaidinvoices.'</div>
		</div>';
        exit;

    }

    $adminusername = get_query_val("tbladmins","username",array("id"=>$vars['adminid']));
    $lastlogin = get_query_vals("tbladminlog","lastvisit,ipaddress",array("adminusername"=>$adminusername),"lastvisit","DESC","1,1");
    $lastlogindate = ($lastlogin[0]) ? fromMySQLDate($lastlogin[0],true) : '(None Recorded)';
    $lastloginip = ($lastlogin[1]) ? $lastlogin[1] : '-';

    $content = '<div id="systemoverviewstats">'.$vars['loading'].'</div>

<div class="lastlogin">'.$_ADMINLANG['home']['lastlogin'].': <strong>'.$lastlogindate.'</strong> '.$_ADMINLANG['home']['lastloginip'].' <strong>'.$lastloginip.'</strong></div>

';

$statusfilter = array();
$result = select_query("tblticketstatuses","title",array("showawaiting"=>"1"));
while ($data = mysql_fetch_array($result)) {
    $statusfilter[] = $data[0];
}

if (count($statusfilter) > 0) {
    $result = full_query("SELECT COUNT(*) FROM tbltickets WHERE status IN (".db_build_in_array($statusfilter).")");
    $data = mysql_fetch_array($result);
    $ticketsawaitingreply = $data[0];
} else {
    $ticketsawaitingreply = 0;
}

$result = full_query("SELECT COUNT(*) FROM tblcancelrequests INNER JOIN tblhosting ON tblhosting.id=tblcancelrequests.relid WHERE (tblhosting.domainstatus!='Cancelled' AND tblhosting.domainstatus!='Terminated')");
$data = mysql_fetch_array($result);
$cancellationrequests = $data[0];
$result = full_query("SELECT COUNT(*) FROM tbltodolist WHERE status!='Completed' AND status!='Postponed' AND duedate<='".date("Y-m-d")."'");
$data = mysql_fetch_array($result);
$todoitemsdue = $data[0];
$result = full_query("SELECT COUNT(*) FROM tblnetworkissues WHERE status!='Scheduled' AND status!='Resolved'");
$data = mysql_fetch_array($result);
$opennetworkissues = $data[0];

    $jquerycode = 'jQuery.post("index.php", { getsystemoverview: 1 },
    function(data){
        jQuery("#systemoverviewstats").html(data);
        jQuery("#systemoverviewstats").slideDown();
        jQuery("#sysoverviewbanner").html("'.$_ADMINLANG['global']['attentionitems'].': &nbsp; <a href=\"supporttickets.php\">'.$ticketsawaitingreply.' '.$_ADMINLANG['stats']['ticketsawaitingreply'].'</a> &nbsp;-&nbsp; <a href=\"cancelrequests.php\">'.$cancellationrequests.' '.$_ADMINLANG['stats']['pendingcancellations'].'</a> &nbsp;-&nbsp; <a href=\"todolist.php\">'.$todoitemsdue.' '.$_ADMINLANG['stats']['todoitemsdue'].'</a> &nbsp;-&nbsp; <a href=\"networkissues.php\">'.$opennetworkissues.' '.$_ADMINLANG['stats']['opennetworkissues'].'</a>");
});';

    return array('title'=>$title,'content'=>$content,'jquerycode'=>$jquerycode);

}

function widget_system_overview_home_banner() {
    $roleid = WHMCS_Admin::getRoleID();
    $widgets = get_query_val("tbladminroles","widgets",array("id"=>$roleid));
    $widgets = explode(',',$widgets);
    $banner = (in_array('system_overview',$widgets)) ? '<div id="sysoverviewbanner"><i class="fa fa-circle-o-notch fa-spin"></i> Loading...</div>' : '';
    return $banner;
}

add_hook("AdminHomeWidgets",1,"widget_system_overview");
add_hook("AdminHomepage",1,"widget_system_overview_home_banner");