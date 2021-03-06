<?php
  $title="Staff - Manage Participants";
  require_once('db_functions.php');
  require_once('StaffHeader.php');
  require_once('StaffFooter.php');
  require_once('StaffCommonCode.php');
  staff_header($title);
?>

<p>
On this page you will find the online tools for managing Participants.</p>
<hr>
<DL>
   <DT id="managebios"><!-- <A HREF="StaffManageBios.php"> --><SPAN style="text-decoration: line-through">Manage biographies.</SPAN><!--</A> --></DT>
   <DD id="managebios">Manage and edit participants' biographies.</DD>
<?php if(may_I('create_participant')) { ?>
   <DT><A HREF="StaffEditCreateParticipants.php?action=create">Enter Participants</A></DT> 
   <DD>Manually create new participants in the database and enter their data</DD>
<?php } ?>
   <DT><A HREF="AdminParticipants.php">Administer participants</A></DT>
   <DD>Use this tool to modify a participant's "interested" flag, change his password, or delete him from all sessions.</DD>
   <DT><A HREF="InviteParticipants.php">Invite a participant to a session</A></DT>
   <DD>Use this tool to put sessions marked "invited guests only" on a participant's interest list.</DD>
   <DT><A HREF=" StaffAssignParticipants.php">Assign participants to a session</A></DT>
   <DD>Use this tool to assign participants to a session and select moderator.</DD>
   <DT><A HREF="MaintainRoomSched.php">Maintain room schedule</A></DT>
   <DD>Assign sessions at particular times in a room.</DD>
   <DT><A HREF="StaffMaintainSchedule.php">New Scheduler</A></DT>
   <DD>Edit the schedule in a variety of ways.</DD>
<?php if(may_I("SendEmail")) { ?>
   <DT><A HREF="StaffSendEmailCompose.php">Send Email to Participants</A></DT>
   <DD>Select a set of participants and send them a form letter.</DD>
<?php } ?>
</DL>

<?php staff_footer(); ?>
