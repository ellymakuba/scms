<?php

$PageSecurity = 2;
include('includes/session.inc');
if(isset($_POST['period_id']) && isset($_POST['class_id']) && isset($_POST['PrintPDF'])){
include('includes/PDFStarter.php');
include('grades/OveralClassReport.php');
include('grades/ClassSubjectMean.php');

$_SESSION['class'] = $_POST['class_id'];
$_SESSION['period'] = $_POST['period_id'];		
$PageNumber=1;
$line_height=12;
NewPageHeader ();
$FontSize=18;
function studentsRegisteredForSubject($subject,$period,$class,$db)
{
$sql="SELECT id FROM classes WHERE grade_level_id='$class'";
$result=DB_query($sql,$db);
while($myrow=DB_fetch_row($result)){
$streamsInClass[]=$myrow[0];
}
$streamsInClass=implode(', ', $streamsInClass);
$sql="SELECT COUNT(*) FROM registered_students WHERE subject_id='$subject' AND period_id='$period' AND class_id IN ($streamsInClass)";
$result=DB_query($sql,$db);
$myrow=DB_fetch_row($result);
return $myrow[0];
}
function studentTakesSubject($student,$subject,$period,$db){
$studentTakesThisSubject=0;
$sql="SELECT student_id FROM registered_students WHERE subject_id='$subject' AND period_id='$period' AND student_id='$student'";
$result=DB_query($sql,$db);
$num=DB_fetch_row($result);
if($num[0] >0){
$studentTakesThisSubject=1;
}

return $studentTakesThisSubject;
}

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
$FontSize=10;

$style = array('width' => 0.70, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'phase' => 10, 'color' => array(12, 12, 12));

$sql="SELECT cp.id,terms.title,years.year FROM collegeperiods cp
INNER JOIN terms ON terms.id=cp.term_id
INNER JOIN years ON years.id=cp.year 
WHERE cp.id =  '". $_SESSION['period'] ."'";
$result=DB_query($sql,$db);
$myrow=DB_fetch_array($result);
$title=$myrow['title'];
$year=$myrow['year'];

$sql = "SELECT id,grade_level FROM gradelevels
WHERE id =  '". $_SESSION['class'] ."'";
$result=DB_query($sql,$db);
$myrow=DB_fetch_array($result);
$class_name=$myrow['grade_level'];
/*$LeftOvers = $pdf->addTextWrap(100,$YPos-($line_height*11),500,$FontSize, _('Reportcard For').': ' . $myrow[0].'    '._('Period').': ' .$myrow2[1].'-'.$myrow2[2]);*/	
$LeftOvers = $pdf->addTextWrap(200,$YPos-($line_height*12),400,$FontSize,_('OVERAL CLASS PERFORMANCE'));
 $LeftOvers = $pdf->addTextWrap(200,$YPos-($line_height*12.3),80,$FontSize,'______________________________________________________________________________');
$LeftOvers = $pdf->addTextWrap(100,$YPos-($line_height*15),300,$FontSize, $title.' ' . $year);
$LeftOvers = $pdf->addTextWrap(300,$YPos-($line_height*15),300,$FontSize,_('Class')._(': '). $class_name);
	
$YPos +=20;
$YPos -=$line_height;
//Note, this is ok for multilang as this is the value of a Select, text in option is different

$YPos -=(12*$line_height);

$pdf->line(19, $YPos+$line_height,$Page_Width-$Right_Margin, $YPos+$line_height,$style);

$YPos -=50;
$YPos -=$line_height;
$pdf->line(19, $YPos+$line_height,$Page_Width-$Right_Margin, $YPos+$line_height,$style);

$YPos -=(8*$line_height);
$line_width=40;
$XPos=180;
$YPos2=$YPos;
$count=0;
$i=0;
$FontSize=8;
$bus_report = new bus_report($_POST['class_id'],$_POST['period_id'],$db);
$subjects_array = tep_get_subjects($_POST['class_id'],$_POST['period_id'],$db);
$current_student='';
$pdf->starttransform();
$pdf->xy($XPos-20,332);
$pdf->rotate(90);
$LeftOvers = $pdf->addTextWrap($XPos-65,$YPos-5,300,$FontSize,_('GENDER'));
$pdf->stoptransform();
foreach ($subjects_array as $r => $s)
 {
	$pdf->starttransform();
	$pdf->xy($XPos,332);
	$pdf->rotate(90);
	$LeftOvers = $pdf->addTextWrap($XPos-45,$YPos,300,$FontSize,$s['subject_name']);
	$pdf->stoptransform();		
	$XPos +=(0.55*$line_width);
}
	$LeftOvers = $pdf->addTextWrap($XPos+35,$YPos,300,$FontSize,_('Mean'));
	$LeftOvers = $pdf->addTextWrap($XPos+70,$YPos,300,$FontSize,_('Grade'));
	$LeftOvers = $pdf->addTextWrap($XPos+110,$YPos,300,$FontSize,_('Rank'));		
	$YPos -=10;
	$rank =0;
	$pdf->line(19, $YPos,$Page_Width-$Right_Margin, $YPos,$style);	
	$YPos -=$line_height;
foreach ($bus_report->scheduled_students as $sa => $st) 
{
	$total=0;
	$no_of_students=$no_of_students+1;
	if ($YPos < ($Bottom_Margin + (2* $line_height)))
	{ 
		$PageNumber++;
		NewPageHeader ();
	}
	$rank=$rank+1;
	//$LeftOvers = $pdf->addTextWrap(200,$YPos+1,300,$FontSize,$st['initial']);
	$LeftOvers = $pdf->addTextWrap(21,$YPos+1,300,$FontSize,$st['name']);
	$pdf->line(19, $YPos,$Page_Width-$Right_Margin, $YPos,$style);	
	$YPos -=(0.8*$line_height);
	$scheduled = new scheduled($st['student_id'],$db);
	$subjects_taken_by_student=0;
	$student_total=0;
	$student_total2=0;
	$scheduled->set_calendar_vars($_POST['class_id'],$st['student_id'],$_POST['period_id'],$st['id'],$db);
	$XPos2=230;
	$subject_meangrade_array=0;
	foreach ($scheduled->subject as $y=>$z)
	{		
		$addToSubject=0;
		$addToSubject=studentTakesSubject($st['student_id'],$z['id'],$_POST['period_id'],$db);		
		$LeftOvers = $pdf->addTextWrap($XPos2-20,$YPos+10,300,$FontSize,$z['tmarks']);
		if(isset($z['tmarks']) && $z['tmarks'] !="")
		{
		     $sql = "SELECT grade,title FROM reportcardgrades
		     WHERE range_from <=  '". $z['tmarks'] ."'
		     AND range_to >='". $z['tmarks']."'
		     AND grading LIKE '".$z['grading']."'";
		     $result=DB_query($sql,$db);
		     $myrow=DB_fetch_row($result);
		     $sub_grade=$myrow[0];
			 $sub_points=$myrow[1];				 
			$LeftOvers = $pdf->addTextWrap($XPos2-11,$YPos+10,300,$FontSize,$sub_grade);
		}
		if($PageNumber <2)
		{
			$pdf->line($XPos2,$YPos+140,$XPos2, $YPos+10,$style);
		}
		if($PageNumber >1)
		{
			$pdf->line($XPos2, $YPos+32,$XPos2, $YPos-11,$style);
		}
		$XPos2 +=(0.55*$line_width);
		
		if($z['id']==4 && $addToSubject==1){
		$sqlmean = "SELECT title FROM reportcardgrades
		WHERE range_from <=  '". $z['tmarks'] ."'
		AND range_to >='". $z['tmarks'] ."'
		AND grading LIKE 'OTHER'";
		$resultmean=DB_query($sqlmean,$db);
		$myrowmean=DB_fetch_row($resultmean);
		$points=$myrowmean[0];
		$totalEnglishPoints=$totalEnglishPoints+$points;
		}
		else if($z['id']==6 && $addToSubject==1){
		$sqlmean = "SELECT title FROM reportcardgrades
		WHERE range_from <=  '". $z['tmarks'] ."'
		AND range_to >='". $z['tmarks'] ."'
		AND grading LIKE 'OTHER'";
		$resultmean=DB_query($sqlmean,$db);
		$myrowmean=DB_fetch_row($resultmean);
		$points=$myrowmean[0];
		$totalKiswahiliPoints=$totalKiswahiliPoints+$points;
		}
		else if($z['id']==5 && $addToSubject==1){
		$sqlmean = "SELECT title FROM reportcardgrades
		WHERE range_from <=  '". $z['tmarks'] ."'
		AND range_to >='". $z['tmarks'] ."'
		AND grading LIKE 'MATHS'";
		$resultmean=DB_query($sqlmean,$db);
		$myrowmean=DB_fetch_row($resultmean);
		$points=$myrowmean[0];
		$totalMathPoints=$totalMathPoints+$points;
		}
		else if($z['id']==14 && $addToSubject==1){
		$sqlmean = "SELECT title FROM reportcardgrades
		WHERE range_from <=  '". $z['tmarks'] ."'
		AND range_to >='". $z['tmarks'] ."'
		AND grading LIKE 'OTHER'";
		$resultmean=DB_query($sqlmean,$db);
		$myrowmean=DB_fetch_row($resultmean);
		$points=$myrowmean[0];
		$totalGeographyPoints=$totalGeographyPoints+$points;
		}
		else if($z['id']==8 && $addToSubject==1){
		$sqlmean = "SELECT title FROM reportcardgrades
		WHERE range_from <=  '". $z['tmarks'] ."'
		AND range_to >='". $z['tmarks'] ."'
		AND grading LIKE 'OTHER'";
		$resultmean=DB_query($sqlmean,$db);
		$myrowmean=DB_fetch_row($resultmean);
		$points=$myrowmean[0];
		$totalHistoryPoints=$totalHistoryPoints+$points;
		}
		else if($z['id']==9 && $addToSubject==1){
		$sqlmean = "SELECT title FROM reportcardgrades
		WHERE range_from <=  '". $z['tmarks'] ."'
		AND range_to >='". $z['tmarks'] ."'
		AND grading LIKE 'SCIENCE'";
		$resultmean=DB_query($sqlmean,$db);
		$myrowmean=DB_fetch_row($resultmean);
		$points=$myrowmean[0];
		$totalPhysicsPoints=$totalPhysicsPoints+$points;
		}
		else if($z['id']==10 && $addToSubject==1){
		$sqlmean = "SELECT title FROM reportcardgrades
		WHERE range_from <=  '". $z['tmarks'] ."'
		AND range_to >='". $z['tmarks'] ."'
		AND grading LIKE 'OTHER'";
		$resultmean=DB_query($sqlmean,$db);
		$myrowmean=DB_fetch_row($resultmean);
		$points=$myrowmean[0];
		$totalBusinessStudies=$totalBusinessStudies+$points;
		}
		else if($z['id']==11 && $addToSubject==1){
		$sqlmean = "SELECT title FROM reportcardgrades
		WHERE range_from <=  '". $z['tmarks'] ."'
		AND range_to >='". $z['tmarks'] ."'
		AND grading LIKE 'SCIENCE'";
		$resultmean=DB_query($sqlmean,$db);
		$myrowmean=DB_fetch_row($resultmean);
		$points=$myrowmean[0];
		$totalChemistryPoints=$totalChemistryPoints+$points;
		}
		else if($z['id']==12 && $addToSubject==1){
		$sqlmean = "SELECT title FROM reportcardgrades
		WHERE range_from <=  '". $z['tmarks'] ."'
		AND range_to >='". $z['tmarks'] ."'
		AND grading LIKE 'SCIENCE'";
		$resultmean=DB_query($sqlmean,$db);
		$myrowmean=DB_fetch_row($resultmean);
		$points=$myrowmean[0];
		$totalBiologyPoints=$totalBiologyPoints+$points;
		}
		else if($z['id']==13 && $addToSubject==1){
		$sqlmean = "SELECT title FROM reportcardgrades
		WHERE range_from <=  '". $z['tmarks'] ."'
		AND range_to >='". $z['tmarks'] ."'
		AND grading LIKE 'OTHER'";
		$resultmean=DB_query($sqlmean,$db);
		$myrowmean=DB_fetch_row($resultmean);
		$points=$myrowmean[0];
		$totalCREpoints=$totalCREpoints+$points;
		}
		else if($z['id']==15 && $addToSubject==1){
		$sqlmean = "SELECT title FROM reportcardgrades
		WHERE range_from <=  '". $z['tmarks'] ."'
		AND range_to >='". $z['tmarks'] ."'
		AND grading LIKE 'OTHER'";
		$resultmean=DB_query($sqlmean,$db);
		$myrowmean=DB_fetch_row($resultmean);
		$points=$myrowmean[0];
		$totalAgriculturePoints=$totalAgriculturePoints+$points;
		}	
	}//end of scheduled subject
	if($PageNumber ==1)
	{
		$pdf->line(19, $YPos+140,19, $YPos+10,$style);
		$pdf->line(195, $YPos+140,195, $YPos+10,$style);
		$pdf->line($XPos2+50,$YPos+140,$XPos2+50, $YPos+10,$style);
		$pdf->line($XPos2+14,$YPos+140,$XPos2+14, $YPos+10,$style);
		$pdf->line(210, $YPos+140,210, $YPos+10,$style);	
		$pdf->line(566, $YPos+140,566, $YPos+10,$style);
	}
	if($PageNumber >1)
	{
		$pdf->line(19, 835,$Page_Width-$Right_Margin, 835,$style);
		$pdf->line(195, 835,195, $YPos-11,$style);
		$pdf->line(19, $YPos+31,19, $YPos-11,$style);
		$pdf->line($XPos2+50,$YPos+31,$XPos2+50, $YPos-11,$style);
		$pdf->line($XPos2+14,$YPos+31,$XPos2+14, $YPos-11,$style);
		$pdf->line(210, $YPos+31,210, $YPos-11,$style);	
		$pdf->line(566, $YPos+31,566, $YPos-11,$style);
	}
	
	$sql = "SELECT grade FROM reportcardgrades
	WHERE title=  '". $st['meanScore'] ."'
	AND grading LIKE 'other'";
	$result=DB_query($sql,$db);
	$myrow=DB_fetch_row($result);
	$grade=$myrow[0];
			
$totalmarks_array =$bus_report->total_marks($st['student_id'],$_POST['period_id'],$db);
$LeftOvers = $pdf->addTextWrap($XPos2-15,$YPos+10,300,$FontSize,number_format($st['mean']/$st['no_of_subjects'],1));
$LeftOvers = $pdf->addTextWrap($XPos2+30,$YPos+10,300,$FontSize,$grade);	
$LeftOvers = $pdf->addTextWrap($XPos2+65,$YPos+10,300,$FontSize,$st['class_rank']);	

$grand_total=$grand_total+$st['marks'];							
	}
$XPos3=212;
foreach ($subjects_array as $r => $s) 
{	
	$count=0;
	$total_marks=0;
	$total_marks2=0;
	$count=studentsRegisteredForSubject($s['id'],$_POST['period_id'],$_POST['class_id'],$db);
	
		if($s['id']==4){
		$subject_mean=number_format($totalEnglishPoints/$count,3);
		}
		if($s['id']==6){
		$subject_mean=number_format($totalKiswahiliPoints/$count,3);
		}
		if($s['id']==5){
		$subject_mean=number_format($totalMathPoints/$count,3);
		}
		if($s['id']==14){
		$subject_mean=number_format($totalGeographyPoints/$count,3);
		}
		if($s['id']==8){
		$subject_mean=number_format($totalHistoryPoints/$count,3);
		}
		if($s['id']==9){
		$subject_mean=number_format($totalPhysicsPoints/$count,3);
		}
		if($s['id']==10){
		$subject_mean=number_format($totalBusinessStudies/$count,3);
		}
		if($s['id']==11){
		$subject_mean=number_format($totalChemistryPoints/$count,3);
		}
		if($s['id']==12){
		$subject_mean=number_format($totalBiologyPoints/$count,3);
		}
		if($s['id']==13){
		$subject_mean=number_format($totalCREpoints/$count,3);
		}
		if($s['id']==15){
		$subject_mean=number_format($totalAgriculturePoints/$count,1);
		}
	$checkMean=	number_format($subject_mean,0);	
	$sql = "SELECT grade FROM reportcardgrades
	WHERE title =  '". $checkMean ."'
	AND grading LIKE 'other'";
	$result=DB_query($sql,$db);
	$myrow=DB_fetch_row($result);
	$classSubjectGrade=$myrow[0];
	$LeftOvers = $pdf->addTextWrap($XPos3-2,$YPos+1,300,9,number_format($subject_mean,1));
	$LeftOvers = $pdf->addTextWrap($XPos3,$YPos-10,300,9,$classSubjectGrade);
	$XPos3 +=(0.55*$line_width);
}//end of ssubjects array foreach
if($no_of_students>0){	
$mean_class=number_format($grand_total/$no_of_students,2);	
}

$LeftOvers = $pdf->addTextWrap(21,$YPos+1,300,$FontSize,_('Subject Mean Score'));
//$LeftOvers = $pdf->addTextWrap($XPos3,$YPos+1,300,$FontSize,$mean_class);
$pdf->line(19, $YPos,$Page_Width-$Right_Margin, $YPos,$style);	
$LeftOvers = $pdf->addTextWrap(21,$YPos-10,300,$FontSize,_('Subject Mean Grade'));
//$LeftOvers = $pdf->addTextWrap($XPos3,$YPos-10,300,$FontSize,number_format($classSubjectGrade,1));	
$pdf->line(19, $YPos-11,$Page_Width-$Right_Margin, $YPos-11,$style);	
$pdf->Output('Report-', 'I');
}

else { /*The option to print PDF was not hit */
$title = _('Consolidated Class Marksheet Report');
include('includes/header.inc');
echo '<p class="page_title_text">' . ' ' . $title. '';
echo '<FORM METHOD="POST" ACTION="' . $_SERVER['PHP_SELF'] . '?' . SID . '">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<CENTER><TABLE class="enclosed"><TR><TD>' . _('Class:') . '</TD><TD><SELECT Name="class_id">';
		DB_data_seek($result, 0);
		$result = DB_query('SELECT * FROM gradelevels',$db);
while ($myrow = DB_fetch_array($result)) {
	if ($myrow['id']==$_POST['class_id']) {
		echo '<option selected VALUE=';
	} else {
		echo '<option VALUE=';
	}
	echo $myrow['id'] . '>' . $myrow['grade_level'];
} //end while loop
	echo '</SELECT></TD></TR>';
echo '<CENTER><TR><TD>' . _('Period:') . '</TD><TD><SELECT Name="period_id">';
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
	echo "<P><CENTER><INPUT TYPE='Submit' NAME='PrintPDF' VALUE='" . _('View') . "'>";

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