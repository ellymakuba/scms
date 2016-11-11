<?php

$PageSecurity = 2;
include('includes/session.inc');

if(isset($_POST['generate'])){
require('grades/EndYearReportCardClass.php');
require('grades/TranscriptAsterikClass.php');
include("Numbers/Words.php");
include('includes/header.inc');

$sql="DELETE FROM annual_ranks WHERE academic_year_id ='" . $_POST['period_id'] . "'
AND rolled=0";
$Postdelptrans= DB_query($sql,$db);


$sqlclass = "SELECT * FROM gradelevels ";
$resultclass = DB_query($sqlclass,$db);	
while ($myrowclass= DB_fetch_array($resultclass))
{
$students=get_students_in_class($myrowclass['id'],$db);
if($students>0){
foreach ($students as $student=> $st)
{
$aggragate=0;
$total_aggragate=0;
$bus_report = new bus_report($st['id'],$_POST['period_id'],$db);
foreach ($bus_report->consolidated_subjects as $a => $b) {
	$count=$count+1;
	$scheduled = new scheduled($b['subject_id'],$db);
	$scheduled->set_calendar_vars($st['id'],$_POST['period_id'],$b['subject_id'],$db);
	$aggragate=number_format($scheduled->total_marks/3,0);
	$total_aggragate=$total_aggragate + $aggragate;
			}//end of foreach scheduled subjects
			
$sqlroll = "SELECT an.rolled,dm.name FROM annual_ranks an
INNER JOIN debtorsmaster dm ON dm.id=an.student_id
WHERE an.student_id='".$st['id']."'
AND an.academic_year_id='".$_POST['period_id']."'
AND rolled=1";
$resultroll = DB_query($sqlroll,$db);	

if(DB_num_rows($resultroll) >0){
$myrowroll= DB_fetch_array($resultroll);
$rolled=$myrowroll['rolled'];
$name=$myrowroll['name'];
prnMsg(_($name._(' ').'has already been rolled'),'warn');
}
else{			
$sql = "INSERT INTO annual_ranks (class_id,academic_year_id,student_id,total)
	VALUES ('" . $myrowclass['id'] ."','" . $_POST['period_id'] ."','" . $st['id'] ."','$total_aggragate')";

			$ErrMsg = _('Ranking failed');
			$result = DB_query($sql,$db,$ErrMsg);
			}				
		  }
		}
	
}
		
$sqlclass = "SELECT * FROM gradelevels ";
$resultclass = DB_query($sqlclass,$db);	
	while ($myrowclass= DB_fetch_array($resultclass))
	{
	$stude=get_students_rank($myrowclass['id'],$_POST['period_id'],$db);
	if($stude>0){
	$rank=0;
		foreach ($stude as $student_id=> $stid)
		{
		$rank=$rank+1;
		$sql="UPDATE annual_ranks SET rank='$rank'
		WHERE class_id='" . $myrowclass['id'] ."'
		AND academic_year_id='" . $_POST['period_id'] ."'
		AND student_id='" . $stid['student_id'] ."'";
		$rankpost=DB_Query($sql,$db);
		}
	  }
	 } 	
prnMsg( _('completed'),'success');		
}
else { /*The option to print PDF was not hit */
	$title = _('Manage Students');
include('includes/header.inc');


echo '<FORM METHOD="POST" ACTION="' . $_SERVER['PHP_SELF'] . '?' . SID . '">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<CENTER><TABLE class="selection"><TR><TD class="visible">' . _('Academic Year:') . '</TD><TD class="visible"><SELECT Name="period_id">';
$result = DB_query('SELECT id,year FROM years',$db);
while ($myrow = DB_fetch_array($result)) {
	if ($myrow['id']==$_POST['period_id']) {
		echo '<option selected VALUE=';
	} else {
		echo '<option VALUE=';
	}
	echo $myrow['id'] . '>' . $myrow['year'];
} //end while loop
	echo '</SELECT></TD></TR>';
	echo "</TABLE>";
	
	$sql = "SELECT fullaccess FROM www_users
		WHERE userid=  '" . trim($_SESSION['UserID']) . "'";
		$result=DB_query($sql,$db);
		$myrow=DB_fetch_row($result);
	if($myrow[0]==8 || $myrow[0]==11){	
	echo "<P><INPUT TYPE='Submit' NAME='generate' VALUE='" . _('Generate Ranks') . "'>";
	}

	include('includes/footer.inc');
} /*end of else not PrintPDF */

?>