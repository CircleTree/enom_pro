<?php

if (!defined("WHMCS"))
    die("This file cannot be accessed directly");

function widget_admin_activity($vars) {
    global $_ADMINLANG;

    $title = $_ADMINLANG['home']['recentadminactivity'];

    $content = '<table width="75%" bgcolor="#cccccc" cellspacing="1" align="center">
<tr bgcolor="#efefef" style="text-align:center;font-weight:bold;"><td>'.$_ADMINLANG['fields']['admin'].'</td><td>'.$_ADMINLANG['fields']['ipaddress'].'</td><td>'.$_ADMINLANG['system']['lastaccess'].'</td></tr>';
    $result = select_query("tbladminlog","","","lastvisit","DESC","0,5");
    while ($data = mysql_fetch_array($result)) {
        $content .= '<tr bgcolor="#ffffff" style="text-align:center;"><td>'.$data["adminusername"].'</a></td><td><a href="http://www.geoiptool.com/en/?IP='.$data["ipaddress"].'" target="_blank">'.$data["ipaddress"].'</a></td><td>'.fromMySQLDate($data["lastvisit"],true).'</td></tr>';
    }
    $content .= '</table>';

    return array('title'=>$title,'content'=>$content);

}

add_hook("AdminHomeWidgets",1,"widget_admin_activity");

?>