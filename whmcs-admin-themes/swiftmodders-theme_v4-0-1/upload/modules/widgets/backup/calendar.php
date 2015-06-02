<?php

if (!defined("WHMCS"))
    die("This file cannot be accessed directly");

function widget_calendar($vars) {
    global $whmcs,$_ADMINLANG;

    if ($whmcs->get_req_var('getcalendarevents')) {
        $day = $whmcs->get_req_var('day');
        if (!$day) $day = date("d");
        echo '<div class="title">';
        if ($day==date("d")) echo 'Today, '.date("jS F Y",mktime(0,0,0,date("m"),$day,date("Y")));
        elseif ($day==date("d")-1) echo 'Yesterday, '.date("jS F Y",mktime(0,0,0,date("m"),$day,date("Y")));
        elseif ($day==date("d")+1) echo 'Tomorrow, '.date("jS F Y",mktime(0,0,0,date("m"),$day,date("Y")));
        else echo date("l, jS F Y",mktime(0,0,0,date("m"),$day,date("Y")));
        echo '</div>';
        $numproducts = get_query_val("tblhosting","COUNT(id)","domainstatus IN ('Active','Suspended') AND nextduedate='".date("Y-m-").(int)$day."'");
        $numaddons = get_query_val("tblhostingaddons","COUNT(id)","status IN ('Active','Suspended') AND nextduedate='".date("Y-m-").(int)$day."'");
        $numdomains = get_query_val("tbldomains","COUNT(id)","status IN ('Active') AND nextduedate='".date("Y-m-").(int)$day."'");
        $numtodoitems = get_query_val("tbltodolist","COUNT(id)","duedate='".date("Y-m-d",mktime(0,0,0,date("m"),$day,date("Y")))."'");
        $numevents = get_query_val("tblcalendar","COUNT(id)","start>='".mktime(0,0,0,date("m"),$day,date("Y"))."' AND start<'".mktime(0,0,0,date("m"),$day+1,date("Y"))."'");
        if ($numproducts==0 && $numaddons==0 && $numdomains==0 && $numtodoitems==0 && $numevents==0) echo '<div>No Events Scheduled</div>';
        else echo '<div>'.$numproducts.' Products/Services Due to Renew</div><div>'.$numaddons.' Addons Due to Renew</div><div>'.$numdomains.' Domains Due to Renew</div><div>'.$numtodoitems.' To-Do Items Due</div><div>'.$numevents.' Events Scheduled</div>';
        echo '<div style="padding-top:10px;"><a href="calendar.php"><img src="images/icons/add.png" align="top" /> Add New Event</a></div>';
        exit;
    }

    $jscode = 'function loadCalEvents(day) {
    $.post("index.php", { getcalendarevents: 1, day: day },
    function(data){
        jQuery("#eventslist").html(data);
    });
}';

    $jquerycode = 'loadCalEvents();';

    $title = "Calendar";

    $content = '<style>
div#calendarwidget div.calendar { float: left; width: 265px; padding: 0 0 0 20px; }
div#calendarwidget div.eventslist { background-color: #fff; padding: 15px 0 0 0; height: 145px; overflow: auto; }
div#calendarwidget div.eventslist div.title { padding: 0 0 12px 0; font-family: Sans-Serif; font-size: 18px; }
div#calendarwidget table.calendar    { width: 240px; border-left:1px solid #ccc; }
div#calendarwidget td.calendar-day  { height:25px; font-size:11px; position:relative; }
div#calendarwidget div.calendar-day { height:25px; }
div#calendarwidget td.calendar-day:hover  { background:#eceff5; }
div#calendarwidget td.calendar-day-np  { background:#eee; height:25px; color: #fff; }
div#calendarwidget div.calendar-day-np { height:25px; }
div#calendarwidget td.calendar-day-head { background:#eee; font-weight:bold; text-align:center; width:14%; padding:5px; border-bottom:1px solid #ccc; border-top:1px solid #ccc; border-right:1px solid #ccc; }
div#calendarwidget td.calendar-day-today { background:#2165CB; }
div#calendarwidget td.calendar-day-today a { color: #fff; }
div#calendarwidget td.calendar-day-events { background:#D9F273; }
div#calendarwidget a.day-number    { display: block; color: #000; font-size: 13px; padding:0; float:right; margin: 0; width:100%; text-align:center; }
div#calendarwidget td.calendar-day, td.calendar-day-np { width:14%; padding:0; border-bottom:1px solid #ccc; border-right:1px solid #ccc; }
</style>
';

$headings = array('S','M','T','W','T','F','S');

$calendar = '
<div id="calendarwidget">
    <div class="calendar">
        <table cellpadding="0" cellspacing="0" class="calendar">
            <tr class="calendar-row"><td class="calendar-day-head">'.implode('</td><td class="calendar-day-head">',$headings).'</td></tr>
';

$month = date("m");
$year = date("Y");
$running_day = date('w',mktime(0,0,0,$month,1,$year));
$days_in_month = date('t',mktime(0,0,0,$month,1,$year));
$days_in_this_week = 1;
$day_counter = 0;
$dates_array = array();

$calendar.= '<tr class="calendar-row">';

for($x = 0; $x < $running_day; $x++){
    $calendar.= '<td class="calendar-day-np">&nbsp;</td>';
    $days_in_this_week++;
}

for($list_day = 1; $list_day <= $days_in_month; $list_day++){
    $events = false;
    $calendar.= '<td class="calendar-day'.(($list_day==date("d"))?' calendar-day-today':(($events)?' calendar-day-events':'')).'"><a href="#" class="day-number" onclick="loadCalEvents(\''.$list_day.'\');return false">'.$list_day.'</a></td>';
    if($running_day == 6){
      $calendar.= '</tr>';
      if(($day_counter+1) != $days_in_month){
        $calendar.= '<tr class="calendar-row">';
      }
      $running_day = -1;
      $days_in_this_week = 0;
    }
    $days_in_this_week++; $running_day++; $day_counter++;
}

  if($days_in_this_week < 8):
    for($x = 1; $x <= (8 - $days_in_this_week); $x++):
      $calendar.= '<td class="calendar-day-np">&nbsp;</td>';
    endfor;
  endif;

$calendar.= '</tr>
        </table>
    </div>
    <div class="eventslist" id="eventslist"></div>
</div>';

    return array('title'=>$title,'content'=>$content.$calendar,'jscode'=>$jscode,'jquerycode'=>$jquerycode);

}

add_hook("AdminHomeWidgets",1,"widget_calendar");

?>