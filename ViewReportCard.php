<?php

$PageSecurity = 2;
if(isset($_POST['period_id']) &&  isset($_POST['html'])){
include('includes/session.inc');
require('grades/ReportCardClass.php');
include('includes/header.inc');
?>
<html><body><br /><br /><br />
<?php
$_SESSION['period'] = $_POST['period_id'];
$sql = "SELECT dtr.name,dtr.debtorno,dp.department_name,cs.course_name as course,gl.grade_level FROM debtorsmaster dtr
INNER JOIN courses cs ON cs.id=dtr.course_id
INNER JOIN departments dp ON dp.id=cs.department_id
INNER JOIN gradelevels gl ON gl.id=dtr.grade_level_id 
		WHERE debtorno =  '". $_SESSION['UserID'] ."'";
        $result=DB_query($sql,$db);
		$myrow=DB_fetch_row($result);
		$course=$myrow['course'];
		
$sql="SELECT cp.id,terms.title,years.year,cp.end_date FROM collegeperiods cp
		INNER JOIN terms ON terms.id=cp.term_id
		INNER JOIN years ON years.id=cp.year 
		WHERE cp.id='".$_SESSION['period']."'";
		$result=DB_query($sql,$db);
		$myrow=DB_fetch_array($result);
		$title=$myrow['title'];
$count=0;
$i=0;
$bus_report = new bus_report($_SESSION['UserID'],$_POST['period_id'],$db);
$status_array = tep_get_status($db);
echo "<form method='post' action=" . $_SERVER['PHP_SELF'] . "?" . SID . ">";
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo "<table  width='50%' align='center' border=\"1\" >";
echo "<tr><td colspan='8'  class=\"centered\"><font size='4'>"._('Masinde Muliro University of Science & Technology')."</td></tr>";
echo "<tr><td colspan='8' class=\"centered\"><font size='4'>"._('End semester Marks Report')."</td></tr>";
echo "<tr><td colspan='2' align='center'><font size='4'>"._('RegNO')._(': '). $_SESSION['UserID'] ."</td><td colspan='5'>".
_('  ')._('Term')._(': ').$title._(' ')._('Course')._(': ').$course."</td></tr>";
echo "<tr><td class=\"visible\">"._('Subject')."</td>";
foreach ($status_array as $r => $s) {
echo "<td class=\"visible\">".$s['title']."</td>";
		}
echo "<td class=\"visible\">"._('Total(%)')."</td>";
echo "<td class=\"visible\">"._('Points')."</td>";
echo "<td class=\"visible\">"._('Grade')."</td>";
echo "</tr>"; 
foreach ($bus_report->scheduled_subjects as $a => $b) {

	$count=$count+1;
	$scheduled = new scheduled($b['subject_id'],$db);
	$scheduled->set_calendar_vars($b['id'],$db);

echo "<tr><td class=\"visible\">".$scheduled->subject_name."</td>"; 
	$status_array = tep_get_status($db);
foreach ($scheduled->status as $y=>$z) {
	$i++;
echo "<td class=\"visible\">".$z['marks']."</td>";
	
				}
	$totalmarks_array =$bus_report->total_marks($_SESSION['UserID'],$b['id'],$b['subject_id'],$db);
	$sql = "SELECT title,comment FROM reportcardgrades
	WHERE range_from <=  '". $totalmarks_array ."'
	AND range_to >='". $totalmarks_array ."'";
    $result=DB_query($sql,$db);
	$myrow=DB_fetch_row($result);
echo "<td class=\"visible\">".$totalmarks_array."</td>";
echo "<td class=\"visible\">".$myrow[0]."</td>";
echo "<td class=\"visible\">".$myrow[1]."</td>";				
	$totalmarks_array2=$totalmarks_array2+$totalmarks_array;					
			}
echo "</tr><tr><td class=\"visible\">"._('Total Subjects')._(' ').$count."</td>";
echo "<td class=\"visible\">"._('Total Marks')._(' ').$totalmarks_array2."</td>";
$out_of=100*$count;
echo "<td class=\"visible\">"._('Out of')._(' ').$out_of."</td>";

$mean_grade=$totalmarks_array2/$count;
$sql = "SELECT title,comment FROM reportcardgrades
		WHERE range_from <=  '". $mean_grade ."'
		AND range_to >='". $mean_grade."'";
		$result=DB_query($sql,$db);
		$myrow=DB_fetch_row($result);		
echo "<td class=\"visible\">"._('Mean Grade')._(' :').$myrow[1]."</td></tr>";
echo "<tr>";
echo "<td colspan='6' class=\"visible\">"._('KEY TO GRADING SYSTEM')."</td></tr>";
echo "<tr><td colspan='2' class=\"visible\">"._('100-90  1 Distinction')."</td><td colspan='2' class=\"visible\">"._('69-60   4 Credit')."</td><td colspan='2' class=\"visible\">"._('39-30  7 Reffered')."</td></tr>";
echo "<tr><td colspan='2' class=\"visible\">"._('89-80   2 Distiction')."</td><td colspan='2' class=\"visible\">"._('59-50  5 Pass')."</td><td colspan='2' class=\"visible\">"._('29-0   8 Fail')."</td></tr>";
echo "<tr><td colspan='2' class=\"visible\">"._('79-70   3 Credit')."</td><td colspan='2' class=\"visible\">"._('49-40  6 Pass')."</td></tr>";
echo "</table><br>";	
}

else { /*The option to print PDF was not hit */

	include('includes/session.inc');
	$title = _('Manage Students');

include('includes/header.inc');

echo '<FORM METHOD="POST" ACTION="' . $_SERVER['PHP_SELF'] . '?' . SID . '">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<CENTER><TABLE><TR><TD>' . _('Period:') . '</TD><TD><SELECT Name="period_id">';
		DB_data_seek($result, 0);
		$sql="SELECT cp.id,terms.title,years.year FROM collegeperiods cp
		INNER JOIN terms ON terms.id=cp.term_id
		INNER JOIN years ON years.id=cp.year ";
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
	$sql = "SELECT sr.secrolename FROM www_users us
	INNER JOIN securityroles sr ON sr.secroleid=us.fullaccess
		WHERE us.userid=  '" . $_SESSION['UserID'] . "'";
		$result=DB_query($sql,$db);
		$myrow=DB_fetch_row($result);
	if($myrow[0]==_('System Administrator') || $myrow[0]==_('Academic Officer')){	
	echo "<P><INPUT TYPE='Submit' NAME='PrintPDF' VALUE='" . _('PrintPDF') . "'>";
	}
	echo "<INPUT TYPE='Submit' NAME='html' VALUE='" . _('View Html') . "'>";

	include('includes/footer.inc');
} /*end of else not PrintPDF */

?>