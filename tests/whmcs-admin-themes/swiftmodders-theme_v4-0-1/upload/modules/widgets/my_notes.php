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
<div id="widgetnotesconfirm" data-alert class="alert-box success" style="display:none;">Notes Saved Successfully!</div>
<div class="text-center">
<textarea id="widgetnotesbox">'.$mynotes.'</textarea>
<input class="button small" type="button" value="Save Notes" onclick="widgetnotessave()" />
</div>
    ';

    return array('title'=>$title,'content'=>$content);

}

add_hook("AdminHomeWidgets",1,"widget_my_notes");

?>