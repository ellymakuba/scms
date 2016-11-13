<?php
$PageSecurity = 2;
if(isset($_POST['period_id'])  && isset($_POST['class_id']) && isset($_POST['sms'])){
include('includes/session.inc');
include("sendsms.php");
require('grades/EndTermReportClass.php');
include("Numbers/Words.php");
$_SESSION['student'] = $myrowclass['student_id'];
$_SESSION['period'] = $_POST['period_id'];
$sqlclass = "SELECT DISTINCT(rs.student_id) FROM registered_students rs
INNER JOIN termly_student_ranks tsr ON tsr.student_id=rs.student_id
 WHERE rs.class_id='" .$_POST['class_id']. "'
 AND rs.period_id='" .$_POST['period_id']. "'
 AND tsr.period_id='" .$_POST['period_id']. "'
 AND tsr.class_id='" .$_POST['class_id']. "'
 ORDER BY tsr.rank";
$resultclass = DB_query($sqlclass,$db);
if(DB_num_rows($resultclass)>0)
{
	while ($myrowclass=DB_fetch_array($resultclass))
	{
		$sql = "SELECT rank,class_id,mean FROM termly_student_ranks
		WHERE period_id =  '". $_POST['period_id'] ."'
		AND student_id='". $myrowclass['student_id'] ."'";
		$result=DB_query($sql,$db);
		$myrow=DB_fetch_row($result);
		$rank=$myrow[0];
		$class_id=$myrow[1];
		$marks=$myrow[2];

		$sql = "SELECT class_rank,class_id FROM termly_class_ranks
		WHERE period_id =  '". $_POST['period_id'] ."'
		AND student_id='". $myrowclass['student_id'] ."'";
		$result=DB_query($sql,$db);
		$myrow=DB_fetch_row($result);
		$class_rank=$myrow[0];
		$class=$myrow[1];

		$sql = "SELECT COUNT(*) FROM termly_class_ranks
		WHERE period_id =  '". $_POST['period_id'] ."'
		AND class_id='$class'";
		$result=DB_query($sql,$db);
		$myrow=DB_fetch_row($result);
		$total_students=$myrow[0];

		$sql = "SELECT COUNT(*) FROM termly_student_ranks
		WHERE period_id =  '". $_POST['period_id'] ."'
		AND class_id='$class_id'";
		$result=DB_query($sql,$db);
		$myrow=DB_fetch_row($result);
		$out_of=$myrow[0];

		$sql = "SELECT DISTINCT(rs.student_id),dtr.name,dtr.debtorno,gl.grade_level,dtr.grade_level_id,
		dtr.age,gl.id,dtr.balance
		FROM registered_students rs
		INNER JOIN debtorsmaster dtr ON dtr.id=rs.student_id
		INNER JOIN classes cl ON cl.id=rs.class_id
		INNER JOIN gradelevels gl ON gl.id=cl.grade_level_id
		WHERE dtr.id =  '". $myrowclass['student_id'] ."'
		AND rs.period_id='". $_POST['period_id'] ."'";
		$result=DB_query($sql,$db);
		$myrow=DB_fetch_row($result);
		$feebalance=$myrow[8];

		$sql3 = "SELECT SUM(dm.age) as age,COUNT(dm.id) as student_count FROM debtorsmaster dm
		INNER JOIN registered_students rs ON rs.student_id=dm.id
		INNER JOIN classes cl ON cl.id=rs.class_id
		INNER JOIN gradelevels gl ON gl.id=cl.grade_level_id
		WHERE gl.id =  '". $myrow[7] ."'
		AND rs.period_id='". $_POST['period_id'] ."'";
		$result3=DB_query($sql3,$db);
		$myrow3=DB_fetch_row($result3);
		$age_sum=$myrow3[0];
		$student_count=$myrow3[1];

		$sql2="SELECT cp.id,terms.title,years.year,cp.end_semester_date FROM collegeperiods cp
		INNER JOIN terms ON terms.id=cp.term_id
		INNER JOIN years ON years.id=cp.year
		WHERE cp.id='".$_SESSION['period']."'";
		$result2=DB_query($sql2,$db);
		$myrow2=DB_fetch_row($result2);
	
		$studentName="St stephen's Lwanda Secondary,".$myrow[3]." End Term Results For ".$myrow[1].' ('.$myrow[2].')';
		$positions='STREAM POSITION:'.$rank.' OUT OF '.$out_of.' CLASS POSITION '.$class_rank.' OUT OF '.$total_students;

		$bus_report = new bus_report($myrowclass['student_id'],$_POST['period_id'],$db);
		$status_array = tep_get_status($_POST['period_id'],$myrowclass['student_id'],$db);
		$subject_marks='';
		foreach ($bus_report->scheduled_subjects as $a => $b)
		 {
			$sql = "select no_of_subjects from termly_class_ranks
			WHERE student_id='".$myrowclass['student_id']."'
			AND period_id='".$_POST['period_id']."'";
			$result = DB_query($sql,$db);
			$row = DB_fetch_row($result);
			$count=$row[0];

			$scheduled = new scheduled($b['subject_id'],$db);
			$scheduled->set_calendar_vars($b['id'],$b['subject_id'],$myrowclass['student_id'],$_POST['period_id'],$db);
			$status_array = tep_get_status($_POST['period_id'],$myrowclass['student_id'],$db);
			$cat_marks =$bus_report->average_cat_marks($b['subject_id'],$myrowclass['student_id'],$_POST['period_id'],$b['id'],$db);
			$totalmarks_array =$bus_report->total_marks($b['subject_id'],$myrowclass['student_id'],$_POST['period_id'],$b['id'],$db);
			$sql = "SELECT grade,comment FROM reportcardgrades
			WHERE range_from <=  '". $totalmarks_array ."'
			AND range_to >='". $totalmarks_array ."'
			AND grading LIKE '". $scheduled->grading."'";
			$result=DB_query($sql,$db);
			$myrow=DB_fetch_row($result);
			$totalmarks_array2=$totalmarks_array2+$totalmarks_array;
			$subject_marks=$subject_marks.' '.$scheduled->subject_name.':'.$myrow[0];
		}
		$sql = "SELECT meanScore FROM termly_class_ranks 
		WHERE student_id='".$myrowclass['student_id']."'
		AND period_id='".$_POST['period_id']."'";
		$result=DB_query($sql,$db);
		$myrow=DB_fetch_row($result);
		$mean_grade=$myrow[0];
		
		$sql = "SELECT grade,comment FROM reportcardgrades
		WHERE title =  '". $mean_grade ."'
		AND grading LIKE 'other'";
		$result=DB_query($sql,$db);
		$myrow=DB_fetch_row($result);
		$grade=$myrow[0];
		$comment=$myrow[1];
		$means='Mean '.number_format($marks/$count,3).' Mean Grade '.$mean_grade;
		$openingDate='';
		$feeBalance=2500;
		$recipient='0720700561';
		$Msg=$studentName.' '.$subject_marks.' '.$means.' '.$positions.' Fee balance '.$feeBalance;
		SendSms($recipient,$Msg,true);
	}
}
}
else { /*The option to print PDF was not hit */
include('includes/session.inc');
$title = _('Stream End Term Report Cards');
include('includes/header.inc');
echo '<p class="page_title_text">' . ' ' . $title. '';
echo '<FORM METHOD="POST" ACTION="' . $_SERVER['PHP_SELF'] . '?' . SID . '">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<br><CENTER><TABLE class="enclosed"><TR><TD>' . _('Stream:') . '</TD><TD><SELECT Name="class_id">';
		DB_data_seek($result, 0);
		$sql = 'SELECT id,class_name FROM classes ORDER BY class_name';
		$result = DB_query($sql, $db);
		while ($myrow = DB_fetch_array($result)) {
			if ($myrow['id'] == $_POST['class_id']) {
				echo '<OPTION SELECTED VALUE=';
			} else {
				echo '<OPTION VALUE=';
			}
			echo $myrow['id'] . '>' . $myrow['class_name'];
		} //end while loop
	echo '</SELECT></TD></TR>';
echo '<TR><TD>' . _('Period:') . '</TD><TD><SELECT Name="period_id">';
		DB_data_seek($result, 0);
		$sql="SELECT cp.id,terms.title,years.year FROM collegeperiods cp
		INNER JOIN terms ON terms.id=cp.term_id
		INNER JOIN years ON years.id=cp.year
		ORDER BY cp.id DESC";
		$result=DB_query($sql,$db);
		while ($myrow = DB_fetch_array($result)) {
			if ($myrow['id'] == $_POST['id']) {
				echo '<OPTION SELECTED VALUE=';
			} else {
				echo '<OPTION VALUE=';
			}
			echo $myrow['id'] . '>'.' '.$myrow['title'].' '.$myrow['year'];
		} //end while loop
	echo '</SELECT></TD></TR>';
	echo "</TABLE>";
	echo "<P><INPUT TYPE='Submit' NAME='sms' VALUE='" . _('SMS Results') . "'>";


	include('includes/footer.inc');
} /*end of else not PrintPDF */

?>
