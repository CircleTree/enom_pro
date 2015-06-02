<?php

if (!defined("WHMCS"))
    die("This file cannot be accessed directly");

function widget_client_activity($vars) {
    global $_ADMINLANG;

    $title = $_ADMINLANG['home']['recentclientactivity'];

    $content = '<table width="75%" bgcolor="#cccccc" cellspacing="1" align="center">
<tr bgcolor="#efefef" style="text-align:center;font-weight:bold;"><td>'.$_ADMINLANG['fields']['client'].'</td><td>'.$_ADMINLANG['fields']['ipaddress'].'</td><td>'.$_ADMINLANG['system']['lastaccess'].'</td></tr>';

    $id = '';
    $result = select_query("tblclients","id,firstname,lastname,ip,lastlogin","","lastlogin","DESC","0,5");
    while ($data = mysql_fetch_array($result)) {
        $id = $data['id'];
        $content .= '<tr bgcolor="#ffffff" style="text-align:center;"><td><a href="clientssummary.php?userid='.$id.'">'.$data["firstname"].' '.$data["lastname"].'</a></td><td><a href="http://www.geoiptool.com/en/?IP='.$data["ip"].'" target="_blank">'.$data["ip"].'</a></td><td>'.fromMySQLDate($data["lastlogin"],true).'</td></tr>';
    }
    if (!$id) $content .= '<tr bgcolor="#ffffff" style="text-align:center;"><td colspan="3">'.$_ADMINLANG['global']['norecordsfound'].'</td></tr>';

    $content .= '</table>';

    return array('title'=>$title,'content'=>$content);

}

add_hook("AdminHomeWidgets",1,"widget_client_activity");

?>