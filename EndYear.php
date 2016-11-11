<?php

/* $Id: CustomerReceipt.php 3868 2010-09-30 14:53:59Z tim_schofield $ */
/* $Revision: 1.46 $ */
ob_start();
$PageSecurity = 2;
include('includes/session.inc');

$title = _('Update Class Grade Level');

include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
require('grades/EndYearReportCardClass.php');
require('grades/LecturerSubjectClass2.php');
require('grades/ReportCardClass2.php');
$msg='';

$sql = "SELECT year FROM years
		WHERE id='". $_POST['academic_year']."'";
		$result = DB_query($sql,$db);		
		$myrow = DB_fetch_array($result);
		$year_current=$myrow['year'];
		
if ($_POST['open']==_('Open Period'))
{
$sql = "UPDATE collegeperiods SET 		 
year_status=0
WHERE year = '".$_POST['academic_year']."'";
$open_year = DB_query($sql,$db);
prnMsg(_('The academic year') . ' ' . $year_current . ' ' . _('has been opened'),'success');
exit("Academic year succesfully opened...");	
	}
	
	
	
if ($_POST['Close']==_('Close Period'))
{

$sql = "SELECT year_status
		FROM collegeperiods
		WHERE year='". $_POST['academic_year']."'";
		$result = DB_query($sql,$db);		
		$myrow = DB_fetch_array($result);
		$Status=$myrow['year_status'];
if ($Status==1) {
   exit("Academic Year has already been closed...");
}
else{
$sql = "UPDATE collegeperiods SET 		 
year_status=1
WHERE year = '".$_POST['academic_year']."'";
$close_year = DB_query($sql,$db);
prnMsg(_('The academic year') . ' ' . $year_current . ' ' . _('has been closed'),'success');
	exit("Academic year succesfully closed...");
}
}



	
if ($_POST['unroll']==_('UnRole Year'))
{
$sql = "SELECT SUM(run) FROM years
WHERE year > $year_current";
$result = DB_query($sql,$db);		
$myrow = DB_fetch_row($result);
$later=$myrow[0];
if ($later > 0) {
   exit("A later academic year has already been compiled");
}

$sql2 = "SELECT run FROM years
		WHERE id='".$_POST['academic_year']."'";
		$result2 = DB_query($sql2,$db);
		$myrow2 = DB_fetch_array($result2);
		$run=$myrow2['run'];
if($run==1){
$sql = "UPDATE years SET run=0
		WHERE id='".$_POST['academic_year']."'";
		$query = DB_query($sql,$db);
}	
$sql = "SELECT year_status
		FROM collegeperiods
		WHERE year='". $_POST['academic_year']."'";
		$result = DB_query($sql,$db);		
		$myrow = DB_fetch_array($result);
		$Status=$myrow['year_status'];
if ($Status==1) {
   exit("This academic year is closed.");
} else {
		//$current_year=date('Y');
		$sqlclass = "SELECT dm.id,dm.class_id FROM debtorsmaster dm
		INNER JOIN annual_ranks an ON an.student_id=dm.id
		AND an.academic_year_id='".$_POST['academic_year']."'
		AND dm.status=0
		AND an.rolled=1 AND an.unrolled=0";
		$resultclass = DB_query($sqlclass,$db);	
while ($myrowclass= DB_fetch_array($resultclass))
{

	$sql = "SELECT previous_stream FROM classes
		WHERE id='".$myrowclass['class_id']."'";
		$result=DB_query($sql,$db);
		$myrow=DB_fetch_array($result);
		$previous_stream=$myrow['previous_stream'];
		
	$sql = "SELECT grade_level_id FROM classes
		WHERE id='".$previous_stream."'";
		$result=DB_query($sql,$db);
		$myrow=DB_fetch_array($result);
		$previous_class=$myrow['grade_level_id'];		
			
	$sql = "UPDATE debtorsmaster SET 		 
		class_id='$previous_stream',
		grade_level_id='$previous_class',
		age=age-1
		WHERE id = '".$myrowclass['id']."'";
		$query = DB_query($sql,$db);
			
		$sql = "UPDATE annual_ranks SET 		 
		unrolled='1',rolled='0'
		WHERE student_id = '".$myrowclass['id']."'
		AND academic_year_id='" . $_POST['academic_year']."'";
		$query = DB_query($sql,$db);
		
		
					}//end of while
	 prnMsg(_('The academic year') . ' ' . $year_current . ' ' . _('has been Un rolled'),'success'); 
	exit("Students succesfully updated...");				
				}	
			}	

	
if ($_POST['generate']==_('Role Year'))
{
$sql2 = "SELECT approved FROM years
		WHERE id='".$_POST['academic_year']."'";
		$result2 = DB_query($sql2,$db);
		$myrow2 = DB_fetch_array($result2);
		$approved=$myrow2['approved'];
if($approved==1){
$sql2 = "SELECT run FROM years
		WHERE id='".$_POST['academic_year']."'";
		$result2 = DB_query($sql2,$db);
		$myrow2 = DB_fetch_array($result2);
		$run=$myrow2['run'];
if($run==1){
 prnMsg(_('This academic year has already been compiled, Unroll first'),'warn'); 
exit("");
}		
$sql = "SELECT year_status
		FROM collegeperiods
		WHERE year='". $_POST['academic_year']."'";
		$result = DB_query($sql,$db);		
		$myrow = DB_fetch_array($result);
		$Status=$myrow['year_status'];
if ($Status==1) {
   exit("This academic year is closed.");
} 
$sql="DELETE FROM annual_ranks WHERE academic_year_id ='" . $_POST['academic_year'] . "'";
$Postdelptrans= DB_query($sql,$db);

		//$current_year=date('Y');
		$sqlclass = "SELECT dm.id,rs.class_id,rs.yos FROM debtorsmaster dm
		INNER JOIN registered_students rs ON rs.student_id=dm.id
		WHERE rs.academic_year_id='".$_POST['academic_year']."'
		AND dm.status=0 
		GROUP BY rs.student_id";
		$resultclass = DB_query($sqlclass,$db);	
while ($myrowclass= DB_fetch_array($resultclass))
{
if($myrowclass['yos'] !=10){
	$sql = "SELECT next_stream FROM classes
		WHERE id='".$myrowclass['class_id']."'";
		$result=DB_query($sql,$db);
		$myrow=DB_fetch_array($result);
		$next_stream=$myrow['next_stream'];
		
	$sql = "SELECT grade_level_id FROM classes
		WHERE id='".$next_stream."'";
		$result=DB_query($sql,$db);
		$myrow=DB_fetch_array($result);
		$next_class=$myrow['grade_level_id'];		
	
			
	$sql = "UPDATE debtorsmaster SET 		 
		class_id='$next_stream',
		grade_level_id='$next_class',
		age=age+1
		WHERE id = '".$myrowclass['id']."'";
		$grade_level = DB_query($sql,$db);
	}	
	elseif($myrowclass['yos'] ==10){
	$sql = "UPDATE debtorsmaster SET 		 
		status=1
		WHERE id = '".$myrowclass['id']."'";
		$query = DB_query($sql,$db);
	}
	
	$sql = "INSERT INTO annual_ranks (class_id,academic_year_id,student_id)
	VALUES ('" . $myrowclass['class_id'] ."','" . $_POST['academic_year'] ."','" . $myrowclass['id']."')";
	$ErrMsg = _('Ranking failed');
	$result = DB_query($sql,$db,$ErrMsg);	
	
		$sql = "UPDATE annual_ranks SET rolled='1',		 
		unrolled='0'
		WHERE student_id = '".$myrowclass['id']."'
		AND academic_year_id='" . $_POST['academic_year']."'";
		$query = DB_query($sql,$db);
			
}//end of while
$sql = "UPDATE collegeperiods SET 		 
year_status=1
WHERE year = '".$_POST['academic_year']."'";
$next_year = DB_query($sql,$db);
  
$sql = "UPDATE years SET run=1
WHERE id = '".$_POST['academic_year']."'";
$query = DB_query($sql,$db); 
  
  
 prnMsg(_('The academic year') . ' ' . $year_current . ' ' . _('has been compiled'),'success'); 
	exit("Academic Year's data succesfully updated...");
							}//end of else
					
else{
 prnMsg(_('The Principal has not yet approved compiling this academic year'),'warn');
	}					
}// end of generate data
echo "<form method='post' action=" . $_SERVER['PHP_SELF'] . '>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<table border="1">';

	echo '<tr><td>' . _('Academic Year') . ': </td><td><select tabindex="5" name="academic_year">';
$result = DB_query('SELECT id,year FROM years',$db);
while ($myrow = DB_fetch_array($result)) {
	if ($myrow['id']==$_POST['academic_year']) {
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
$_SESSION['period'] = $_POST['academic_year'];

echo "<TABLE BORDER=2><TR><td><INPUT TYPE=SUBMIT NAME='Close' VALUE='" . _('Close Period') . "'><INPUT TYPE=SUBMIT NAME='open' VALUE='" . _('Open Period') . "'><INPUT TYPE=SUBMIT NAME='generate' VALUE='" . _('Role Year') . "'><INPUT TYPE=SUBMIT NAME='unroll' VALUE='" . _('UnRole Year') . "'></td></tr></table></BR>";

$sql="SELECT year FROM years
	WHERE id='".$_SESSION['period'] ."'";
	$result=DB_query($sql,$db);
	$myrow = DB_fetch_array($result);
	$year=$myrow['year'];
?>
<table width="640" border="0" cellspacing="0" cellpadding="0">
  <tr> 
    <td height="50" > 
	
      <table width="90%" border="1" cellspacing="0" cellpadding="0" align="center" bordercolordark="#CCCCCC" bordercolorlight="#CCCCCC" bgcolor="#F2F2F2">
        <tr bgcolor="#F4F4F4"> 
          <td height="30" width="26%"> 
            <div align="right"><font face="Verdana, Arial, Helvetica, sans-serif" size="-1">Academic Year
              :</font></div>
          </td>
          <td height="30" width="74%"><font face="Verdana, Arial, Helvetica, sans-serif" size="-1" color="#000066"><b><?php echo $year; ?></b></font></td> </tr>
      </table>
    </td>
  </tr>

</table>
<?php	
}
include('includes/footer.inc');
?>
