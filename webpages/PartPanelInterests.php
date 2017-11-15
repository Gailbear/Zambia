<?php
// Copyright (c) 2011-2017 Peter Olszowka. All rights reserved. See copyright document for more details.
	//This file should be requested from menu link only -- it doesn't process any posts
    require ('PartCommonCode.php'); // initialize db; check login; set $badgeid
    require ('PartPanelInterests_FNC.php');
    require ('PartPanelInterests_Render.php');
    global $session_interests,$session_interest_index, $title;
    $title="Panel Interests";
    if (!may_I('my_panel_interests')) {
        $message="You do not currently have permission to view this page.<br />\n";
        RenderError($message);
        exit();
        }
    // Get the participant's interest data -- use global $session_interests
    $session_interest_count=get_session_interests_from_db($badgeid); // Returns count; Will render its own errors
    // Get title, etc. of such data -- use global $session_interests
    get_si_session_info_from_db($session_interest_count); // Will render its own errors
    $message="";
    $message_error="";
	$pageIsDirty = False;
    render_session_interests($badgid, $session_interest_count, $message, $message_error, $pageIsDirty); // includes footer
?>
