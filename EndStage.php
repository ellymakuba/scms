<?php

/* $Id: CustomerReceipt.php 3868 2010-09-30 14:53:59Z tim_schofield $ */
/* $Revision: 1.46 $ */
ob_start();
$PageSecurity = 2;
include('includes/session.inc');

$title = _('Update Class Stage');

include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
$msg='';
if ($_POST['Close']==_('Close Period'))
{
$sql = "SELECT stage_status
		FROM collegeperiods
		WHERE year='".$_SESSION['year']."'";
		$result = DB_query($sql,$db);		
		$myrow = DB_fetch_array($result);
		$Status=$myrow['stage_status'];
if ($Status==1) {
   exit("Current Class stage has already been changed this year...");
} else {  
			$sql = "SELECT * FROM classes
				WHERE id='".$_SESSION['class']."'";
				$result = DB_query($sql,$db);
				$myrow= DB_fetch_array($result);
				$stage=$myrow['stage'];
				if($stage<4){ 
				$sql = "UPDATE classes SET stage=stage+1
				WHERE id = '".$_SESSION['class']."'";
				$close_stage = DB_query($sql,$db);					
						
				}
		
		$sql = "UPDATE collegeperiods SET
		stage_status=1
		WHERE id = '".$_SESSION['period']."'";
		$ErrMsg = _('The record could not be updated because');
		$DbgMsg = _('The SQL that was used to update  was');
		$result = DB_query($sql, $db, $ErrMsg, $DbgMsg);
		prnMsg(_('update succesful') ,'success');
	exit("Stage succesfully updated...");
}
}
echo "<form method='post' action=" . $_SERVER['PHP_SELF'] . '>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<table border="1">';
	
echo '<tr><td>' . _('Period') . ":</td>
		<td><select name='period'>";
		echo '<OPTION SELECTED VALUE=0>' . _('Select Period');
		$sql="SELECT cp.id,terms.title,years.year FROM collegeperiods cp
		INNER JOIN terms ON terms.id=cp.term_id
		INNER JOIN years ON years.id=cp.year ";
		$result=DB_query($sql,$db);
		while ($myrow = DB_fetch_array($result)) {
		echo '<option value='. $myrow['id'].  '>'.' '.$myrow['title'].' '.$myrow['year'];
		} //end while loop
		DB_data_seek($result,0);
		echo '</select></td></tr>';

	DB_data_seek($result,0);
	echo '</select></td></tr>';
echo '<tr><td>' . _('Class') . ":</td>
		<td><select name='student_class'>";
		echo '<OPTION SELECTED VALUE=0>' . _('Select Class');
		$sql="SELECT cl.id,cl.class_name,c.course_name,gl.grade_level FROM classes cl 
		INNER JOIN courses c ON c.id=cl.course_id
		INNER JOIN gradelevels gl ON gl.id=cl.grade_level_id";
		$result=DB_query($sql,$db);
		while ($myrow = DB_fetch_array($result)) {
	echo '<option value='. $myrow['id'] . '>' . $myrow['class_name']._('-').$myrow['grade_level']._('-').$myrow['course_name'];
		} //end while loop
		DB_data_seek($result,0);
	echo '</select></td></tr>
<tr><td>' . _('Year') . ': </td><td><select tabindex="5" name="year">';
$result = DB_query('SELECT id,year FROM years',$db);
while ($myrow = DB_fetch_array($result)) {
	if ($myrow['id']==$_POST['year']) {
		echo '<option selected VALUE=';
	} else {
		echo '<option VALUE=';
	}
	echo $myrow['id'] . '>' . $myrow['year'];
} //end while loop

echo '</select></td></tr></table>';

		echo '<table border="1">';
echo "<br><div class='centre'><input  type='Submit' name='submit' value='" . _('Submit') . "'>&nbsp;<input  type=submit action=RESET VALUE='" . _('Reset') . "'></div>";	

if (isset($_POST['submit'])) {
$_SESSION['class'] = $_POST['student_class'];
$_SESSION['period'] = $_POST['period'];
$_SESSION['year'] = $_POST['year'];

echo "<TABLE BORDER=2><TR><td><INPUT TYPE=SUBMIT NAME='Close' VALUE='" . _('Close Period') . "'><INPUT TYPE=SUBMIT NAME='open' VALUE='" . _('Open Period') . "'></td></tr></table></BR>";

$sql2="SELECT t.title,cp.* FROM terms t
	INNER JOIN collegeperiods cp ON cp.term_id=t.id
	WHERE cp.id='".$_SESSION['period'] ."'";
	$result2=DB_query($sql2,$db);
	$myrow2 = DB_fetch_array($result2);
	$term_name=$myrow2['title'];
$sql="SELECT cl.*,m.month_name FROM classes cl
	INNER JOIN months m ON m.id=cl.month_id
	WHERE cl.id='".$_SESSION['class'] ."'";
	$result=DB_query($sql,$db);
	$myrow = DB_fetch_array($result);
?>
<table width="640" border="0" cellspacing="0" cellpadding="0">
  <tr> 
    <td height="180" valign="top"> 
	
      <table width="90%" border="1" cellspacing="0" cellpadding="0" align="center" bordercolordark="#CCCCCC" bordercolorlight="#CCCCCC" bgcolor="#F2F2F2">
        <tr bgcolor="#F4F4F4"> 
          <td height="30" width="26%"> 
            <div align="right"><font face="Verdana, Arial, Helvetica, sans-serif" size="-1">Month
              :</font></div>
          </td>
          <td height="30" width="74%"><font face="Verdana, Arial, Helvetica, sans-serif" size="-1" color="#000066"><b><?php echo $myrow['month_name']; ?></b></font></td>
        </tr>
        <tr bgcolor="#F4F4F4"> 
          <td height="30" width="26%"> 
            <div align="right"><font face="Verdana, Arial, Helvetica, sans-serif" size="-1">Term 
              :</font></div>
          </td>
          <td height="30" width="74%"><font face="Verdana, Arial, Helvetica, sans-serif" size="-1" color="#000066"><b><?php echo $term_name; ?></b></font></td>
        </tr>
        <tr bgcolor="#F4F4F4"> 
          <td height="30" width="26%"> 
            <div align="right"><font face="Verdana, Arial, Helvetica, sans-serif" size="-1">Start Date 
              :</font></div>
          </td>
          <td height="30" width="74%"><font face="Verdana, Arial, Helvetica, sans-serif" size="-1" color="#000066"><b><?php echo $myrow2['start_date'] ?></a></b></font></td>
        </tr>
        <tr bgcolor="#F4F4F4"> 
          <td height="30" width="26%"> 
            <div align="right"><font face="Verdana, Arial, Helvetica, sans-serif" size="-1">End Date 
              :</font></div>
          </td>
          <td height="30" width="74%"><font face="Verdana, Arial, Helvetica, sans-serif" size="-1" color="#000066"><b><?php echo $myrow2['end_date']; ?></a></b></font></td>
        </tr>
        <tr bgcolor="#F4F4F4"> 
          <td height="30" width="26%"> 
            <div align="right"><font face="Verdana, Arial, Helvetica, sans-serif" size="-1">Academic Year 
              :</font></div>
          </td>
          <td height="30" width="74%"><font face="Verdana, Arial, Helvetica, sans-serif" size="-1" color="#000066"><b><?php echo $myrow2['year']; ?></a></b></font></td>
        </tr>
        
        <tr bgcolor="#F4F4F4"> 
          <td height="30" width="26%"> 
            <div align="right"><font face="Verdana, Arial, Helvetica, sans-serif" size="-1">Period Status
              :</font></div>
          </td>
          <td height="30" width="74%" bgcolor="#F4F4F4"><font face="Verdana, Arial, Helvetica, sans-serif" size="-1"><b><font color="#000066"><?php echo $myrow2['status']; ?></font></b></font></td>
        </tr>
      </table>
	  
    </td>
  </tr>

</table>
<?php	
}
include('includes/footer.inc');
?>
