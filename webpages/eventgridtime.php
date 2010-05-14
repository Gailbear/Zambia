<?php
    require_once('db_functions.php');
    require_once('StaffHeader.php');
    require_once('StaffFooter.php');
    require_once('StaffCommonCode.php');
    global $link;
    $ConStartDatim=CON_START_DATIM; // make it a variable so it can be substituted

    ## LOCALIZATIONS
    $_SESSION['return_to_page']="eventgridtimereport.php";
    $title="Published Time Filled Event Grid";
    $description="<P>Display published event schedule with rooms on horizontal axis and regular time on vertical. This excludes any item marked \"Do Not Print\" or \"Staff Only\".</P>\n";
    $additionalinfo="<P>Click on the room name to edit the room's schedule;\n";
    $additionalinfo.="the session id to edit the session's participants; or\n";
    $additionalinfo.="the title to edit the session.</P>\n";
    $indicies="PROGWANTS=1, GRIDSWANTS=1";
    $Grid_Spacer=GRID_SPACER;

    /* This query returns the room names for an array. */
    $query = <<<EOD
SELECT
        R.roomname,
        R.roomid
    FROM
            Rooms R
    WHERE
        R.function like '%vent%' AND
        R.roomid in
        (SELECT DISTINCT roomid FROM Schedule)
    ORDER BY
        R.display_order;
EOD;

    /* Standard test for failing to connect to the database. */
    if (($result=mysql_query($query,$link))===false) {
        $message="Error retrieving data from database.<BR>";
        $message.=$query;
        $message.="<BR>";
        $message.= mysql_error();
        RenderError($title,$message);
        exit ();
        }

    /* Standard test to make sure there was some information returned. */
    if (0==($rooms=mysql_num_rows($result))) {
        $message="<P>This report retrieved no results matching the criteria.</P>\n";
        RenderError($title,$message);
        exit();
        }

    /* Associate the information with header_array. */
    for ($i=1; $i<=$rooms; $i++) {
        $header_array[$i]=mysql_fetch_assoc($result);
        }
    $header_cells="<TR><TH>Time</TH>";
    for ($i=1; $i<=$rooms; $i++) {
        $header_cells.="<TH>";
        $header_cells.=sprintf("<A HREF=\"MaintainRoomSched.php?selroom=%s\">%s</A>",$header_array[$i]["roomid"],$header_array[$i]["roomname"]);
        $header_cells.="</TH>";
        }
    $header_cells.="</TR>";

    /* This query finds the first second that is actually scheduled
       so we don't waste grid-space. */
    $query="SELECT TIME_TO_SEC(starttime) as 'beginschedule' FROM Schedule ORDER BY starttime ASC LIMIT 0,1";
    if (($result=mysql_query($query,$link))===false) {
        $message="Error retrieving data from database.<BR>";
        $message.=$query;
        $message.="<BR>";
        $message.= mysql_error();
        RenderError($title,$message);
        exit ();
        }
    if (0==($earliest=mysql_num_rows($result))) {
        $message="<P>This report retrieved no results matching the criteria.</P>\n";
        RenderError($title,$message);
        exit();
        }
    $grid_start_sec=mysql_result($result,0);

    $query="SELECT (TIME_TO_SEC(SCH.starttime) + TIME_TO_SEC(S.duration)) as 'endschedule' FROM Schedule SCH JOIN Sessions S USING (sessionid) ORDER BY endschedule DESC LIMIT 0,1";
    if (($result=mysql_query($query,$link))===false) {
        $message="Error retrieving data from database.<BR>";
        $message.=$query;
        $message.="<BR>";
        $message.= mysql_error();
        RenderError($title,$message);
        exit ();
        }
    if (0==($latest=mysql_num_rows($result))) {
        $message="<P>This report retrieved no results matching the criteria.</P>\n";
        RenderError($title,$message);
        exit();
        }
    $grid_end_sec=mysql_result($result,0);

    /* This complex set of queries fills in the header_cells and then
       puts the times, associated with each room along the row
       by stepping along in time intervals */
    for ($time=$grid_start_sec; $time<=$grid_end_sec; $time = $time + $Grid_Spacer) {
        $query="SELECT DATE_FORMAT(ADDTIME('$ConStartDatim',SEC_TO_TIME('$time')),'%a %l:%i %p') as 'blocktime'";
        for ($i=1; $i<=$rooms; $i++) {
            $x=$header_array[$i]["roomid"];
            $y=$header_array[$i]["roomname"];
            $query.=sprintf(",GROUP_CONCAT(IF(roomid=%s,S.title,\"\") SEPARATOR '') as \"%s title\"",$x,$y);
            $query.=sprintf(",GROUP_CONCAT(IF(roomid=%s,S.sessionid,\"\") SEPARATOR '') as \"%s sessionid\"",$x,$y);
            $query.=sprintf(",GROUP_CONCAT(IF(roomid=%s,S.duration,\"\") SEPARATOR '') as \"%s duration\"",$x,$y);
            }
        $query.=" FROM Schedule SCH JOIN Sessions S USING (sessionid)";
        $query.=" JOIN Rooms R USING (roomid)";
        $query.=" WHERE S.pubstatusid = 2 AND R.function like '%vent%' AND";
        $query.=" TIME_TO_SEC(SCH.starttime) <= $time";
        $query.=" AND (TIME_TO_SEC(SCH.starttime) + TIME_TO_SEC(S.duration)) >= ($time + $Grid_Spacer);";
        if (($result=mysql_query($query,$link))===false) {
            $message="Error retrieving data from database.<BR>";
            $message.=$query;
            $message.="<BR>";
            $message.= mysql_error();
            RenderError($title,$message);
            exit ();
            }
        if (0==($rows=mysql_num_rows($result))) {
            $message="<P>This report retrieved no results matching the criteria.</P>\n";
            RenderError($title,$message);
            exit();
            }
        $grid_array[$time]=mysql_fetch_array($result,MYSQL_BOTH);
        $skiprow=0;
        for ($i=1; $i<=$rooms; $i++) {
            $j=$header_array[$i]['roomname'];
            if ($grid_array[$time]["$j sessionid"]!="") {$skiprow++;}
            }
        if ($skiprow == 0) {$grid_array[$time]['blocktime'] = "Skip";}
        }

    /* Printing body.  Uses the page-init from above adds informational line
       then creates the grid.  skipinit kills the rogue extrat /TABLE and
       skipaccum allows for only one new tabel per set of skips.  */
    topofpagereport($title,$description,$additionalinfo);
    $skipinit=0;
    $skipaccum=1;
    for ($i = $grid_start_sec; $i < $grid_end_sec; $i = ($i + $Grid_Spacer)) {
        if ($skipaccum == 1) { 
            if ($skipinit != 0) {echo "</TABLE>\n";} else {$skipinit++;}
            echo "<TABLE BORDER=1>";
            echo $header_cells;
            }
        if ($grid_array[$i]['blocktime'] == "Skip") {
            $skipaccum++;
            } else {
            echo "<TR><TD>";
            echo $grid_array[$i]['blocktime'];
            echo "</TD>";
            for ($j=1; $j<=$rooms; $j++) {
                $z=$header_array[$j]['roomname'];
                $x=$grid_array[$i]["$z sessionid"]; //sessionid
                if ($x!="") {
                    echo "<TD>";
                    echo sprintf("(<A HREF=\"StaffAssignParticipants.php?selsess=%s\">%s</A>) ",$x,$x);
                    $y = $grid_array[$i]["$z title"]; //title
                    echo sprintf("<A HREF=\"EditSession.php?id=%s\">%s</A>",$x,$y);
                    $y = substr($grid_array[$i]["$z duration"],0,-3); // duration; drop ":00" representing seconds off the end
                    if (substr($y,0,1)=="0") {$y = substr($y,1,999);} // drop leading "0"
                    echo sprintf(" (%s)",$y);
                    }
                else
                    { echo "<TD>&nbsp;"; } 
                echo "</TD>";
                }
                echo "</TR>\n";
                $skipaccum=0;
            }
        }
    echo "</TABLE>";
    staff_footer();