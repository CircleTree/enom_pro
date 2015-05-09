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
        $content .= $entry['description'].'<br /><small>'.$entry['date'].' - '.$entry['username'].' - '.$entry['ipaddress'].'</small><hr />';
    }

    if (!$content) $content = '<div class="text-center">No Activity Recorded Yet</div>';
    else $content .= '<div class="text-right"><a class="button small" href="systemactivitylog.php">'.$_ADMINLANG['home']['viewall'].' &raquo;</a></div>';

    return array('title'=>$title,'content'=>$content);

}

add_hook("AdminHomeWidgets",1,"widget_activity_log");