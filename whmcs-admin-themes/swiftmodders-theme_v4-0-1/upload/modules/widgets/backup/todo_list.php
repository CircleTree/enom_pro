<?php

if (!defined("WHMCS"))
    die("This file cannot be accessed directly");

function widget_todo_list($vars) {
    global $_ADMINLANG;

    $dateField = isset($_ADMINLANG['fields']['date']) ? $_ADMINLANG['fields']['date'] : '';
    $titleField = isset($_ADMINLANG['fields']['title']) ? $_ADMINLANG['fields']['title'] : '';
    $descriptionField = isset($_ADMINLANG['fields']['description']) ? $_ADMINLANG['fields']['description'] : '';
    $duedateField = isset($_ADMINLANG['fields']['duedate']) ? $_ADMINLANG['fields']['duedate'] : '';
    $statusField = isset($_ADMINLANG['fields']['status']) ? $_ADMINLANG['fields']['status'] : '';
    $noRecordsFoundGlobal = isset($_ADMINLANG['global']['norecordsfound']) ? $_ADMINLANG['global']['norecordsfound'] : '';

    $content = '<table width="100%" bgcolor="#cccccc" cellspacing="1">
<tr bgcolor=#efefef style="text-align:center;font-weight:bold;"><td>' . $dateField . '</td><td>' . $titleField . '/' . $descriptionField . '</td><td>' . $duedateField . '</td><td>' . $statusField . '</td><td width="20"></td></tr>
    ';

    $id = '';
    $result = select_query("tbltodolist","",array("status"=>array("sqltype"=>"NEQ","value"=>"Completed")),"duedate","ASC");
    while ($data = mysql_fetch_array($result)) {
        $id = $data["id"];
        $date = $data["date"];
        $title = $data["title"];
        $description = $data["description"];
        $admin = $data["admin"];
        $status = $data["status"];
        $duedate = $data["duedate"];
        $date = fromMySQLDate($date);
        $duedate = ($duedate == "0000-00-00") ? '-' : fromMySQLDate($duedate);
        $bgcolor = ($admin == $vars['adminid']) ? "#f5f5d7" : "#ffffff";
        $description = (strlen($description)>50) ? substr($description,0,50).'...' : $description;
        $content .= '<tr bgcolor="'.$bgcolor.'" style="text-align:center;"><td>'.$date.'</td><td>'.$title.' - '.$description.'</td><td>'.$duedate.'</td><td>'.$status.'</td><td><a href="todolist.php?action=edit&id='.$id.'"><img src="images/edit.gif" border="0"></a></td></tr>
';
    }
    if (!$id) $content .= '<tr bgcolor="#ffffff"><td colspan="5" align="center">' . $noRecordsFoundGlobal . '</td></tr>';

    $content .= '</table>
<div align="right" style="padding-top:5px;"><a href="todolist.php">'.$_ADMINLANG['home']['manage'].' &raquo;</a></div>';

    $title = $_ADMINLANG['todolist']['todolisttitle'];

    return array('title'=>$title,'content'=>$content);

}

add_hook("AdminHomeWidgets",1,"widget_todo_list");
