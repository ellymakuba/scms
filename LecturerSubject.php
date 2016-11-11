<?php
$PageSecurity = 2;
include('includes/session.inc');
if(isset($_POST['period_id']) && isset($_POST['class_id']) && isset($_POST['subject_id'])){
include('includes/PDFStarter.php');
require('grades/LecturerSubjectClass.php');


$_SESSION['class'] = $_POST['class_id'];
$_SESSION['period'] = $_POST['period_id'];
$_SESSION['subject'] = $_POST['subject_id'];			
$PageNumber=1;
$line_height=12;
NewPageHeader ();
$FontSize=18;
$pdf->addJpegFromFile($_SESSION['LogoFile'] ,$XPos+260,$YPos-120,0,100);
$YPos-=(2*$line_height);
$pdf->SetFont('times', '', 18, '', 'false');
$LeftOvers = $pdf->addTextWrap(100,$YPos-($line_height*10),400,$FontSize,strtoupper($_SESSION['CompanyRecord']['coyname']));
		$FontSize=12;
		$LeftOvers = $pdf->addTextWrap(180,$YPos-($line_height*11),400,$FontSize,
		$_SESSION['CompanyRecord']['regoffice3'].' - '.$_SESSION['CompanyRecord']['regoffice5'].' - '.('TEL :').' '.
		$_SESSION['CompanyRecord']['regoffice4']);
		$FontSize=10;
		$LeftOvers = $pdf->addTextWrap(240,$YPos-($line_height*12),300,$FontSize,_('EMAIL :').' '.$_SESSION['CompanyRecord']['email']);
$YPos-=(2*$line_height);
$pdf->SetFont('times', '', 12, '', 'false');
$FontSize=12;

$sql = "SELECT usr.realname,sub.subject_name FROM subjects sub
INNER JOIN registered_students rs ON rs.subject_id=sub.id
INNER JOIN www_users usr ON usr.userid=rs.teacher
WHERE sub.id =  '". $_SESSION['subject'] ."'";
$result=DB_query($sql,$db);
$myrow=DB_fetch_array($result);
$lecturer=$myrow['realname'];
$subject=$myrow['subject_name'];

/*$LeftOvers = $pdf->addTextWrap(100,$YPos-($line_height*11),500,$FontSize, _('Reportcard For').': ' . $myrow[0].'    '._('Period').': ' .$myrow2[1].'-'.$myrow2[2]);*/	
$LeftOvers = $pdf->addTextWrap(200,$YPos-($line_height*12),400,$FontSize,_('TEACHER SUBJECT PERFORMANCE'));
 $LeftOvers = $pdf->addTextWrap(200,$YPos-($line_height*12.3),100,$FontSize,'______________________________________________________________________________');

$LeftOvers = $pdf->addTextWrap(120,$YPos-($line_height*15),300,$FontSize, _('Subject').': ' . $subject);
$LeftOvers = $pdf->addTextWrap(300,$YPos-($line_height*15),300,$FontSize, _('Teacher').': ' . $lecturer);	
$YPos +=20;
$YPos -=$line_height;
//Note, this is ok for multilang as this is the value of a Select, text in option is different

$YPos -=(12*$line_height);

$pdf->line($Left_Margin, $YPos+$line_height,$Page_Width-$Right_Margin, $YPos+$line_height);

$YPos -=50;
$YPos -=$line_height;
$Left_Margin2=100;
$pdf->line($Left_Margin2, $YPos+$line_height,500, $YPos+$line_height);

$line_width=40;
$XPos=180;
$YPos2=$YPos;
$count=0;
$i=0;
$bus_report2 = new bus_report2($_POST['class_id'],$_POST['period_id'],$_POST['subject_id'],$db);
$subjects_array = tep_get_exam_mode2($db);
$current_student='';
foreach ($subjects_array as $r => $s) {
$LeftOvers = $pdf->addTextWrap($XPos+65,$YPos,300,$FontSize,$s['title']);
$XPos +=(1.5*$line_width);
		}
$LeftOvers = $pdf->addTextWrap($XPos+70,$YPos+1,300,$FontSize,_('AVG'));
$YPos -=10;
$rank =0;
$pdf->line($Left_Margin2, $YPos,500, $YPos);
$YPos -=$line_height;
$count=0;
$marks_total=0;
foreach ($bus_report2->scheduled_students as $a => $b) {
if ($YPos < ($Bottom_Margin + (5* $line_height))){ //need 5 lines grace otherwise start new page
			$PageNumber++;
			NewPageHeader ();
		}	
$count=$count+1;	
$scheduled2 = new scheduled2($b['student_id'],$db);
$scheduled2->set_calendar_vars2($_POST['class_id'],$b['id'],$db);
$LeftOvers = $pdf->addTextWrap($Left_Margin2+3,$YPos+1,300,$FontSize,$scheduled2->name);
$pdf->line($Left_Margin2, $YPos,500, $YPos);
$YPos -=(1*$line_height);
$XPos2=240;
$totalmarks_array =$bus_report2->total_marks($b['student_id'],$_POST['period_id'],$_POST['subject_id'],$db);
foreach ($scheduled2->exam_mode as $y=>$z) {
$i++;

	$LeftOvers = $pdf->addTextWrap($XPos2+20,$YPos+15,300,$FontSize,$z['tmarks']);
	$XPos2 +=(1.5*$line_width);
	if($PageNumber <2){
	$pdf->line($XPos2,$YPos+43,$XPos2, $YPos+12);
	}
	if($PageNumber >1){
	$pdf->line($XPos2, $YPos+32,$XPos2, $YPos+12);
	
	//$pdf->line(19, $YPos+24,$Page_Width-$Right_Margin, $YPos+24);
	}
if($PageNumber ==1){
$pdf->line($Left_Margin2, $YPos+47,$Left_Margin2, $YPos+($line_height*1));
$pdf->line(240, $YPos+47,240, $YPos+($line_height*1));
$pdf->line(500, $YPos+47,500, $YPos+($line_height*1));
}
if($PageNumber >1){
$pdf->line($Left_Margin2, 831,500, 831);
$pdf->line($Left_Margin2, 830,$Left_Margin2, $YPos+($line_height*1));
$pdf->line(240, 830,240, $YPos+12);
$pdf->line(500, 830,500, $YPos+12);
}	
}
$LeftOvers = $pdf->addTextWrap($XPos2+10,$YPos+15,300,$FontSize,$totalmarks_array);
$marks_total=$marks_total+$totalmarks_array;
		}
$subject_mean=$bus_report2->subject_meangrade2($_POST['subject_id'],$_POST['period_id'],$_POST['class_id'],$db);			
	


$pdf->line($Left_Margin2, $YPos+$line_height,500, $YPos+$line_height);
if($count > 0){
$mean=$marks_total/$count;
}
else{
$mean=0;
}
$LeftOvers = $pdf->addTextWrap($Left_Margin2+150,$YPos-10,300,$FontSize,_('Mean')._(': ').number_format($mean,2));			


$pdf->Output('Receipt-'.$_GET['ReceiptNumber'], 'I');


}
else { /*The option to print PDF was not hit */
$title = _('Exam Subject Breakdown Report');
include('includes/header.inc');
echo '<p class="page_title_text">' . ' ' . $title. '';
echo '<FORM METHOD="POST" ACTION="' . $_SERVER['PHP_SELF'] . '?' . SID . '">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<CENTER><TABLE class="enclosed"><TR><TD>' . _('Stream:') . '</TD><TD><SELECT Name="class_id">';
		DB_data_seek($result, 0);
		$sql = 'SELECT cl.id,cl.class_name FROM classes cl ORDER BY class_name';
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
echo '<tr><td>' . _('Subject') . ":</td>
		<td><select name='subject_id'>";
		echo '<OPTION SELECTED VALUE=0>' . _('Select Subject');
		$sql="SELECT id,subject_name FROM subjects ";
		$result=DB_query($sql,$db);
		while ($myrow = DB_fetch_array($result)) {
		echo '<option value='. $myrow['id'] . '>' . $myrow['subject_name'];
		} //end while loop
		DB_data_seek($result,0);
		echo '</select></td></tr>';	
	echo "</TABLE>";
	echo "<P><CENTER><INPUT TYPE='Submit' NAME='PrintPDF' VALUE='" . _('Display') . "'>";

	include('includes/footer.inc');;
} /*end of else not PrintPDF */
function NewPageHeader () {
	global $PageNumber,
				$pdf,
				$YPos,
				$YPos2,
				$YPos4,
				$Page_Height,
				$Page_Width,
				$Top_Margin,
				$FontSize,
				$Left_Margin,
				$XPos,
				$XPos2,
				$Right_Margin,
				$line_height;
				$line_width;

	/*PDF page header for GL Account report */

	if ($PageNumber > 1){
		$pdf->newPage();
	}
$YPos= $Page_Height-$Top_Margin;


	


}
?>