<?php

if (!defined("WHMCS"))
    die("This file cannot be accessed directly");

function widget_client_activity($vars) {
    global $_ADMINLANG;

    $title = $_ADMINLANG['home']['recentclientactivity'];

    $content = '<table class="datatable" width="100%">
<thead><tr><th>'.$_ADMINLANG['fields']['client'].'</th><th>'.$_ADMINLANG['fields']['ipaddress'].'</th><th>'.$_ADMINLANG['system']['lastaccess'].'</th></tr></thead><tbody>';

    $id = '';
    $result = select_query("tblclients","id,firstname,lastname,ip,lastlogin","","lastlogin","DESC","0,5");
    while ($data = mysql_fetch_array($result)) {
        $id = $data['id'];
        $content .= '<tr><td><a href="clientssummary.php?userid='.$id.'">'.$data["firstname"].' '.$data["lastname"].'</a></td><td><a href="http://www.geoiptool.com/en/?IP='.$data["ip"].'" target="_blank">'.$data["ip"].'</a></td><td>'.fromMySQLDate($data["lastlogin"],true).'</td></tr>';
    }
    if (!$id) $content .= '<tr><td colspan="3">'.$_ADMINLANG['global']['norecordsfound'].'</td></tr>';

    $content .= '</tbody></table>';

    return array('title'=>$title,'content'=>$content);

}

add_hook("AdminHomeWidgets",1,"widget_client_activity");

?>