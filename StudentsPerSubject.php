<?php
/* $Id: PDFReceipt.php 3714 2010-09-07 21:31:01Z tim_schofield $*/

$PageSecurity = 2;
if(isset($_POST['period_id']) && isset($_POST['subject_id']) && isset($_POST['grade'])){
include('includes/session.inc');
include('includes/PDFStarter.php');
require('grades/TeacherSubjectClass.php');
$FontSize=13;
$pdf->addinfo('Title', _('Sales Receipt') );

$_SESSION['class'] = $_POST['class_id'];
$_SESSION['period'] = $_POST['period_id'];
$_SESSION['subject'] = $_POST['subject_id'];			
$PageNumber=1;
$line_height=12;
if ($PageNumber>1){
	$pdf->newPage();
}
$FontSize=13;
$YPos= $Page_Height-$Top_Margin;
$XPos=0;
$pdf->addJpegFromFile($_SESSION['LogoFile'] ,$XPos+200,$YPos-120,0,80);

$DebtorNo=$_POST['debtorno'];


$FontSize=8;
$LeftOvers = $pdf->addTextWrap(40,$YPos-($line_height*5),300,$FontSize,_('Institute Plaza'));
$LeftOvers = $pdf->addTextWrap(40,$YPos-($line_height*6),300,$FontSize,_('Next to kenya Power'));
$LeftOvers = $pdf->addTextWrap(40,$YPos-($line_height*7),300,$FontSize,_('emergency office'));
$LeftOvers = $pdf->addTextWrap(40,$YPos-($line_height*8),300,$FontSize,$_SESSION['CompanyRecord']['regoffice3']);
$LeftOvers = $pdf->addTextWrap(40,$YPos-($line_height*9),300,$FontSize,$_SESSION['CompanyRecord']['regoffice4']);
$LeftOvers = $pdf->addTextWrap(40,$YPos-($line_height*10),300,$FontSize,$_SESSION['CompanyRecord']['regoffice6']);
$LeftOvers = $pdf->addTextWrap($Page_Width-$Right_Margin-150,$YPos-($line_height*5),140,$FontSize, _('Tel').': ' . $_SESSION['CompanyRecord']['regoffice2']);

$LeftOvers = $pdf->addTextWrap($Page_Width-$Right_Margin-150,$YPos-($line_height*6),300,$FontSize, _('website').': ' . $_SESSION['CompanyRecord']['regoffice1']);


$sql = "SELECT subject_name FROM subjects
WHERE id =  '". $_POST['subject_id'] ."'";
$result=DB_query($sql,$db);
$myrow=DB_fetch_array($result);
$subject=$myrow['subject_name'];

$sql = "SELECT grade_level FROM gradelevels
WHERE id =  '". $_POST['grade'] ."'";
$result=DB_query($sql,$db);
$myrow=DB_fetch_array($result);
$grade=$myrow['grade_level'];

/*$LeftOvers = $pdf->addTextWrap(100,$YPos-($line_height*11),500,$FontSize, _('Reportcard For').': ' . $myrow[0].'    '._('Period').': ' .$myrow2[1].'-'.$myrow2[2]);*/	
$LeftOvers = $pdf->addTextWrap(200,$YPos-($line_height*12),400,$FontSize,_('STREAMS PERFORMANCE IN SUBJECT'));
 $LeftOvers = $pdf->addTextWrap(200,$YPos-($line_height*12.3),75,$FontSize,'______________________________________________________________________________');

$LeftOvers = $pdf->addTextWrap(40,$YPos-($line_height*15),300,$FontSize, _('Class').': ' . $grade);
$LeftOvers = $pdf->addTextWrap(300,$YPos-($line_height*15),300,$FontSize, _('Subject').': ' . $subject);	
$YPos +=20;
$YPos -=$line_height;
//Note, this is ok for multilang as this is the value of a Select, text in option is different

$YPos -=(12*$line_height);

$pdf->line(39, $YPos+$line_height,$Page_Width-$Right_Margin, $YPos+$line_height);

$YPos -=50;
$YPos -=$line_height;
$Left_Margin2=100;
$pdf->line(39, $YPos+$line_height,500, $YPos+$line_height);
$pdf->line(39, $YPos,500, $YPos);

$LeftOvers = $pdf->addTextWrap(40,$YPos+1,300,$FontSize,_('Rank'));
$LeftOvers = $pdf->addTextWrap(70,$YPos+1,300,$FontSize,_('AdmsnNo'));
$LeftOvers = $pdf->addTextWrap(150,$YPos+1,300,$FontSize,_('Roll'));
$LeftOvers = $pdf->addTextWrap(200,$YPos+1,300,$FontSize,_('Teacher'));
$LeftOvers = $pdf->addTextWrap(300,$YPos+1,300,$FontSize,_('marks'));
$LeftOvers = $pdf->addTextWrap(370,$YPos+1,300,$FontSize,_('Mean Grade'));
$line_width=40;
$XPos=160;
$YPos2=$YPos+$line_height;
$count=0;
$i=0;
$reportgrade=0;
$total_mean=0;
$streams_array=get_streams($_POST['grade'],$_POST['period_id'],$_POST['subject_id'],$db);
foreach($streams_array as $streams=>$stream){
$bus_report2 = new bus_report2($stream['id'],$_POST['period_id'],$_POST['subject_id'],$db);
foreach ($bus_report2->scheduled_streams as $a => $b) {
$count=$count+1;
$sql = "SELECT grade FROM reportcardgrades
		WHERE range_from <=  '".$b['marks']."'
		AND range_to >='". $b['marks']."'";
		$result=DB_query($sql,$db);
		$myrow=DB_fetch_array($result);
		$reportgrade=$myrow['grade'];
		
$LeftOvers = $pdf->addTextWrap(40,$YPos-10,300,$FontSize,$count);
$LeftOvers = $pdf->addTextWrap(70,$YPos-10,300,$FontSize,$b['debtorno']);	
$LeftOvers = $pdf->addTextWrap(150,$YPos-10,300,$FontSize,$b['name']);
$LeftOvers = $pdf->addTextWrap(200,$YPos-10,300,$FontSize,$b['marks']);
$LeftOvers = $pdf->addTextWrap(300,$YPos-10,300,$FontSize,$b['mean']);
$LeftOvers = $pdf->addTextWrap(370,$YPos-10,300,$FontSize,$reportgrade);
	
$YPos -=$line_height;
$total_mean=$total_mean+$b['marks'];
				}
			}
		$YPos -=$line_height;	
if($count>0)
$subject_mean=number_format($total_mean/$count,2);
else
$subject_mean=0;

$sql = "SELECT grade FROM reportcardgrades
		WHERE range_from <=  '".$subject_mean."'
		AND range_to >='". $subject_mean."'";
		$result=DB_query($sql,$db);
		$myrow=DB_fetch_array($result);
		$reportgrade=$myrow['grade'];	
		
$LeftOvers = $pdf->addTextWrap(200,$YPos-10,300,$FontSize,_('Class Subject Mean').' '.$subject_mean);		
$pdf->line(39, $YPos2,39, $YPos+($line_height*1));
$pdf->line(69, $YPos2,69, $YPos+($line_height*1));
$pdf->line(149, $YPos2,149, $YPos+($line_height*1));
$pdf->line(198, $YPos2,198, $YPos+($line_height*1));
$pdf->line(298, $YPos2,298, $YPos+($line_height*1));
$pdf->line(368, $YPos2,368, $YPos+($line_height*1));
$pdf->line(500, $YPos2,500, $YPos+($line_height*1));
$pdf->line(39, $YPos+$line_height,500, $YPos+$line_height);

$pdf->Output('Receipt-'.$_GET['ReceiptNumber'], 'I');


}
else { /*The option to print PDF was not hit */

	include('includes/session.inc');
	$title = _('Manage Students2');

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
echo '<TR><TD>' . _('Class:') . '</TD><TD><SELECT Name="grade">';
		$sql="SELECT id,grade_level FROM gradelevels ";
		$result=DB_query($sql,$db);
		while ($myrow = DB_fetch_array($result)) {
			if ($myrow['id'] == $_POST['grade']) {  
				echo '<OPTION SELECTED VALUE=';
			} else {
				echo '<OPTION VALUE=';
			}
			echo $myrow['id'] . '>'.' '.$myrow['grade_level'];
		} //end while loop
	echo '</SELECT></TD></TR>';
echo '<TR><TD>' . _('Subject:') . '</TD><TD><SELECT Name="subject_id">';
		$sql="SELECT id,subject_name FROM subjects ";
		$result=DB_query($sql,$db);
		while ($myrow = DB_fetch_array($result)) {
			if ($myrow['id'] == $_POST['subject_id']) {  
				echo '<OPTION SELECTED VALUE=';
			} else {
				echo '<OPTION VALUE=';
			}
			echo $myrow['id'] . '>'.' '.$myrow['subject_name'];
		} //end while loop
	echo '</SELECT></TD></TR>';
	echo "</TABLE>";
	echo "<P><CENTER><INPUT TYPE='Submit' NAME='PrintPDF' VALUE='" . _('PrintPDF') . "'>";

	include('includes/footer.inc');;
} /*end of else not PrintPDF */

?>