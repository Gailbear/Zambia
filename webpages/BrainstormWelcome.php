<?php
//	Copyright (c) 2011-2017 Peter Olszowka. All rights reserved. See copyright document for more details.
    global $participant, $message_error, $message2, $congoinfo, $title;
    $title="Brainstorm View";
    require_once ('BrainstormCommonCode.php'); 
    if (retrieve_participant_from_db($badgeid)==0) {
        require ('renderBrainstormWelcome.php');
        exit();
        }
    $message_error=$message2."<BR>Error retrieving data from DB.  No further execution possible.";
    RenderError($message_error);
    exit();
?>
