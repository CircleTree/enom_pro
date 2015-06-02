<?php

if (!defined("WHMCS"))
    die("This file cannot be accessed directly");

function widget_my_notes($vars) {

    $title = "My Notes";

    $mynotes = get_query_val("tbladmins","notes",array("id"=>$vars['adminid']));

    $content = '
<script>
function widgetnotessave() {
    $.post("index.php", { action: "savenotes", notes: $("#widgetnotesbox").val(), token: "'.generate_token('plain').'" });
    $("#widgetnotesconfirm").slideDown().delay(2000).slideUp();
}
</script>
<div align="center">
<div id="widgetnotesconfirm" style="display:none;margin:0 0 5px 0;padding:5px 20px;background-color:#DBF3BA;font-weight:bold;color:#6A942C;">Notes Saved Successfully!</div>
<textarea id="widgetnotesbox" style="width:95%;height:100px;">'.$mynotes.'</textarea>
<input type="button" value="Save Notes" onclick="widgetnotessave()" />
</div>
    ';

    return array('title'=>$title,'content'=>$content);

}

add_hook("AdminHomeWidgets",1,"widget_my_notes");

?>