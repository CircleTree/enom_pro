<?php

if (!defined("WHMCS"))
    die("This file cannot be accessed directly");

function widget_getting_started($vars) {

    $title = "Getting Started with WHMCS";

    $content = '<h3>Welcome to WHMCS - The Complete Client Management, Billing & Support Solution!</h3>
<p>Here\'s our handy tips for getting up & running if this is your first time using WHMCS...</p>
<blockquote>
<strong>Step 1:</strong> Review & Configure <a href="configgeneral.php">General System Settings</a> including Company Name, URL, etc...<br />
<strong>Step 2:</strong> Activate & Configure <a href="configgateways.php">Payment Methods</a> you want to accept<br />
<strong>Step 3:</strong> Setup at least 1 <a href="configproducts.php">Product Group</a> & <a href="configproducts.php">Product/Service</a> in your system (<a href="http://docs.whmcs.com/Setting_Up_Your_First_Product" target="_blank">More Help</a>)<br />
</blockquote>
<p>For more information please refer to our documentation @ <a href="http://docs.whmcs.com/" target="_blank">http://docs.whmcs.com/</a> for lots of useful information.</p>
<div class="text-right"><input class="button small" type="submit" value="Dismiss Getting Started Guide" onclick="dismissgs()" /></div>
    ';

    $jscode = 'function dismissgs() {
    $("#getting_started").fadeOut();
    $.post("index.php", { dismissgs: 1 });
}';

    return array('title'=>$title,'content'=>$content,'jscode'=>$jscode);

}

add_hook("AdminHomeWidgets",1,"widget_getting_started");

?>