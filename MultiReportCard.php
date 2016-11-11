<?php
/* $Id: PDFReceipt.php 3714 2010-09-07 21:31:01Z tim_schofield $*/

$PageSecurity = 2;
if(isset($_POST['period_id']) ){
include('includes/session.inc');
include('includes/PDFStarter.php');
require('grades/MultiReportCardClass.php');
$FontSize=13;
$pdf->addinfo('Title', _('Sales Receipt') );

$_SESSION['period'] = $_POST['period_id'];		
$PageNumber=1;
$ReportCard=1;
$YPos -=75;
$YPos -=$line_height;
//Note, this is ok for multilang as this is the value of a Select, text in option is different

$YPos -=(5*$line_height);

/*Draw a rectangle to put the headings in     */

$pdf->line($Left_Margin, $YPos+$line_height,$Page_Width-$Right_Margin, $YPos+$line_height);

$FontSize=13;
$YPos -= (1.5 * $line_height);

$PageNumber++;
$line_width=70;
$XPos=150;
$YPos2=$YPos;
$count=0;
$i=0;
$bus_report = new bus_report($_POST['period_id'],$db);

foreach ($bus_report->scheduled_subjects as $a => $b) {
$scheduled = new scheduled($b['subject_id'],$db);
	$scheduled->set_calendar_vars($b['id'],$db);	
	
if ($ReportCard==1) {
$FontSize=13;
$YPos= $Page_Height-$Top_Margin;
$XPos=0;

$status_array = tep_get_status($db);
foreach ($status_array as $r => $s) {
$LeftOvers = $pdf->addTextWrap($XPos+45,$YPos,300,$FontSize,$s['title']);
$XPos +=(1*$line_width);
		}
		$YPos =620;
		
$count=$count+1;
	
	$LeftOvers = $pdf->addTextWrap(50,$YPos,300,$FontSize,$scheduled->subject_name);
	$status_array = tep_get_status($db);
	$XPos2=180;
	$YPos -=(2*$line_height);

foreach ($scheduled->status as $y=>$z) {
$i++;
	$LeftOvers = $pdf->addTextWrap($XPos2+25,$YPos+25,300,$FontSize,$z['marks']);
	$XPos2 +=(1*$line_width);
	
				}
	$totalmarks_array =$bus_report->total_marks($b['id'],$b['subject_id'],$db);
$sql = "SELECT title,comment FROM reportcardgrades
		WHERE range_from <=  '". $totalmarks_array ."'
		AND range_to >='". $totalmarks_array ."'";
        $result=DB_query($sql,$db);
		$myrow=DB_fetch_row($result);
	$LeftOvers = $pdf->addTextWrap($XPos2,$YPos+28,300,$FontSize,$totalmarks_array);					
	$LeftOvers = $pdf->addTextWrap($XPos2+50,$YPos+28,300,$FontSize,$myrow[0]);
	$LeftOvers = $pdf->addTextWrap($XPos2+100,$YPos+28,300,$FontSize,$myrow[1]);			
	$totalmarks_array2=$totalmarks_array2+$totalmarks_array;

$pdf->line($Left_Margin, $YPos+$line_height,$Page_Width-$Right_Margin, $YPos+$line_height);
$mean_grade=$totalmarks_array2/$count;

$ReportCard==2;		
}
elseif ($ReportCard==2) {
		$PageNumber++;
		if ($PageNumber>1){
			$pdf->newPage();
			$YPos = $Page_Height - $Top_Margin;
			$YPos -= (3 * $line_height);
			}
			$FontSize =13;

$FontSize=13;
$YPos= $Page_Height-$Top_Margin;
$XPos=0;

$status_array = tep_get_status($db);
foreach ($status_array as $r => $s) {
$LeftOvers = $pdf->addTextWrap($XPos+45,$YPos,300,$FontSize,$s['title']);
$XPos +=(1*$line_width);
		}
		$YPos =620;
		
$count=$count+1;
$LeftOvers = $pdf->addTextWrap(50,$YPos,300,$FontSize,$scheduled->subject_name);
$status_array = tep_get_status($db);
$XPos2=180;
$YPos -=(2*$line_height);

foreach ($scheduled->status as $y=>$z) {
$i++;
	$LeftOvers = $pdf->addTextWrap($XPos2+25,$YPos+25,300,$FontSize,$z['marks']);
	$XPos2 +=(1*$line_width);
	
				}
	$totalmarks_array =$bus_report->total_marks($b['id'],$b['subject_id'],$db);
$sql = "SELECT title,comment FROM reportcardgrades
		WHERE range_from <=  '". $totalmarks_array ."'
		AND range_to >='". $totalmarks_array ."'";
        $result=DB_query($sql,$db);
		$myrow=DB_fetch_row($result);
	$LeftOvers = $pdf->addTextWrap($XPos2,$YPos+28,300,$FontSize,$totalmarks_array);					
	$LeftOvers = $pdf->addTextWrap($XPos2+50,$YPos+28,300,$FontSize,$myrow[0]);
	$LeftOvers = $pdf->addTextWrap($XPos2+100,$YPos+28,300,$FontSize,$myrow[1]);			
	$totalmarks_array2=$totalmarks_array2+$totalmarks_array;
	


$pdf->line($Left_Margin, $YPos+$line_height,$Page_Width-$Right_Margin, $YPos+$line_height);
$mean_grade=$totalmarks_array2/$count;

$ReportCard==1;		
}			
}

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
			if ($myrow['id'] == $_POST['period_id']) {  
				echo '<OPTION SELECTED VALUE=';
			} else {
				echo '<OPTION VALUE=';
			}
			echo $myrow['id'] . '>'.' '.$myrow['title'].' '.$myrow['year'];
		} //end while loop
	echo '</SELECT></TD></TR>';
	echo "</TABLE>";
	echo "<P><CENTER><INPUT TYPE='Submit' NAME='PrintPDF' VALUE='" . _('PrintPDF') . "'>";

	include('includes/footer.inc');;
} /*end of else not PrintPDF */

?>