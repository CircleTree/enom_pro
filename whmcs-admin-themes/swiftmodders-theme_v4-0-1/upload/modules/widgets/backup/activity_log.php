<?php

if (!defined("WHMCS"))
    die("This file cannot be accessed directly");

function widget_activity_log($vars) {
    global $_ADMINLANG;

    $title = $_ADMINLANG['utilities']['activitylog'];

    $content = '';

    $log = new WHMCS_Log_Activity();

    $logs = $log->getLogEntries(0, 10);
    foreach ($logs AS $entry) {
        $content .= $entry['description'].'<br /><span style="font-size:11px;">&nbsp; - '.$entry['date'].' - '.$entry['username'].' - '.$entry['ipaddress'].'</span><br />';
    }

    if (!$content) $content = '<div align="center">No Activity Recorded Yet</div>';
    else $content .= '<div align="right"><a href="systemactivitylog.php">'.$_ADMINLANG['home']['viewall'].' &raquo;</a></div>';

    return array('title'=>$title,'content'=>$content);

}

add_hook("AdminHomeWidgets",1,"widget_activity_log");