<?php

if (!defined("WHMCS"))
    die("This file cannot be accessed directly");

function widget_supporttickets_overview($vars) {
    global $chart;

    $title = "Support Tickets Overview";

    $activestatuses = $replystatuses = array();
    $result = select_query("tblticketstatuses","title,showactive,showawaiting","showactive=1");
    while ($data = mysql_fetch_array($result)) {
        if ($data['showactive']) $activestatuses[] = $data['title'];
        if ($data['showawaiting']) $replystatuses[] = $data['title'];
    }

    $ticketcount = 0;

    $awaitingReplyByDept = array();

    if (count($replystatuses) > 0) {
        $query = "SELECT name,(SELECT COUNT(*) FROM tbltickets WHERE tbltickets.did=tblticketdepartments.id AND tbltickets.status IN (".db_build_in_array($replystatuses).")) FROM tblticketdepartments ORDER BY `order` ASC";
        $result = full_query($query);
        while ($data = mysql_fetch_array($result)) {
            $awaitingReplyByDept[] = array(
                'c' => array(
                    array(
                        'v' => addcslashes(WHMCS_Input_Sanitize::decode($data[0]), '"'),
                    ),
                    array(
                        'v' => $data[1],
                        'f' => $data[1],
                    ),
                ),
            );
            $ticketcount += $data[1];
        }
    }

    $awaitingReplyByStatus = array();

    $query = "SELECT tblticketstatuses.title,(SELECT COUNT(*) FROM tbltickets WHERE tbltickets.status=tblticketstatuses.title) FROM tblticketstatuses WHERE showawaiting=1 ORDER BY sortorder ASC";
    $result = full_query($query);
    while ($data = mysql_fetch_array($result)) {
        $awaitingReplyByStatus[] = array(
            'c' => array(
                array(
                    'v' => addcslashes(WHMCS_Input_Sanitize::decode($data[0]), '"'),
                ),
                array(
                    'v' => $data[1],
                    'f' => $data[1],
                ),
            ),
        );
        $ticketcount += $data[1];
    }

    if (!$ticketcount) {
        $content = <<<EOT
<br />
<div align="center">
    There are <strong>0</strong> Tickets Currently Awaiting a Reply
</div>
<br />
EOT;
    } else {

        // Awaiting Reply by Department
        $chartData = array(
            'cols' => array(
                array(
                    'label' => 'Department',
                    'type' => 'string',
                ),
                array(
                    'label' => 'Ticket Count',
                    'type' => 'number',
                ),
            ),
            'rows' => $awaitingReplyByDept,
        );
        $content = '<div id="ticketOverviewDepartments">'
            . $chart->drawChart(
                'Pie',
                $chartData,
                array(
                    'title' => 'Awaiting Reply by Department',
                    'legendpos' => 'right',
                ),
                '250px'
            )
            . '</div>';

        // Awaiting Reply by Status
        $chartData = array(
            'cols' => array(
                array(
                    'label' => 'Status',
                    'type' => 'string',
                ),
                array(
                    'label' => 'Ticket Count',
                    'type' => 'number',
                ),
            ),
            'rows' => $awaitingReplyByStatus,
        );
        $content .= '<div id="ticketOverviewStatuses">'
            . $chart->drawChart(
                'Pie',
                $chartData,
                array(
                    'title' => 'Awaiting Reply by Status',
                    'legendpos' => 'right',
                ),
                '250px'
            )
            . '</div>';

    }

    return array(
        'title' => $title,
        'content' => $content,
    );

}

add_hook("AdminHomeWidgets", 1, "widget_supporttickets_overview");
