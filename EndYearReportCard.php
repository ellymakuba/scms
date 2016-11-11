<?php

$PageSecurity = 2;
if(isset($_POST['student_id']) && isset($_POST['PrintPDF'])){
include('includes/session.inc');
include('includes/PDFStarter.php');
require('grades/EndYearReportCardClass.php');
require('grades/TranscriptAsterikClass.php');
include("Numbers/Words.php");
	
$FontSize=13;
$pdf->addinfo('Title', _('Sales Receipt') );

$_SESSION['student'] = $_POST['student_id'];

$sql = "SELECT year FROM years
WHERE id =  '". $_POST['period_id'] ."'";
$result=DB_query($sql,$db);
$myrow=DB_fetch_row($result);
$year=$myrow[0];	
$PageNumber=1;
$line_height=12;
if ($PageNumber>1){
	$pdf->newPage();
}
$FontSize=10;
$YPos= $Page_Height-$Top_Margin;
$XPos=0;
$pdf->addJpegFromFile($_SESSION['LogoFile'] ,250,$YPos-70,0,50);
$FontSize=8;
$LeftOvers = $pdf->addTextWrap(50,$YPos-($line_height*5),300,$FontSize,$_SESSION['CompanyRecord']['coyname']);
$LeftOvers = $pdf->addTextWrap(50,$YPos-($line_height*6),300,$FontSize,$_SESSION['CompanyRecord']['regoffice3']);
$LeftOvers = $pdf->addTextWrap(50,$YPos-($line_height*7),300,$FontSize,$_SESSION['CompanyRecord']['regoffice5']);
$LeftOvers = $pdf->addTextWrap($Page_Width-$Right_Margin-150,$YPos-($line_height*5),140,$FontSize,_('Phone').': ' .  $_SESSION['CompanyRecord']['telephone']);
$LeftOvers = $pdf->addTextWrap($Page_Width-$Right_Margin-150,$YPos-($line_height*6),300,$FontSize, _('Email').': ' . $_SESSION['CompanyRecord']['email']);
$LeftOvers = $pdf->addTextWrap($Page_Width-$Right_Margin-150,$YPos-($line_height*7),300,$FontSize, _('website').': ' . $_SESSION['CompanyRecord']['fax']);

$sql = "SELECT rank,class_id FROM annual_ranks  
WHERE academic_year_id =  '". $_POST['period_id'] ."'
AND student_id='". $_POST['student_id'] ."'";
$result=DB_query($sql,$db);
$myrow=DB_fetch_row($result);
$rank=$myrow[0];
$class_id=$myrow[1];


$sql = "SELECT COUNT(*) FROM annual_ranks  
WHERE academic_year_id =  '". $_POST['period_id'] ."'
AND class_id='$class_id'";
$result=DB_query($sql,$db);
$myrow=DB_fetch_row($result);
$out_of=$myrow[0];

$sql = "SELECT dtr.name,dtr.debtorno,gl.grade_level FROM debtorsmaster dtr
INNER JOIN gradelevels gl ON gl.id=dtr.grade_level_id 
WHERE dtr.id =  '". $_SESSION['student'] ."'";
$result=DB_query($sql,$db);
$myrow=DB_fetch_row($result);

$sql = "SELECT DISTINCT(rs.student_id),dtr.name,dtr.debtorno,gl.grade_level,dtr.grade_level_id FROM registered_students rs
INNER JOIN debtorsmaster dtr ON dtr.id=rs.student_id
INNER JOIN classes cl ON cl.id=rs.class_id 
INNER JOIN gradelevels gl ON gl.id=cl.grade_level_id
WHERE dtr.id =  '". $_SESSION['student'] ."'
AND rs.academic_year_id='". $_POST['period_id'] ."'";
$result=DB_query($sql,$db);
$myrow=DB_fetch_row($result);
		
$LeftOvers = $pdf->addTextWrap(40,$YPos-($line_height*10),300,$FontSize,_('NAME OF STUDENT').':'.strtoupper($myrow[1]));
$LeftOvers = $pdf->addTextWrap(40,$YPos-($line_height*10.9),300,$FontSize,_('POSITION').':'.$rank );	
$LeftOvers = $pdf->addTextWrap(40,$YPos-($line_height*11.5),300,$FontSize,_('ACADEMIC YEAR').':'. $year );
		
$LeftOvers = $pdf->addTextWrap($Page_Width-$Right_Margin-150,$YPos-($line_height*10),300,$FontSize, _('REGNO').': ' . $myrow[2]);
$LeftOvers = $pdf->addTextWrap($Page_Width-$Right_Margin-150,$YPos-($line_height*10.9),300,$FontSize, _('Out Of').': ' . $out_of);
$LeftOvers = $pdf->addTextWrap($Page_Width-$Right_Margin-150,$YPos-($line_height*11.5),300,$FontSize, _('Year of Study').': ' . $myrow[3]);

$FontSize=7;	
$YPos -=100;
$LeftOvers = $pdf->addTextWrap(250,$YPos,400,$FontSize,_('END YEAR REPORT CARD'));

$YPos -=(1*$line_height);
$FontSize=7;	

$FontSize=8;
$YPos -=30;
$XPos=150;

$pdf->line($Left_Margin, $YPos,$Page_Width-$Right_Margin, $YPos);
$YPos -=(1*$line_height);
$YPos2=$YPos+$line_height;
$count=0;
$units=0;
$i=0;
$aggragate=0;
$total_aggragate=0;
$YPos -=24;
$line_height=10;
$bus_report = new bus_report($_POST['student_id'],$_POST['period_id'],$db);
foreach ($bus_report->consolidated_subjects as $a => $b) {
$FontSize=8;
	$count=$count+1;
	$total_aggragate=$total_aggragate + $aggragate;
	$scheduled = new scheduled($b['subject_id'],$db);
	$scheduled->set_calendar_vars($_POST['student_id'],$_POST['period_id'],$b['subject_id'],$db);
$FontSize=6;
	$LeftOvers = $pdf->addTextWrap(50,$YPos+13,300,$FontSize,$scheduled->subject_code);	
	$LeftOvers = $pdf->addTextWrap(155,$YPos+13,300,$FontSize,$scheduled->subject_name);
	$LeftOvers = $pdf->addTextWrap(330,$YPos+13,300,$FontSize,$scheduled->units);
	$pdf->line($Left_Margin, $YPos+$line_height,$Page_Width-$Right_Margin, $YPos+$line_height);
$units=$units+$scheduled->units;
	$XPos2=380;
	$YPos -=(1*$line_height);
	
	$aggragate=number_format($scheduled->total_marks/3,0);
	$LeftOvers = $pdf->addTextWrap($XPos2+20,$YPos+23,300,$FontSize,$aggragate);	
$sql = "SELECT title,grade FROM reportcardgrades
		WHERE range_from <=  '". $aggragate."'
		AND range_to >='". $aggragate ."'";
        $result=DB_query($sql,$db);
		$myrow=DB_fetch_row($result);
$LeftOvers = $pdf->addTextWrap($XPos2+90,$YPos+23,300,$FontSize,$myrow[1]);		
						
			}//end of foreach scheduled subjects
			
$LeftOvers = $pdf->addTextWrap(50,$YPos2-10,300,$FontSize,_('Subject Code'));
$LeftOvers = $pdf->addTextWrap(155,$YPos2-10,300,$FontSize,_('Subject Name'));		
$LeftOvers = $pdf->addTextWrap($XPos2+10,$YPos2-10,300,$FontSize,_('MARKS'));
$pdf->line($XPos2-10,$YPos2,$XPos2-10, $YPos+($line_height*2));
$LeftOvers = $pdf->addTextWrap($XPos2+80,$YPos2-10,300,$FontSize,_('GRADES'));
$pdf->line($XPos2+70,$YPos2,$XPos2+70, $YPos+($line_height*2));
$pdf->line($Left_Margin, $YPos2-14,$Page_Width-$Right_Margin, $YPos2-14);
$pdf->line(150,$YPos2,150, $YPos+($line_height*2));
$pdf->line(325,$YPos2,325, $YPos+($line_height*2));
$pdf->line(40,$YPos2,40, $YPos+($line_height*2));
$pdf->line(566,$YPos2,566, $YPos+($line_height*2));

//$pdf->line($Left_Margin, $YPos+$line_height,$Page_Width-$Right_Margin, $YPos+$line_height);
$nw = new Numbers_Words();
$LeftOvers = $pdf->addTextWrap(40,$YPos,300,$FontSize,_('TOTAL NUMBER OF SUBJECTS TAKEN').' :'._('[').$count._(']').' '._('[').strtoupper($nw->toWords($count,$locale)._(']')));
$out_of=100*$count;

if($count>0){
$mean_grade=$totalmarks_array2/$count;
}
else{
$mean_grade=1;
}
$sql = "SELECT rule FROM exam_rules
		WHERE range_from <=  '". $total_aggragate ."'
		AND range_to >='". $total_aggragate."'";
		$result=DB_query($sql,$db);
		$myrow=DB_fetch_row($result);
$LeftOvers = $pdf->addTextWrap(40,$YPos-20,300,$FontSize,_('Comment').' :'.$myrow[0]);	

$FontSize=13;
$LeftOvers = $pdf->addTextWrap(40,$YPos-150,300,$FontSize,_('SIGNED'.':'));
$LeftOvers = $pdf->addTextWrap(90,$YPos-150,60,$FontSize,'______________________________________________________________________________');
$LeftOvers = $pdf->addTextWrap(220,$YPos-150,300,$FontSize,_('Date'));
$LeftOvers = $pdf->addTextWrap(250,$YPos-150,60,$FontSize,'______________________________________________________________________________');

$FontSize=8;
$LeftOvers = $pdf->addTextWrap(90,$YPos-160,300,$FontSize,_(' Principal'));

$pdf->Output('Receipt-'.$_GET['ReceiptNumber'], 'I');
	
}
if(isset($_POST['student_id']) && isset($_POST['html'])){
include('includes/session.inc');
require('grades/ReportCardClass.php');
?>
<html><body><br /><br /><br />
<?php
$_SESSION['student'] = $_POST['student_id'];
$_SESSION['period'] = $_POST['period_id'];
$sql = "SELECT dtr.name,dtr.debtorno,dp.department_name,cs.course_name as course,gl.grade_level FROM debtorsmaster dtr
INNER JOIN courses cs ON cs.id=dtr.course_id
INNER JOIN departments dp ON dp.id=cs.department_id
INNER JOIN gradelevels gl ON gl.id=dtr.grade_level_id 
		WHERE debtorno =  '". $_SESSION['student'] ."'";
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
$totalmarks_array2=0;
$bus_report = new bus_report($_POST['student_id'],$_POST['period_id'],$db);
$status_array = tep_get_status($db);
echo "<form method='post' action=" . $_SERVER['PHP_SELF'] . "?" . SID . ">";
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo "<table border='1' width='50%' align='center'='20'>";
echo "<tr><td colspan='8'><font size='16'>"._('School')."</td></tr>";
echo "<tr><td colspan='8' align='center'><font size='4'>"._('Management System')."</td></tr>";
echo "<tr><td colspan='8' align='center'><font size='2'>"._('RegNO')._(': '). $_SESSION['student'] .
_('  ')._('Term')._(': ').$title._(' ')._('Course')._(': ').$course."</td></tr>";
echo "<tr><td>"._('Subject')."</td>";
foreach ($status_array as $r => $s) {
echo "<td>".$s['title']."</td>";
		}
echo "<td>"._('Total(%)')."</td>";
echo "<td>"._('Grade')."</td>";
echo "<td>"._('Comment')."</td>";
echo "</tr>"; 
foreach ($bus_report->scheduled_subjects as $a => $b) {

	$count=$count+1;
	$scheduled = new scheduled($b['subject_id'],$db);
	$scheduled->set_calendar_vars($b['id'],$db);
echo "<tr><td>".$scheduled->subject_name."</td>";
	$status_array = tep_get_status($db);
foreach ($scheduled->status as $y=>$z) {
	$i++;
echo "<td>".$z['marks']."</td>";
	
				}
	$totalmarks_array =$bus_report->total_marks($_POST['student_id'],$b['id'],$b['subject_id'],$db);
	$sql = "SELECT title,comment FROM reportcardgrades
	WHERE range_from <=  '". $totalmarks_array ."'
	AND range_to >='". $totalmarks_array ."'";
    $result=DB_query($sql,$db);
	$myrow=DB_fetch_row($result);
echo "<td>".$totalmarks_array."</td>";
echo "<td>".$myrow[0]."</td>";
echo "<td>".$myrow[1]."</td>";				
	$totalmarks_array2=$totalmarks_array2+$totalmarks_array;					
			}
echo "</tr><tr><td>"._('Total Subjects')._(' ').$count."</td>";
echo "<td>"._('Total Marks')._(' ').$totalmarks_array2."</td>";
$out_of=100*$count;
echo "<td>"._('Out of')._(' ').$out_of."</td></tr>";

$mean_grade=$totalmarks_array2/$count;
$sql = "SELECT title,comment FROM reportcardgrades
		WHERE range_from <=  '". $mean_grade ."'
		AND range_to >='". $mean_grade."'";
		$result=DB_query($sql,$db);
		$myrow=DB_fetch_row($result);
echo "<tr>";		
echo "<td>"._('Mean Grade')._(' ').$myrow[1]."</td></tr>";
echo "<tr>";
echo "<td colspan='6'>"._('KEY TO GRADING SYSTEM')."</td></tr>";
echo "<tr><td colspan='2'>"._('100-90  1 Distinction')."</td><td colspan='2'>"._('69-60   4 Credit')."</td><td colspan='2'>"._('39-30  7 Reffered')."</td></tr>";
echo "<tr><td colspan='2'>"._('89-80   2 Distiction')."</td><td colspan='2'>"._('59-50  5 Pass')."</td><td colspan='2'>"._('29-0   8 Fail')."</td></tr>";
echo "<tr><td colspan='2'>"._('79-70   3 Credit')."</td><td colspan='2'>"._('49-40  6 Pass')."</td></tr>";






echo "</table>";	
}

else { /*The option to print PDF was not hit */

	include('includes/session.inc');
	$title = _('Manage Students');

include('includes/header.inc');

if(isset($_GET['ID'])){
$_POST['student_id']=$_GET['ID'];
}
echo '<FORM METHOD="POST" ACTION="' . $_SERVER['PHP_SELF'] . '?' . SID . '">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<CENTER><TABLE class="selection"><TR><TD class="visible">' . _('student:') . '</TD><TD class="visible"><SELECT Name="student_id">';
		DB_data_seek($result, 0);
		$sql = 'SELECT id,debtorno,name FROM debtorsmaster';
		$result = DB_query($sql, $db);
		while ($myrow = DB_fetch_array($result)) {
			if ($myrow['id'] == $_POST['student_id']) {  
				echo '<OPTION SELECTED VALUE=';
			} else {
				echo '<OPTION VALUE=';
			}
			echo $myrow['id'] . '>' . $myrow['name'];
		} //end while loop
	echo '</SELECT></TD></TR>';
		echo '<CENTER><TR><TD class="visible">' . _('Period:') . '</TD><TD class="visible"><SELECT Name="period_id">';
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
	echo "<P><INPUT TYPE='Submit' NAME='PrintPDF' VALUE='" . _('PrintPDF') . "'>";
	}

	include('includes/footer.inc');
} /*end of else not PrintPDF */

?>