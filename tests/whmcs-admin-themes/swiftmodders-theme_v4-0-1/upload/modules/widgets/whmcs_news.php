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
        echo '<div class="right"><a href="https://twitter.com/whmcs" class="twitter-follow-button" data-show-count="false">Follow @whmcs</a>
<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?"http":"https";if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+"://platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document, "script", "twitter-wjs");</script></div>';
        $i=0;
        foreach ($feed AS $news) {
            echo '<p><strong>'.(($news['link'])?'<a href="'.$news['link'].'" target="_blank">':'').$news['headline'].(($news['link'])?'</a>':'').'</strong>
<br />'.$news['text'].'</p>
<small>'.date("l, F jS, Y",strtotime($news['date'])).'</small><hr />';
            $i++;
        }
        exit;
    }

    $content = '<div id="whmcsnewsfeed">'.$vars['loading'].'</div>';

    $jquerycode = '$.post("index.php", { getwhmcsnews: 1 },
    function(data){
        jQuery("#whmcsnewsfeed").html(data);
    });';

    return array('title'=>$title,'content'=>$content,'jquerycode'=>$jquerycode);

}

add_hook("AdminHomeWidgets",1,"widget_whmcs_news");

?>