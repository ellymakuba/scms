<?php
$PageSecurity = 2;
if(isset($_POST['submit']) && isset($_POST['yos']) && isset($_POST['term'])){
include('includes/session.inc');
include('includes/PDFStarter.php');
require('grades/OveralClassReport.php');
$FontSize=13;
$pdf->addinfo('Title', _('Sales Receipt') );

$_SESSION['yos'] = $_POST['yos'];
$_SESSION['term'] = $_POST['term'];
		
$PageNumber=1;
$line_height=12;

$FontSize=13;
$YPos= $Page_Height-$Top_Margin;
$XPos=0;
$pdf->addJpegFromFile($_SESSION['LogoFile'] ,$XPos+230,$YPos-120,0,80);

$DebtorNo=$_POST['debtorno'];

$FontSize=8;
$LeftOvers = $pdf->addTextWrap(50,$YPos-($line_height*5),300,$FontSize,$_SESSION['CompanyRecord']['coyname']);
$LeftOvers = $pdf->addTextWrap(50,$YPos-($line_height*6),300,$FontSize,$_SESSION['CompanyRecord']['regoffice3']);
$LeftOvers = $pdf->addTextWrap(50,$YPos-($line_height*7),300,$FontSize,$_SESSION['CompanyRecord']['regoffice5']);
$LeftOvers = $pdf->addTextWrap($Page_Width-$Right_Margin-150,$YPos-($line_height*5),140,$FontSize,_('Phone').': ' .  $_SESSION['CompanyRecord']['telephone']);
$LeftOvers = $pdf->addTextWrap($Page_Width-$Right_Margin-150,$YPos-($line_height*6),300,$FontSize, _('Email').': ' . $_SESSION['CompanyRecord']['email']);
$LeftOvers = $pdf->addTextWrap($Page_Width-$Right_Margin-150,$YPos-($line_height*7),300,$FontSize, _('website').': ' . $_SESSION['CompanyRecord']['fax']);
$FontSize=16;
/*$LeftOvers = $pdf->addTextWrap(100,$YPos-($line_height*11),500,$FontSize, _('Reportcard For').': ' . $myrow[0].'    '._('Period').': ' .$myrow2[1].'-'.$myrow2[2]);*/	
$LeftOvers = $pdf->addTextWrap(250,$YPos-($line_height*12),180,$FontSize,_('Fee Structure'));
 $LeftOvers = $pdf->addTextWrap(250,$YPos-($line_height*12.3),45,$FontSize,'______________________________________________________________________________');

$YPos -=(11.5*$line_height);
$pdf->line($Left_Margin, $YPos+$line_height,$Page_Width-$Right_Margin, $YPos+$line_height);

$sql = "SELECT grade_level FROM gradelevels
	WHERE id = '".$_SESSION['yos']."'";
     $result=DB_query($sql,$db);
	$myrow = DB_fetch_array($result);
	$grade_level=$myrow['grade_level'];
	
$sql="SELECT cp.id,terms.title,years.year FROM collegeperiods cp
	INNER JOIN terms ON terms.id=cp.term_id
	INNER JOIN years ON years.id=cp.year
WHERE cp.id='".$_SESSION['term']."'";
$result=DB_query($sql,$db);
$myrow = DB_fetch_array($result);
$period_name=$myrow['title']._(' ').$myrow['year'];
		
$FontSize=12;
$YPos -=(3*$line_height);
$LeftOvers = $pdf->addTextWrap(50,$YPos,180,$FontSize,_('YOS').': '.$grade_level);	
$YPos -=(1*$line_height);
$LeftOvers = $pdf->addTextWrap(50,$YPos,180,$FontSize,_('Period').': '.$period_name);		

$YPos -=(1.5*$line_height);
$YPos3=$YPos;

$FontSize=12;
$YPos -=$line_height;
$pdf->line($Left_Margin, $YPos+$line_height,$Page_Width-$Right_Margin, $YPos+$line_height);

$line_width=40;
$XPos=150;
$YPos2=$YPos;
$count=0;
$i=0;


 $sql = "SELECT id FROM autobilling 
	 WHERE  yos = '".$_SESSION['yos']."'
	 AND term_id = '".$_SESSION['term']."'";
     $result=DB_query($sql,$db);
	$myrow = DB_fetch_array($result);
	$autobillID=$myrow['id'];

	 $sql = "SELECT sm.description, ai.amount FROM autobilling_items ai
	 INNER JOIN stockmaster sm ON sm.stockid=ai.product_id
	 WHERE autobilling_id = '".$autobillID."'
	 ORDER BY priority";
     $DbgMsg = _('The SQL that was used to retrieve the information was');
     $ErrMsg = _('Could not check whether the group is recursive because');
     $result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
	$YPos -=$line_height;
	$count=0;
	$total=0;	 
while ($row = DB_fetch_array($result))
	{
	if ($YPos < ($Bottom_Margin)) {
	$PageNumber++;
	if ($PageNumber>1){
	$pdf->newPage();
		 }
	 }
	$count=$count+1;		
	$LeftOvers = $pdf->addTextWrap(50,$YPos,300,$FontSize,$count._(' ')._('.'));
	$LeftOvers = $pdf->addTextWrap(65,$YPos,300,$FontSize,$row['description']);
	$LeftOvers = $pdf->addTextWrap(300,$YPos,300,$FontSize,$row['amount']);
	$YPos -=(2*$line_height);
	$pdf->line($Left_Margin, $YPos+$line_height,$Page_Width-$Right_Margin, $YPos+$line_height);
	$total=$total+$row['amount'];
}
if ($YPos < ($Bottom_Margin)) {
	$PageNumber++;
	if ($PageNumber>1){
	$pdf->newPage();
		 }
	 }
$LeftOvers = $pdf->addTextWrap(50,$YPos,50,$FontSize,_('Total'));
$LeftOvers = $pdf->addTextWrap(300,$YPos,300,$FontSize,number_format($total,2));
$YPos -=(2*$line_height);
$pdf->line($Left_Margin, $YPos+$line_height,$Page_Width-$Right_Margin, $YPos+$line_height);
$pdf->line(40, $YPos3,40, $YPos+($line_height*1));	
$pdf->line(295, $YPos3,295, $YPos+($line_height*1));	
$pdf->line(566, $YPos3,566, $YPos+($line_height*1));
$pdf->Output('Receipt-'.$_GET['ReceiptNumber'], 'I');


}

else { /*The option to print PDF was not hit */

	include('includes/session.inc');
	$title = _('View Fee Structure');

include('includes/header.inc');

echo "<form method='post' action=" . $_SERVER['PHP_SELF'] . '>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<table border="1">';
	
echo '<tr><td>' . _('YOS') . ":</td>
		<td><select name='yos'>";
		echo '<OPTION SELECTED VALUE=0>' . _('Select YOS');
		$sql="SELECT id,grade_level FROM gradelevels
		ORDER BY grade_level";
		$result=DB_query($sql,$db);
		while ($myrow = DB_fetch_array($result)) {
	echo '<option value='. $myrow['id'] . '>' .$myrow['grade_level'];
		} //end while loop
		DB_data_seek($result,0);
	echo '</select></td></tr>';
echo '<tr><td>' . _('Term') . ":</td>
		<td><select name='term'>";
		echo '<OPTION SELECTED VALUE=0>' . _('Select term');
		$sql="SELECT cp.id,terms.title,years.year FROM collegeperiods cp
		INNER JOIN terms ON terms.id=cp.term_id
		INNER JOIN years ON years.id=cp.year ";
		$result=DB_query($sql,$db);
		while ($myrow = DB_fetch_array($result)) {
		echo '<option value='. $myrow['id'].  '>'.' '.$myrow['title'].' '.$myrow['year'];
		} //end while loop
		DB_data_seek($result,0);
		echo '</select></td></tr></table>';
		echo '<table border="1">';
echo "<br><div class='centre'><input  type='Submit' name='submit' value='" . _('Submit') . "'>&nbsp;<input  type=submit action=RESET VALUE='" . _('Reset') . "'></div>";	

	include('includes/footer.inc');;
} /*end of else not PrintPDF */

