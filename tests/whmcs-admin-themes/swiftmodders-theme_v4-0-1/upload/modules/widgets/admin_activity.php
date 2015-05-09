<?php

if (!defined("WHMCS"))
    die("This file cannot be accessed directly");

function widget_admin_activity($vars) {
    global $_ADMINLANG;

    $title = $_ADMINLANG['home']['recentadminactivity'];

    $content = '<table class="datatable" width="100%"><thead><tr><th>'.$_ADMINLANG['fields']['admin'].'</th><th>'.$_ADMINLANG['fields']['ipaddress'].'</th><th>'.$_ADMINLANG['system']['lastaccess'].'</th></tr></thead><tbody>';
    $result = select_query("tbladminlog","","","lastvisit","DESC","0,5");
    while ($data = mysql_fetch_array($result)) {
        $content .= '<tr><td>'.$data["adminusername"].'</a></td><td><a href="http://www.geoiptool.com/en/?IP='.$data["ipaddress"].'" target="_blank">'.$data["ipaddress"].'</a></td><td>'.fromMySQLDate($data["lastvisit"],true).'</td></tr>';
    }
    $content .= '</tbody></table>';

    return array('title'=>$title,'content'=>$content);

}

add_hook("AdminHomeWidgets",1,"widget_admin_activity");

?>