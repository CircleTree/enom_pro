<?php

if (!defined("WHMCS"))
    die("This file cannot be accessed directly");

function widget_whmcs_news($vars) {
    global $whmcs,$_ADMINLANG;

    $title = $_ADMINLANG['home']['whmcsnewsfeed'];

    if ($whmcs->get_req_var('getwhmcsnews')) {
        if (!function_exists("ticketAutoHyperlinks")) require(ROOTDIR.'/includes/ticketfunctions.php');
        $feed = curlCall('http://www.whmcs.com/feeds/news.php','');
        $feed = json_decode($feed,1);
        echo '<div style="float:right;margin:15px 15px 10px 10px;padding:8px 20px;text-align:center;background-color:#FDF8E1;border:1px dashed #FADA5A;-moz-border-radius: 5px;-webkit-border-radius: 5px;-o-border-radius: 5px;border-radius: 5px;">Follow Us<br /><a href="http://twitter.com/whmcs" target="_blank" style="font-size:16px;color:#D9AE06;">@whmcs</a></div>';
        $i=0;
        foreach ($feed AS $news) {
            echo '<div style="padding-top:5px;font-size:14px;'.(($i==0)?'border-top:1px dashed #ccc;':'').'">'.(($news['link'])?'<a href="'.$news['link'].'" target="_blank">':'').$news['headline'].(($news['link'])?'</a>':'').'</div>
<div style="padding:5px;">'.$news['text'].'</div>
<div style="font-size:10px;font-weight:bold;padding-bottom:5px;border-bottom:1px dashed #ccc;">'.date("l, F jS, Y",strtotime($news['date'])).'</div>
';
            $i++;
        }
        exit;
    }

    $content = '<div id="whmcsnewsfeed" style="max-height:130px;">'.$vars['loading'].'</div>';

    $jquerycode = '$.post("index.php", { getwhmcsnews: 1 },
    function(data){
        jQuery("#whmcsnewsfeed").html(data);
    });';

    return array('title'=>$title,'content'=>$content,'jquerycode'=>$jquerycode);

}

add_hook("AdminHomeWidgets",1,"widget_whmcs_news");

?>