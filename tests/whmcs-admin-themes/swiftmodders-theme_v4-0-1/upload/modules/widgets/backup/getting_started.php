<?php

if (!defined("WHMCS"))
    die("This file cannot be accessed directly");

function widget_getting_started($vars) {

    $title = "Getting Started with WHMCS";

    $content = '
<span style="font-weight:bold;font-size:14px;color:#29467C;">Welcome to WHMCS - The Complete Client Management, Billing & Support Solution!</span><br />
Here\'s our handy tips for getting up & running if this is your first time using WHMCS...<br />
<blockquote>
<b>Step 1:</b> Review & Configure <a href="configgeneral.php">General System Settings</a> including Company Name, URL, etc...<br />
<b>Step 2:</b> Activate & Configure <a href="configgateways.php">Payment Methods</a> you want to accept<br />
<b>Step 3:</b> Setup at least 1 <a href="configproducts.php">Product Group</a> & <a href="configproducts.php">Product/Service</a> in your system (<a href="http://docs.whmcs.com/Setting_Up_Your_First_Product">More Help</a>)<br />
</blockquote>
For more information please refer to our documentation @ <a href="http://docs.whmcs.com/" target="_blank">http://docs.whmcs.com/</a> for lots of useful information.
<div align="right" style="padding-top:5px;"><input type="submit" value="Dismiss Getting Started Guide" onclick="dismissgs()" /></div>
    ';

    $jscode = 'function dismissgs() {
    $("#getting_started").fadeOut();
    $.post("index.php", { dismissgs: 1 });
}';

    return array('title'=>$title,'content'=>$content,'jscode'=>$jscode);

}

add_hook("AdminHomeWidgets",1,"widget_getting_started");

?>