<?php

$PageSecurity = 2;
if(isset($_POST['period_id']) &&  isset($_POST['html'])){
include('includes/session.inc');
require('grades/DegreeTranscriptClass.php');
//require('grades/ReportCardClass.php');
require('grades/TranscriptAsterikClass.php');
include('includes/header.inc');
?>
<html><body><br /><br /><br />
<?php
$sql="SELECT approved FROM years
WHERE id= '" . $_POST['period_id'] . "'";
$result=DB_query($sql,$db);
$myrow = DB_fetch_array($result);
$approved=$myrow['approved'];
if($approved==0){
prnMsg(_('The results for this academic year have not been approved by the senate'),'warn');
exit("Please wait for the approval for you to check your results");
}

$sql = "SELECT id FROM debtorsmaster
		WHERE debtorno= '". $_SESSION['UserID'] ."'";
        $result=DB_query($sql,$db);
		$myrow=DB_fetch_row($result);
		$student_id=$myrow[0];
		
$_SESSION['period'] = $_POST['period_id'];
$sql = "SELECT year FROM years
		WHERE id= '". $_SESSION['period'] ."'";
        $result=DB_query($sql,$db);
		$myrow=DB_fetch_row($result);
		$academic_year=$myrow[0];

$sql = "SELECT dtr.name,dtr.debtorno,dp.department_name,cs.course_name as course,gl.grade_level FROM debtorsmaster dtr
INNER JOIN courses cs ON cs.id=dtr.course_id
INNER JOIN departments dp ON dp.id=cs.department_id
INNER JOIN gradelevels gl ON gl.id=dtr.grade_level_id 
		WHERE debtorno =  '". $_SESSION['UserID'] ."'";
        $result=DB_query($sql,$db);
		$myrow=DB_fetch_row($result);
		$course=$myrow['course'];
		
$sql="SELECT cp.id,terms.title,years.year FROM collegeperiods cp
		INNER JOIN terms ON terms.id=cp.term_id
		INNER JOIN years ON years.id=cp.year 
		WHERE cp.id='".$_SESSION['period']."'";
		$result=DB_query($sql,$db);
		$myrow=DB_fetch_array($result);
		$title=$myrow['title'];
$count=0;
$i=0;
$bus_report = new bus_report($student_id,$_POST['period_id'],$db);
echo "<form method='post' action=" . $_SERVER['PHP_SELF'] . "?" . SID . ">";
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo "<table  width='50%' align='center' border=\"1\" >";
echo "<tr><td colspan='8'  class=\"centered\"><font size='4'>"._('Masinde Muliro University of Science & Technology')."</td></tr>";
echo "<tr><td colspan='8' class=\"centered\"><font size='4'>"._('Academic Year').' '.$academic_year.' '._('Transcript')."</td></tr>";
echo "<tr><td colspan='5' align='center'><font size='4'>"._('RegNO')._(': '). $_SESSION['UserID'] ."</td></tr>";
echo "<tr><th>"._('Subject')."</th><th>"._('Marks')."</th><th>"._('Points')."</th><th>"._('Grade')."</th></tr>";
$asterik=0;
	$marks=0;
foreach ($bus_report->scheduled_subjects as $a => $b) {
	$count=$count+1;
	$scheduled = new scheduled($b['subject_id'],$db);
	$scheduled->set_calendar_vars($student_id,$_POST['period_id'],$b['id'],$db);
	echo "<tr><td class=\"visible\">".$scheduled->subject_name."</td>";
	
foreach ($scheduled->subject as $sub => $s) {
$asterik=$s['asterik'];
$marks=$s['tmarks'];
	}//end of foreach subject
$totalmarks_array =$bus_report->total_marks2($student_id,$b['subject_id'],$_POST['period_id'],$db);
if($asterik==1){
echo "<td class=\"visible\">"._('*').$totalmarks_array."</td>";
}
else{
echo "<td class=\"visible\">".$totalmarks_array."</td>";
}

$sql = "SELECT title,comment FROM reportcardgrades
		WHERE range_from <=  '". $totalmarks_array."'
		AND range_to >='". $totalmarks_array ."'";
        $result=DB_query($sql,$db);
		$myrow=DB_fetch_row($result);	
echo "<td class=\"visible\">".$myrow[0]."</td>";
echo "<td class=\"visible\">".$myrow[1]."</td></tr>";						
			}//end of foreach scheduled subjects
			
			
$sql = "SELECT er.rule FROM academic_year_remarks ayr 
		INNER JOIN exam_rules er ON er.id=ayr.comment_id
		WHERE student_id= '". $student_id ."'
		AND academic_year_id='". $_SESSION['period']."'";
        $result=DB_query($sql,$db);
		$myrow=DB_fetch_row($result);
		$comment=$myrow[0];			
echo "<tr><td class=\"visible\"> <font color='blue' size='2'><b>"._('Remark :')."</b></font></td><td colspan='3' class=\"visible\">".$comment."</td></tr>";
$sql = "SELECT sub.subject_name FROM subjects sub 
		INNER JOIN fails ON fails.course_id=sub.id
		WHERE fails.student_id= '". $student_id ."'
		AND fails.period_id='".$_SESSION['period']."'";
        $result=DB_query($sql,$db);
		if(DB_fetch_row($result)>0){
echo "<tr><td class=\"visible\"><font color='red' size='4'>"._('Reseat the following courses :')."</font></td><td colspan='3' class=\"visible\"></td></tr>";		
$sql = "SELECT sub.subject_name FROM subjects sub 
		INNER JOIN fails ON fails.course_id=sub.id
		WHERE fails.student_id= '". $student_id ."'
		AND fails.period_id='".$_SESSION['period']."'";
        $result=DB_query($sql,$db);
		while($myrow=DB_fetch_row($result)){
		echo "<tr><td class=\"visible\"></td><td colspan='3' class=\"visible\">".$myrow[0]."</td></tr>";
				}
			}	
echo "</table><br>";	
}

else { /*The option to print PDF was not hit */

	include('includes/session.inc');
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
	echo "</selection></TABLE>";
	
	echo "<INPUT TYPE='Submit' NAME='html' VALUE='" . _('View Html') . "'>";

	include('includes/footer.inc');
} /*end of else not PrintPDF */

?>