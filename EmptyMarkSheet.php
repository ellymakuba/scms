<?php
/* $Id: PDFReceipt.php 3714 2010-09-07 21:31:01Z tim_schofield $*/

$PageSecurity = 2;
include('includes/session.inc');
if(isset($_POST['period_id']) && isset($_POST['class_id']) && isset($_POST['PrintPDF'])){
include('includes/PDFStarter.php');
include('grades/EmptyMarkSheetClass.php');
$FontSize=13;

$_SESSION['class'] = $_POST['class_id'];
$_SESSION['period'] = $_POST['period_id'];		
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

$sql = "SELECT c.id,c.class_name FROM classes c
WHERE c.id =  '". $_SESSION['class'] ."'";
$result=DB_query($sql,$db);
$myrow=DB_fetch_array($result);
$class_name=$myrow['class_name'];
/*$LeftOvers = $pdf->addTextWrap(100,$YPos-($line_height*11),500,$FontSize, _('Reportcard For').': ' . $myrow[0].'    '._('Period').': ' .$myrow2[1].'-'.$myrow2[2]);*/	
$LeftOvers = $pdf->addTextWrap(200,$YPos-($line_height*12),400,$FontSize,_('EMPTY STREAM MARKSHEET'));
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
$XPosSubject=$XPos+25;
$bus_report_stream = new bus_report_stream($_POST['class_id'],$_POST['period_id'],$db);
$subjects_array = tep_get_subjects_stream($_POST['class_id'],$_POST['period_id'],$db);
$current_student='';
$pdf->starttransform();
$pdf->xy($XPos-20,332);
$pdf->rotate(90);
$LeftOvers = $pdf->addTextWrap($XPos-65,$YPos-5,300,$FontSize,_('GENDER'));
$pdf->stoptransform();
$YPosSubject=$YPos+140;
	foreach ($subjects_array as $r => $s)
	 {
		$pdf->starttransform();
		$pdf->xy($XPos,332);
		$pdf->rotate(90);
		$LeftOvers = $pdf->addTextWrap($XPos-45,$YPos,300,$FontSize,$s['subject_name']);
		$pdf->stoptransform();		
		$XPos +=(0.8*$line_width);
	}		
$YPos -=10;
$counter =0;
$pdf->line(19, $YPos,$Page_Width-$Right_Margin, $YPos,$style);	
$YPos -=$line_height;
foreach ($bus_report_stream->scheduled_students as $sa => $st) 
{
    $counter++;	
	$no_of_students=$no_of_students+1;
	if ($YPos < ($Bottom_Margin + (2* $line_height)))
	{ //need 5 lines grace otherwise start new page
				$PageNumber++;
				NewPageHeader ();
	}
	
	$LeftOvers = $pdf->addTextWrap(21,$YPos+1,300,$FontSize,$counter);
	$LeftOvers = $pdf->addTextWrap(40,$YPos+1,300,$FontSize,$st['debtorno']);	
	$LeftOvers = $pdf->addTextWrap(70,$YPos+1,300,$FontSize,$st['name']);
	//$LeftOvers = $pdf->addTextWrap(200,$YPos+1,300,$FontSize,$st['initial']);
	$pdf->line(19, $YPos,$Page_Width-$Right_Margin, $YPos,$style);	
	$YPos -=(1.5*$line_height);
	$scheduled = new scheduled_stream($st['student_id'],$db);
	$scheduled->set_calendar_vars_stream($_POST['class_id'],$st['student_id'],$_POST['period_id'],$st['id'],$db);	
	$XPos2=230;
	$subject_meangrade_array=0;
	foreach ($scheduled->subject as $y=>$z) 
	{
		
		if($PageNumber <2){
		$pdf->line($XPos2,$YPos+146,$XPos2, $YPos+16,$style);		
		}
		if($PageNumber >1)
		{
		$pdf->line($XPos2, $YPos+38,$XPos2, $YPos+16,$style);	
		}
		$XPos2 +=(0.8*$line_width);	
	}//end of scheduled subject	
	if($PageNumber ==1)
	{
		$pdf->line(19, $YPos+146,19, $YPos+16,$style);
		$pdf->line(195, $YPos+146,195, $YPos+16,$style);
		$pdf->line(210, $YPos+146,210, $YPos+16,$style);	
		$pdf->line(566, $YPos+146,566, $YPos+16,$style);
	}
	if($PageNumber >1)
	{
		$pdf->line(19, 832,$Page_Width-$Right_Margin, 832,$style);
		$pdf->line(19, $YPos+37,19, $YPos+16,$style);
		$pdf->line(195, 832,195, $YPos+16,$style);
		$pdf->line(210, 832,210, $YPos+16,$style);
		$pdf->line(566, $YPos+37,566, $YPos+16,$style);
	}
	
}//end of scheduled students	
$pdf->Output('Report-', 'I');
}
else { /*The option to print PDF was not hit */	
	$title = _('Manage Students');
include('includes/header.inc');
echo '<p class="page_title_text">' . ' ' . _('Empty Marksheet') . '';
echo '<FORM METHOD="POST" ACTION="' . $_SERVER['PHP_SELF'] . '?' . SID . '">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<CENTER><TABLE class="enclosed"><TR><TD>' . _('Class:') . '</TD><TD><SELECT Name="class_id">';
		DB_data_seek($result, 0);
		$result = DB_query('SELECT * FROM classes ORDER BY class_name',$db);
while ($myrow = DB_fetch_array($result)) {
	if ($myrow['id']==$_POST['class_id']) {
		echo '<option selected VALUE=';
	} else {
		echo '<option VALUE=';
	}
	echo $myrow['id'] . '>' . $myrow['class_name'];
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